package com.notificationcatcher.app

import android.content.Context
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch

/** Fire-and-forget background flush used by the capture entry points. */
object Uploader {
    private val scope = CoroutineScope(Dispatchers.IO)

    fun kickFlush(ctx: Context) {
        val app = ctx.applicationContext
        scope.launch {
            try { MessageQueue.flush(app) } catch (_: Exception) { /* retried next time */ }
        }
    }
}
