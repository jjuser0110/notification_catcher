package com.notificationcatcher.app

import org.json.JSONArray
import org.json.JSONObject
import java.net.HttpURLConnection
import java.net.URL

/** Minimal HTTP client using HttpURLConnection + org.json (both built into Android). */
object ApiClient {

    data class Result(val code: Int, val body: String)

    private fun normalize(baseUrl: String): String =
        baseUrl.trim().trimEnd('/')

    private fun post(urlStr: String, token: String?, payload: JSONObject): Result {
        val conn = (URL(urlStr).openConnection() as HttpURLConnection).apply {
            requestMethod = "POST"
            connectTimeout = 15000
            readTimeout = 20000
            doOutput = true
            setRequestProperty("Content-Type", "application/json")
            setRequestProperty("Accept", "application/json")
            if (!token.isNullOrBlank()) setRequestProperty("Authorization", "Bearer $token")
        }
        return try {
            conn.outputStream.use { it.write(payload.toString().toByteArray(Charsets.UTF_8)) }
            val code = conn.responseCode
            val stream = if (code in 200..299) conn.inputStream else conn.errorStream
            val body = stream?.bufferedReader()?.use { it.readText() } ?: ""
            Result(code, body)
        } catch (e: Exception) {
            Result(-1, e.message ?: "network error")
        } finally {
            conn.disconnect()
        }
    }

    /** Registers the device; on success returns the API token. */
    fun register(
        baseUrl: String, enrollKey: String, uid: String, name: String,
        manufacturer: String, model: String, androidVersion: String, appVersion: String,
    ): Result {
        val payload = JSONObject()
            .put("enroll_key", enrollKey)
            .put("device_uid", uid)
            .put("name", name)
            .put("manufacturer", manufacturer)
            .put("model", model)
            .put("android_version", androidVersion)
            .put("app_version", appVersion)
        return post("${normalize(baseUrl)}/api/devices/register", null, payload)
    }

    /** Uploads a batch of captured items. */
    fun postMessages(baseUrl: String, token: String, items: JSONArray): Result {
        val payload = JSONObject().put("items", items)
        return post("${normalize(baseUrl)}/api/messages", token, payload)
    }
}
