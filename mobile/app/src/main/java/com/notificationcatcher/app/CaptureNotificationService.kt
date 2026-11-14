package com.notificationcatcher.app

import android.app.Notification
import android.service.notification.NotificationListenerService
import android.service.notification.StatusBarNotification

/** Receives every notification the system posts once the user grants access. */
class CaptureNotificationService : NotificationListenerService() {

    override fun onNotificationPosted(sbn: StatusBarNotification) {
        try {
            // Ignore our own notifications and ongoing/foreground-service noise.
            if (sbn.packageName == packageName) return

            val extras = sbn.notification?.extras ?: return
            val title = extras.getCharSequence(Notification.EXTRA_TITLE)?.toString()
            val text = (extras.getCharSequence(Notification.EXTRA_BIG_TEXT)
                ?: extras.getCharSequence(Notification.EXTRA_TEXT))?.toString()

            // Skip notifications with no readable content.
            if (title.isNullOrBlank() && text.isNullOrBlank()) return

            MessageQueue.enqueue(
                ctx = applicationContext,
                type = "notification",
                appPackage = sbn.packageName,
                appName = appLabel(sbn.packageName),
                sender = null,
                title = title,
                body = text,
                receivedAt = sbn.postTime,
            )
            Uploader.kickFlush(applicationContext)
        } catch (_: Exception) {
            // Never crash the listener.
        }
    }

    private fun appLabel(pkg: String): String {
        return try {
            val pm = packageManager
            pm.getApplicationLabel(pm.getApplicationInfo(pkg, 0)).toString()
        } catch (_: Exception) {
            pkg
        }
    }
}
