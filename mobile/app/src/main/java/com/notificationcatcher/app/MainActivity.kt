package com.notificationcatcher.app

import android.Manifest
import android.content.Intent
import android.content.pm.PackageManager
import android.os.Build
import android.os.Bundle
import android.provider.Settings
import android.widget.Button
import android.widget.EditText
import android.widget.TextView
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import androidx.core.app.ActivityCompat
import androidx.core.content.ContextCompat
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext
import org.json.JSONObject

class MainActivity : AppCompatActivity() {

    private val ui = CoroutineScope(Dispatchers.Main)
    private lateinit var serverUrl: EditText
    private lateinit var enrollKey: EditText
    private lateinit var deviceName: EditText
    private lateinit var status: TextView

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_main)

        serverUrl = findViewById(R.id.serverUrl)
        enrollKey = findViewById(R.id.enrollKey)
        deviceName = findViewById(R.id.deviceName)
        status = findViewById(R.id.status)

        // Restore previous values.
        if (Prefs.baseUrl(this).isNotBlank()) serverUrl.setText(Prefs.baseUrl(this))
        if (Prefs.deviceName(this).isNotBlank()) deviceName.setText(Prefs.deviceName(this))

        findViewById<Button>(R.id.btnRegister).setOnClickListener { register() }
        findViewById<Button>(R.id.btnNotifAccess).setOnClickListener {
            startActivity(Intent(Settings.ACTION_NOTIFICATION_LISTENER_SETTINGS))
        }
        findViewById<Button>(R.id.btnSmsPerm).setOnClickListener { requestSms() }
        findViewById<Button>(R.id.btnFlush).setOnClickListener { flushNow() }
    }

    override fun onResume() {
        super.onResume()
        refreshStatus()
    }

    private fun register() {
        val url = serverUrl.text.toString().trim()
        val key = enrollKey.text.toString().trim()
        val name = deviceName.text.toString().trim()
        if (url.isBlank() || !url.startsWith("http") || key.isBlank() || name.isBlank()) {
            toast("Fill in server URL, enroll key and phone name.")
            return
        }

        status.text = "Registering…"
        ui.launch {
            val res = withContext(Dispatchers.IO) {
                ApiClient.register(
                    baseUrl = url,
                    enrollKey = key,
                    uid = Prefs.deviceUid(this@MainActivity),
                    name = name,
                    manufacturer = Build.MANUFACTURER ?: "",
                    model = Build.MODEL ?: "",
                    androidVersion = Build.VERSION.RELEASE ?: "",
                    appVersion = appVersion(),
                )
            }
            if (res.code in 200..299) {
                val token = JSONObject(res.body).optString("token")
                Prefs.saveRegistration(this@MainActivity, url, token, name)
                toast("Registered as \"$name\".")
            } else if (res.code == 403) {
                toast("Wrong enroll key.")
            } else if (res.code == -1) {
                toast("Cannot reach server: ${res.body}")
            } else {
                toast("Registration failed (HTTP ${res.code}).")
            }
            refreshStatus()
        }
    }

    private fun requestSms() {
        if (ContextCompat.checkSelfPermission(this, Manifest.permission.RECEIVE_SMS)
            == PackageManager.PERMISSION_GRANTED) {
            toast("SMS permission already granted.")
            refreshStatus()
            return
        }
        ActivityCompat.requestPermissions(this, arrayOf(Manifest.permission.RECEIVE_SMS), 1)
    }

    override fun onRequestPermissionsResult(
        requestCode: Int, permissions: Array<out String>, grantResults: IntArray,
    ) {
        super.onRequestPermissionsResult(requestCode, permissions, grantResults)
        refreshStatus()
    }

    private fun flushNow() {
        status.text = "Sending…"
        ui.launch {
            val msg = withContext(Dispatchers.IO) { MessageQueue.flush(this@MainActivity) }
            toast(msg)
            refreshStatus()
        }
    }

    private fun refreshStatus() {
        val reg = if (Prefs.isRegistered(this)) "yes (${Prefs.deviceName(this)})" else "no"
        val notif = if (notificationAccessEnabled()) "on" else "OFF — tap button above"
        val sms = if (ContextCompat.checkSelfPermission(this, Manifest.permission.RECEIVE_SMS)
            == PackageManager.PERMISSION_GRANTED) "granted" else "NOT granted"
        val pending = MessageQueue.pendingCount(this)
        status.text = buildString {
            append("Registered: $reg\n")
            append("Notification access: $notif\n")
            append("SMS permission: $sms\n")
            append("Pending to send: $pending")
        }
    }

    private fun notificationAccessEnabled(): Boolean {
        val enabled = Settings.Secure.getString(contentResolver, "enabled_notification_listeners") ?: ""
        return enabled.split(":").any { it.contains(packageName) }
    }

    private fun appVersion(): String = try {
        packageManager.getPackageInfo(packageName, 0).versionName ?: "1.0.0"
    } catch (_: Exception) { "1.0.0" }

    private fun toast(msg: String) = Toast.makeText(this, msg, Toast.LENGTH_LONG).show()
}
