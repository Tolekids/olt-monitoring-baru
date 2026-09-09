# Netio Command — MVP Operasional NOC

## Status

Disetujui untuk penyusunan implementation plan.

## Tujuan

Membangun MVP aplikasi Netio Command untuk monitoring dan operasi NOC dengan dukungan ZTE OLT C300/C320 serta MikroTik RouterOS v3-5.

MVP mencakup:

- Dashboard monitoring perangkat, ONU, dan traffic.
- Polling SNMP/API secara asynchronous.
- Syslog real-time.
- Remote CLI OLT melalui SSH/Telnet.
- Command audit log.
- Role-Based Access Control untuk Admin, NOC, dan Teknisi Field.

Fitur GIS, CRM lengkap, IP pool, dan modul ONU unregistered penuh berada di fase berikutnya, kecuali data yang diperlukan untuk monitoring dasar.

## Keputusan desain

### Pendekatan

Gunakan modular monolith Laravel. Aplikasi tetap berada dalam satu repository dan satu deployment, tetapi setiap domain memiliki service, job, policy, dan adapter yang terpisah.

Pendekatan ini dipilih karena:

- Sesuai dengan ukuran MVP dan kebutuhan deployment sederhana.
- Memudahkan pengujian menggunakan mock adapter.
- Menjaga batas domain sebelum sistem perlu dipisah menjadi service independen.
- Menghindari ketergantungan langsung antara controller dan protokol perangkat.

### Baseline teknologi

- PHP 8.2+.
- Laravel 11.
- MySQL 8.0.
- Redis untuk queue dan cache.
- Blade, Tailwind CSS, Alpine.js.
- Chart.js atau ApexCharts untuk grafik.
- SSE untuk dashboard dan syslog.
- WebSocket untuk terminal CLI.
- `phpseclib` untuk SSH/Telnet.
- SNMP dan RouterOS API melalui adapter terpisah.

Repository saat ini masih menggunakan Laravel 9 dan perlu di-upgrade sebelum implementasi fitur MVP.

### Alur data monitoring

1. Scheduler membuat job polling berdasarkan perangkat dan intervalnya.
2. Queue worker menjalankan job tanpa memblokir request web.
3. Adapter perangkat berkomunikasi melalui SNMP, RouterOS API, SSH, atau Telnet.
4. Hasil yang sudah dinormalisasi disimpan ke database.
5. Event perubahan status dipublikasikan ke channel real-time.
6. Dashboard menerima pembaruan melalui SSE.

Kegagalan komunikasi di satu perangkat tidak harus diisolasi. Job mencatat error, menandai perangkat offline, dan tidak boleh menghentikan worker atau membuat request dashboard gagal.

## Struktur modul

### Authentication dan RBAC

Role MVP:

- **Admin:** seluruh akses.
- **NOC:** dashboard, monitoring, syslog, dan remote CLI sesuai izin.
- **Teknisi Field:** monitoring serta remote CLI terbatas.

Credential perangkat disimpan terenkripsi. Password dan secret tidak boleh masuk ke log aplikasi, output audit, atau exception message.

### Device Management

Menyediakan pengelolaan perangkat, credential, protokol, test koneksi, status online/offline, polling aktif/nonaktif, dan error terakhir.

Jenis perangkat awal:

- ZTE OLT C300/C320.
- MikroTik RouterOS v6/v7.

### Polling

Job dipisahkan berdasarkan tujuan:

- `PollZteOltJob` untuk status OLT, PON, dan ONU.
- `PollMikrotikJob` untuk status router dan client session.
- `PollTrafficInterfacesJob` untuk RX/TX interface.
- `DiscoverOnusJob` untuk penemuan ONU.

Adapter mengembalikan data terstruktur dan tidak mengekspos format response vendor ke controller atau view.

### Syslog

UDP listener menerima syslog dari router dan OLT. Event disimpan, dipetakan ke perangkat berdasarkan source IP, lalu dipublikasikan melalui SSE. UI menyediakan filter berdasarkan perangkat, severity, waktu, dan kata kunci.

### Remote CLI

Backend membuka sesi SSH/Telnet menggunakan service khusus. WebSocket meneruskan input dan output terminal. Setiap sesi menyimpan user, perangkat, waktu mulai/selesai, status, dan command audit log.

### Dashboard

Widget awal:

- Total perangkat online dan offline.
- Jumlah ONU online, LOS, dan power off.
- Aggregate RX/TX.
- Status MikroTik.
- Grafik traffic interface.
- Syslog terbaru.
- Error polling terbaru.

## Skema database

### Tabel utama

