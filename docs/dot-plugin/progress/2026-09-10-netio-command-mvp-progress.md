# Netio Command MVP — Progress Report

**Tanggal:** 10 September 2026  
**Branch:** `feat/netio-command-mvp`  
**Status:** In development

## Ringkasan

Fondasi backend MVP Operasional NOC dan frontend UI awal sudah tersedia dan dapat dijalankan menggunakan MySQL serta Redis melalui Docker. API, persistence, RBAC, polling queue, syslog ingestion, dashboard UI, device management UI, dan dasar remote CLI sudah dibuat.

Fitur belum siap untuk production atau integrasi perangkat nyata karena polling OLT/RouterOS masih membutuhkan pengujian OID, credential, dan response dari perangkat lab. Terminal WebSocket interaktif, grafik traffic, dan beberapa fitur UI lanjutan masih belum selesai.

## Yang sudah dikerjakan

### Project baseline

- Upgrade Laravel 9 ke Laravel 11.56.1.
- Upgrade requirement PHP ke 8.2+.
- Upgrade PHPUnit dan dependency Laravel yang kompatibel.
- Tambah Redis ke `docker-compose.yml`.
- Tambah Laravel Reverb sebagai fondasi WebSocket.
- Tambah RouterOS API client.
- Tambah `phpseclib` untuk SSH/Telnet.
- Tambah Spatie Laravel Permission untuk RBAC.

### Database dan persistence

- Membuat schema DBML di `docs/database/netio-command.dbml`.
- Membuat OpenAPI split-file di `docs/openapi/`.
- Membuat migration untuk:
  - devices
  - device credentials
  - OLT PON ports
  - ONUs
  - device metrics
  - traffic samples
  - polling runs
  - syslog events
  - CLI sessions
  - command audit logs
- Menambahkan model Eloquent dan relasi utama.
- Credential perangkat menggunakan encrypted cast.
- Credential tidak dikembalikan oleh API resource.

### Authentication dan authorization

- Menambahkan role:
  - Admin
  - NOC
  - Teknisi Field
- Menambahkan permission untuk dashboard, device management, syslog, CLI, kontrol ONU, dan audit log.
- Menambahkan middleware permission ke route API.
- Menambahkan endpoint login, logout, dan user profile menggunakan Sanctum.
- Menambahkan seed administrator:
  - Email: `admin@netio.local`
  - Password awal: `password`

### Device adapter

- Membuat interface adapter perangkat.
- Membuat adapter ZTE SNMP dasar.
- Membuat adapter ZTE SSH dasar.
- Membuat adapter MikroTik RouterOS API dasar.
- Membuat normalized data object untuk connection result dan device snapshot.
- Menambahkan konfigurasi timeout dan OID system name ZTE.

### Polling dan monitoring

- Membuat queue job:
  - `PollZteOltJob`
  - `PollMikrotikJob`
  - `PollTrafficInterfacesJob`
  - `DiscoverOnusJob`
- Membuat command `monitoring:poll-devices`.
- Menambahkan scheduler polling setiap menit.
- Menyimpan polling run dengan status running/completed/failed.
- Kegagalan polling menandai perangkat offline dan tidak menghentikan batch perangkat lain.
- Membuat query dashboard summary dan traffic.

### Syslog

- Membuat parser syslog priority/facility/severity.
- Membuat command UDP listener `syslog:listen`.
- Menyimpan syslog ke database.
- Mencocokkan source IP syslog dengan perangkat terdaftar.
- Membuat endpoint histori syslog.
- Membuat endpoint SSE untuk stream syslog.

### Remote CLI

- Membuat lifecycle CLI session: open, execute, close.
- Menambahkan allowlist command read-only pada MVP.
- Menyimpan command audit log.
- Menambahkan authorization channel untuk CLI session.
- Menambahkan endpoint dasar CLI session dan eksekusi command.

### Frontend dan dokumentasi

- Mengganti halaman Laravel default dengan UI NOC berbasis Vite dan vanilla JavaScript.
- Membuat halaman login Sanctum dengan penyimpanan token di browser.
- Membuat layout dashboard dengan sidebar, topbar, status live polling, dan responsive mobile navigation.
- Menampilkan summary devices, ONU, aggregate traffic, dan power-off ONU dari API.
- Membuat halaman network devices dengan search, status, last seen, dan test connection.
- Membuat modal tambah device untuk ZTE dan MikroTik.
- Membuat halaman syslog events dengan pencarian dan severity label.
- Menambahkan logout, refresh data, error toast, dan empty state.
- Menyediakan fallback akun development pada halaman login: `admin@netio.local` / `password`.
- Memperbarui README dengan service yang perlu dijalankan.
- Memperbaiki deprecation `PDO::MYSQL_ATTR_SSL_CA` untuk PHP 8.5 melalui Composer script.
- Memperbarui spec agar konsisten dengan RouterOS v6/v7, tiga role, dan isolasi kegagalan perangkat.

## Yang sudah diverifikasi

### Automated verification

- `composer validate` lulus.
- Semua file PHP bebas syntax error.
- `php artisan test` lulus:
  - 8 tests
  - 15 assertions
- `npm run build` lulus.
- UI production bundle berhasil dibuat dengan Vite.
- OpenAPI Redocly lint lulus.
- YAML OpenAPI dapat diparse.
- Migration dan seeder lulus menggunakan SQLite in-memory.

