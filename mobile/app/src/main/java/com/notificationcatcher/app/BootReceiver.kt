package com.notificationcatcher.app

import android.content.BroadcastReceiver
import android.content.Context
import android.content.Intent

/** After a reboot, push anything that was queued while offline. */
class BootReceiver : BroadcastReceiver() {
    override fun onReceive(context: Context, intent: Intent) {
        if (intent.action == Intent.ACTION_BOOT_COMPLETED) {
            Uploader.kickFlush(context.applicationContext)
        }
    }
}
