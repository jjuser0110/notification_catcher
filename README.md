# Notification Catcher

Collect **notifications** and **incoming SMS** from one or more Android phones into a
central Laravel backend, and browse them in a web dashboard that shows which phone each
message came from.

```
mobile/   → native Android app (Kotlin) that captures and uploads
backend/  → Laravel 13 app: login + dashboard + device API
```

---

## 1. Backend (Laravel + MySQL)

Already installed and migrated. To run it:

```bash
cd backend
php artisan serve --host=0.0.0.0 --port=8000
```

> Use `--host=0.0.0.0` so your phones on the same Wi-Fi can reach it.
> With Laragon you can also just use the pretty URL `http://notification_catcher.test`.

**Dashboard login** (set in `backend/.env`):

- Username: `admin`
- Password: *(see `ADMIN_PASSWORD` in `backend/.env`)*

Change or add a login any time:

```bash
cd backend
php artisan make:admin someuser
```

**Enroll key** — the shared secret a phone needs to register. It lives in
`backend/.env` as `DEVICE_ENROLL_KEY`. Treat it like a password.

### What the dashboard shows
- **Devices page** — every installed phone, whether it's online, its model/Android
  version, how many messages it sent, and when it was last seen.
- **Messages page** — all notifications and SMS, filterable by phone, by type
  (notification / SMS), and by a text search. Each row shows which phone it came from.

### API the app uses
| Method | Route | Purpose |
|--------|-------|---------|
| POST | `/api/devices/register` | One-time enrollment (needs the enroll key). Returns a device token. |
| GET  | `/api/devices/ping` | Heartbeat (Bearer token). |
| POST | `/api/messages` | Upload a batch of captured items (Bearer token). Duplicates are ignored. |

---

## 2. Android app (APK)

The built, installable APK is at the project root: **`NotificationCatcher.apk`**
(debug-signed, ~2.9 MB).

### Install
1. Copy `NotificationCatcher.apk` to the phone and open it.
2. Allow "install from unknown sources" when prompted.

### Set up on each phone
1. Open **Notification Catcher**.
2. **Server URL** — your computer's address on the Wi-Fi, e.g. `http://192.168.50.213:8000`
   (this machine's current IP is `192.168.50.213`; it can change).
3. **Enroll key** — the `DEVICE_ENROLL_KEY` from `backend/.env`.
4. **Phone name** — e.g. `Phone 1`, `Dad's phone`. This is the label you'll see in the
   dashboard, so give each phone a different name.
5. Tap **Register device**.
6. Tap **Enable notification access** → turn on *Notification Catcher* in the system
   list (this is what lets it read notifications).
7. Tap **Grant SMS permission** → Allow.

From then on, new notifications and incoming SMS upload automatically. The app's status
box shows registration, both permissions, and how many items are waiting to send. The
**Send pending now** button forces an immediate upload.

> 5 phones = register the app on each with a different **Phone name**. Every message is
> tagged with its phone in the database and dashboard.

---

## Rebuild the APK from source

```bash
cd mobile
# JDK 17 required
gradle :app:assembleDebug
# output: mobile/app/build/outputs/apk/debug/app-debug.apk
```

Build toolchain used: Gradle 8.2.1, Android Gradle Plugin 8.2.1, Kotlin 1.9.22,
compileSdk 34, minSdk 24.

---

## Notes & limits
- The app captures **incoming** SMS as they arrive (it does not read your old SMS
  history) and notifications posted **after** access is granted.
- Uploads use plain HTTP to your local server, so the app allows cleartext traffic.
  For use outside your LAN, put the backend behind HTTPS and update the Server URL.
- A phone shows as **Offline** in the dashboard after ~30 minutes of silence.
- Reading notifications/SMS and sending them to a server is sensitive. Only install
  this on phones you own or have permission to monitor.
