package com.notificationcatcher.app

import android.content.BroadcastReceiver
import android.content.Context
import android.content.Intent
import android.provider.Telephony

/** Catches incoming SMS, even while the app is closed. */
class SmsReceiver : BroadcastReceiver() {

    override fun onReceive(context: Context, intent: Intent) {
        if (intent.action != Telephony.Sms.Intents.SMS_RECEIVED_ACTION) return

        try {
            val messages = Telephony.Sms.Intents.getMessagesFromIntent(intent) ?: return
            if (messages.isEmpty()) return

            // A long SMS arrives as several parts; join them into one body.
            val sender = messages[0].displayOriginatingAddress ?: messages[0].originatingAddress
            val body = StringBuilder()
            var timestamp = messages[0].timestampMillis
            for (m in messages) body.append(m.messageBody ?: "")
            if (timestamp <= 0L) timestamp = System.currentTimeMillis()

            MessageQueue.enqueue(
                ctx = context.applicationContext,
                type = "sms",
                appPackage = null,
                appName = null,
                sender = sender,
                title = null,
                body = body.toString(),
                receivedAt = timestamp,
            )
            Uploader.kickFlush(context.applicationContext)
        } catch (_: Exception) {
            // Ignore malformed broadcasts.
        }
    }
}
