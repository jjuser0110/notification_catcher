package com.notificationcatcher.app

import android.content.Context
import org.json.JSONArray
import org.json.JSONObject
import java.io.File

/**
 * A durable on-disk queue of captured items (one JSON object per line).
 * Items survive the app being killed; they are removed only after the server
 * confirms them. All access is guarded by [lock] because the notification
 * service and the SMS receiver run in the same process.
 */
object MessageQueue {

    private val lock = Any()
    private const val FILE_NAME = "queue.jsonl"
    private const val MAX_BATCH = 200

    private fun file(ctx: Context) = File(ctx.applicationContext.filesDir, FILE_NAME)

    /** Adds one item. type is "notification" or "sms". receivedAt is epoch millis. */
    fun enqueue(
        ctx: Context, type: String, appPackage: String?, appName: String?,
        sender: String?, title: String?, body: String?, receivedAt: Long,
    ) {
        val obj = JSONObject()
            .put("type", type)
            .put("app_package", appPackage ?: JSONObject.NULL)
            .put("app_name", appName ?: JSONObject.NULL)
            .put("sender", sender ?: JSONObject.NULL)
            .put("title", title ?: JSONObject.NULL)
            .put("body", body ?: JSONObject.NULL)
            .put("received_at", receivedAt)
        synchronized(lock) {
            file(ctx).appendText(obj.toString() + "\n")
        }
    }

    fun pendingCount(ctx: Context): Int = synchronized(lock) {
        val f = file(ctx)
        if (!f.exists()) 0 else f.readLines().count { it.isNotBlank() }
    }

    /**
     * Sends queued items in batches. Returns a short status string.
     * Stops on the first failed batch so nothing is lost.
     */
    fun flush(ctx: Context): String {
        if (!Prefs.isRegistered(ctx)) return "not registered yet"
        val baseUrl = Prefs.baseUrl(ctx)
        val token = Prefs.token(ctx)

        var sentTotal = 0
        while (true) {
            val lines: List<String> = synchronized(lock) {
                val f = file(ctx)
                if (!f.exists()) return if (sentTotal == 0) "nothing to send" else "sent $sentTotal"
                f.readLines().filter { it.isNotBlank() }
            }
            if (lines.isEmpty()) return if (sentTotal == 0) "nothing to send" else "sent $sentTotal"

            val batch = lines.take(MAX_BATCH)
            val items = JSONArray()
            for (line in batch) {
                try { items.put(JSONObject(line)) } catch (_: Exception) { /* skip bad line */ }
            }

            val res = ApiClient.postMessages(baseUrl, token, items)
            if (res.code !in 200..299) {
                return "server error ${res.code}; $sentTotal sent, ${lines.size} still queued"
            }

            // Success: drop the lines we just sent, keep the rest.
            synchronized(lock) {
                val f = file(ctx)
                val remaining = (if (f.exists()) f.readLines().filter { it.isNotBlank() } else emptyList())
                    .drop(batch.size)
                if (remaining.isEmpty()) f.delete()
                else f.writeText(remaining.joinToString("\n") + "\n")
            }
            sentTotal += batch.size
            if (batch.size < MAX_BATCH) return "sent $sentTotal"
        }
    }
}