```text
users
- id, name, email, password, timestamps

roles / permissions
- dikelola melalui RBAC package

devices
- id, name, vendor, model, device_type
- management_ip, status, is_polling_enabled
- last_seen_at, last_error, timestamps, deleted_at

device_credentials
- id, device_id, protocol, port, username
- encrypted_password, encrypted_community, timestamps

olt_pon_ports
- id, device_id, slot, port, name
- status, rx_power_threshold, timestamps

onus
- id, olt_pon_port_id, onu_identifier, serial_number
- name, status, rx_power, last_seen_at, timestamps

device_metric_samples
- id, device_id, metric_name, metric_value, recorded_at

traffic_samples
- id, device_id, interface_name
- rx_bps, tx_bps, recorded_at

syslog_events
- id, device_id nullable, source_ip, facility, severity
- message, received_at, parsed_at

cli_sessions
- id, user_id, device_id, protocol
- started_at, ended_at, status

command_audit_logs
- id, cli_session_id, user_id, device_id
- command, output_excerpt, executed_at, success

device_poll_runs
- id, device_id, poll_type
- started_at, finished_at, status, error_message
```

Semua foreign key dan kolom query utama harus memiliki indeks. Data time-series perlu memiliki indeks gabungan berdasarkan perangkat, metrik/interface, dan waktu. Retention data traffic ditentukan dalam konfigurasi agar volume database tetap terkendali.

## Rancangan API

```text
POST   /api/v1/auth/login
POST   /api/v1/auth/logout
GET    /api/v1/me

GET    /api/v1/devices
POST   /api/v1/devices
GET    /api/v1/devices/{device}
PATCH  /api/v1/devices/{device}
DELETE /api/v1/devices/{device}
POST   /api/v1/devices/{device}/test-connection

GET    /api/v1/devices/{device}/status
GET    /api/v1/devices/{device}/metrics
GET    /api/v1/devices/{device}/traffic

GET    /api/v1/olts/{olt}/pon-ports
GET    /api/v1/pon-ports/{ponPort}/onus
POST   /api/v1/onus/{onu}/reboot
POST   /api/v1/onus/{onu}/disable
POST   /api/v1/onus/{onu}/enable

GET    /api/v1/syslog-events
GET    /api/v1/syslog-events/stream

GET    /api/v1/cli/sessions
POST   /api/v1/cli/sessions
POST   /api/v1/cli/sessions/{session}/close

GET    /api/v1/audit-logs
GET    /api/v1/dashboard/summary
GET    /api/v1/dashboard/traffic
```

Endpoint kontrol ONU dan CLI wajib melewati authorization policy, validasi perangkat, audit logging, dan batasan command sesuai izin user.

## Strategi real-time

- SSE digunakan untuk stream syslog dan perubahan ringkasan dashboard.
- WebSocket digunakan untuk input/output terminal CLI dua arah.
- Event real-time tidak menjadi sumber data utama. Database tetap menjadi sumber kebenaran untuk histori dan pemuatan ulang halaman.
- Client melakukan reconnect dengan backoff ketika koneksi SSE atau WebSocket terputus.

## Strategi pengujian

PHPUnit digunakan sebagai framework utama setelah upgrade ke Laravel 11.

### Unit test

- Parser dan normalisasi response SNMP.
- Parser syslog.
- Normalisasi status ONU dan perangkat.
- Perhitungan RX/TX dan aggregate traffic.
- Validasi command CLI.

### Feature/API test

- Login, logout, dan role authorization.
- CRUD perangkat dan test koneksi.
- Penyimpanan credential terenkripsi.
- Dashboard ketika seluruh perangkat online.
- Dashboard ketika salah satu perangkat offline.
- Penerimaan dan filtering syslog.
- Pembuatan serta penutupan sesi CLI.
- Pencatatan command audit log.
- Kontrol ONU dengan izin dan tanpa izin.

### Integration test

Adapter ZTE dan MikroTik diuji menggunakan mock response yang mencakup response valid, timeout, autentikasi gagal, data tidak lengkap, dan format vendor yang tidak dikenal.

Perangkat lab digunakan untuk verifikasi manual SNMP, SSH/Telnet, RouterOS API, dan UDP syslog.

## Batasan fase

Fase ini tidak mencakup:

- GIS ODP dan jalur kabel.
- CRM pelanggan lengkap.
- IP pool dan IP statis.
- Isolasi pelanggan.
- DDM SFP sebagai modul khusus.
- Multi-vendor selain ZTE dan MikroTik.
- High availability multi-node.

Fitur tersebut dapat ditambahkan setelah alur monitoring dan operasi NOC stabil.
