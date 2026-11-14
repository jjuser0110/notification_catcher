package com.notificationcatcher.app

import android.content.Context
import java.util.UUID

/** Simple wrapper over SharedPreferences for the few values we persist. */
object Prefs {
    private const val FILE = "nc_prefs"

    private fun sp(ctx: Context) =
        ctx.applicationContext.getSharedPreferences(FILE, Context.MODE_PRIVATE)

    fun baseUrl(ctx: Context): String = sp(ctx).getString("base_url", "") ?: ""
    fun token(ctx: Context): String = sp(ctx).getString("token", "") ?: ""
    fun deviceName(ctx: Context): String = sp(ctx).getString("device_name", "") ?: ""

    fun isRegistered(ctx: Context): Boolean =
        baseUrl(ctx).isNotBlank() && token(ctx).isNotBlank()

    fun saveRegistration(ctx: Context, baseUrl: String, token: String, name: String) {
        sp(ctx).edit()
            .putString("base_url", baseUrl)
            .putString("token", token)
            .putString("device_name", name)
            .apply()
    }

    /** Stable id for this install, generated once. */
    fun deviceUid(ctx: Context): String {
        val existing = sp(ctx).getString("device_uid", null)
        if (existing != null) return existing
        val uid = UUID.randomUUID().toString()
        sp(ctx).edit().putString("device_uid", uid).apply()
        return uid
    }
}