### Runtime verification

- Container MySQL aktif di port `3306`.
- Container Redis aktif di port `6379`.
- Migration dan seeder berhasil dijalankan ke MySQL Docker.
- Redis merespons `PONG`.
- `monitoring:poll-devices` berhasil dijalankan.
- Queue worker Redis berhasil dijalankan.
- `php artisan about` tidak lagi menampilkan deprecation PDO.

## Yang masih parsial

### Adapter dan polling perangkat

- OID ZTE untuk PON port, ONU status, serial number, dan RX power belum lengkap.
- Auto-discovery ONU belum melakukan upsert data ONU dari response perangkat nyata.
- Polling traffic masih menyimpan response interface dasar; konversi counter menjadi rate RX/TX berbasis interval belum selesai.
- Statistik PPPoE/static client MikroTik belum dinormalisasi secara lengkap.
- Adapter RouterOS belum diuji terhadap perangkat lab.
- SSH/Telnet ZTE belum diuji terhadap prompt dan format CLI perangkat lab.

### Remote CLI

- Endpoint CLI dasar sudah ada.
- Terminal browser interaktif berbasis `xterm.js` dan WebSocket belum selesai.
- Reverb sudah terpasang, tetapi bridge input/output terminal belum diimplementasikan penuh.
- Command allowlist masih read-only dan belum memiliki konfigurasi command per role.

### Syslog

- UDP listener dan SSE tersedia.
- Parser saat ini masih menangani format priority dasar.
- Parser format vendor ZTE/MikroTik, hostname, timestamp perangkat, dan structured payload belum lengkap.
- UI histori/filter syslog dasar sudah tersedia; live SSE belum terhubung.

### Frontend

- Login UI sudah dibuat dan terhubung ke endpoint Sanctum.
- Dashboard operasional sudah tersedia dan membaca data API.
- Halaman device management dasar sudah tersedia.
- Halaman histori syslog dasar sudah tersedia.
- Grafik traffic belum selesai.
- Tampilan status ONU dan polling error belum selesai.
- Live update via SSE belum dihubungkan ke komponen UI.
- UI remote CLI belum dibuat.

## Yang belum dikerjakan

Pekerjaan berikut belum dimulai atau berada di luar MVP saat ini:

- Integrasi dan verifikasi dengan perangkat lab ZTE C300/C320.
- Integrasi dan verifikasi dengan perangkat lab MikroTik RouterOS v6/v7.
- Terminal WebSocket interaktif penuh.
- Dashboard NOC operasional lengkap dengan grafik dan detail ONU.
- UI CRUD device management lengkap, termasuk edit dan delete.
- Integrasi live stream syslog ke UI.
- DDM SFP POP.
- GIS ODP dan jalur kabel FO.
- CRM pelanggan lengkap.
- Isolasi pelanggan.
- IP pool dan IP statis.
- Traffic history harian/mingguan/bulanan/tahunan.
- ONU unregistered registration workflow.
- Multi-vendor selain ZTE dan MikroTik.
- High availability multi-node.
- Retention command untuk metric dan traffic history.
- Production deployment configuration.
- Monitoring aplikasi dan alerting production.

## Known issues dan risiko

1. Laravel 11.56.1 masih terdeteksi oleh `composer audit` memiliki advisory keamanan dari advisory database saat ini. Versi Laravel perlu ditinjau sebelum production deployment.
2. Docker Compose menampilkan warning bahwa field `version` sudah obsolete.
3. Konfigurasi syslog port default `514` membutuhkan privilege/network setup yang sesuai saat dijalankan bukan sebagai root.
4. Data device credential hanya aman jika `APP_KEY` production dijaga dan tidak berubah.
5. Command CLI read-only tetap perlu diuji terhadap format prompt dan behavior ZTE yang sebenarnya.

## Prioritas berikutnya

### Prioritas 1 — Integrasi perangkat nyata

1. Tambahkan device lab ZTE dan credential SNMP/SSH.
2. Capture response OID ZTE C300/C320.
3. Lengkapi parser PON, ONU, status, dan RX power.
4. Tambahkan test fixture dari response perangkat nyata.
5. Tambahkan device lab MikroTik dan verifikasi RouterOS API.

### Prioritas 2 — Penyempurnaan UI operasional

1. Tambahkan grafik traffic berbasis endpoint dashboard traffic.
2. Tambahkan detail status ONU, PON port, dan polling error.
3. Hubungkan SSE syslog ke live event list.
4. Tambahkan edit dan delete device.
5. Tambahkan validasi credential berbeda untuk SNMP, SSH, dan RouterOS API.

### Prioritas 3 — Remote CLI real-time

1. Buat WebSocket session bridge melalui Reverb.
2. Integrasikan `xterm.js`.
3. Tambahkan reconnect dan idle timeout.
4. Tampilkan audit status dan session history.

## Cara menjalankan saat ini

```bash
docker compose up -d
php artisan migrate --seed
php artisan serve
php artisan queue:work --queue=device-polling
php artisan schedule:work
php artisan syslog:listen
php artisan reverb:start
```

Branch kerja saat ini adalah `feat/netio-command-mvp`. Perubahan belum di-commit atau di-push.
