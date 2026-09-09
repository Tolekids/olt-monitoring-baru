# Software Requirement Specifications (SRS)
## Aplikasi Netio Command — Network & OLT Traffic Monitor

---

### 1. Kebutuhan Sistem & Infrastruktur (System Requirements)

#### A. Kebutuhan Server (Backend)
* **Operating System:** Linux (Ubuntu Server 22.04 LTS / Debian 12 direkomendasikan).
* **Runtime & Framework:** PHP >= 8.2 dengan framework **Laravel 11**.
* **Database Engine:** MySQL 8.0 / MariaDB 10.11+ (Mendukung tipe data JSON & Spasial GIS).
* **Web Server:** Nginx / Apache Web Server.
* **Background Worker & Cron:**
  * **Laravel Queue Worker** (Redis / Database driver) untuk pengolahan SNMP polling secara *asynchronous*.
  * **Cron Job** untuk eksekusi tugas berkala (*scheduled task*) seperti kalkulasi bandwidth harian.
* **Protokol Komunikasi Perangkat:**
  * **SNMP (v1/v2c/v3):** Untuk mengambil telemetri OLT, SFP, dan status PON.
  * **SSH / Telnet (`phpseclib`):** Untuk eksekusi Remote CLI ke OLT.
  * **RouterOS API (Port 8728/8729):** Untuk komunikasi data statistik Mikrotik.
  * **UDP Socket Listener:** Untuk menampung kiriman Syslog dari router/OLT.

#### B. Kebutuhan Frontend & Antarmuka
* **Teknologi UI:** Blade Templating + Tailwind CSS + Alpine.js.
* **Library Grafik Real-Time:** Chart.js / ApexCharts.
* **Peta Interaktif (GIS):** Leaflet.js / Mapbox API.
* **Terminal Emulator:** `xterm.js` via WebSockets / Server-Sent Events (SSE).

---

### 2. Kebutuhan Fungsional (Functional Requirements)

| Modul Antarmuka | ID Requirement | Deskripsi Kebutuhan Fungsional |
| :--- | :---: | :--- |
| **1. Dasbor Utama Monitor** | `FR-01` | Menampilkan statistik real-time pelanggan PPPoE & Static Client (Online/Offline), Aggregate Traffic (RX/TX), serta widget grafik traffic link utama (*Transit Link*, *Backbone Link*, *Peering Link*, *Metro Link*). |
| **2. Peta GIS Area Map** | `FR-02` | Menampilkan peta interaktif sebaran ODP (*Optical Distribution Point*), jalur kabel FO, serta status lokasi ONU/Modem pelanggan (*Online*, *LOS*, *Power Off*). |
| **3. Manajemen ZTE OLT** | `FR-03` | Membaca data konfigurasi & status PON Port OLT via SNMP/SSH, monitoring nilai redaman optik (*RX Power* dBm), dan eksekusi perintah kontrol terhadap ONU (*Reboot*, *Reset*, *Disable/Enable Port*). |
| **4. Telemetri SFP POP** | `FR-04` | Monitoring DDM (*Digital Diagnostics Monitoring*) SFP (Suhu, Voltase, Tx/Rx Power) serta memberikan peringatan visual jika melebihi *threshold* aman. |
| **5. Syslog Real-Time** | `FR-05` | Menerima & mencatat *event log* terpusat dari Router/OLT via UDP Syslog dengan pembaruan tampilan *stream/real-time* tanpa perlu memuat ulang (*refresh*) halaman. |
| **6. Remote CLI OLT** | `FR-06` | Menyediakan terminal interaktif browser berbasis `xterm.js` via SSH dan mencatat seluruh histori perintah ke *Command Audit Logs*. |
| **7. Database Pelanggan** | `FR-07` | Kelola master CRM pelanggan, penambatan ke ONU ID & akun PPPoE, serta fitur isolasi pelanggan (*Isolate Customer*) otomatis/manual. |
| **8. MRTG & Grafik Trafik** | `FR-08` | Menyimpan *time-series data* utilisasi bandwidth interface dan menyajikannya dalam grafik harian (24 Jam), mingguan, bulanan, dan tahunan. |
| **9. ONU Belum Registrasi** | `FR-09` | *Auto-discovery* berkala untuk mendeteksi *Unconfigured ONU* baru pada port PON OLT dan menyediakan opsi registrasi cepat dari dasbor. |
| **10. Antarmuka IP Statis** | `FR-10` | Pengelolaan alokasi subnet/IP Pools, penambatan IP Statis ke MAC Address & Pelanggan, serta cek status aktif via ICMP Ping otomatis. |

---

### 3. Kebutuhan Non-Fungsional (Non-Functional Requirements)

* **Performance (Kinerja):**
  * Waktu muat (*load time*) halaman dasbor tidak boleh melebihi 2 detik.
  * Proses *polling* SNMP berjalan di latar belakang (*queue worker*) tanpa membebani respon antarmuka pengguna.
* **Security (Keamanan):**
  * Penggunaan autentikasi pengguna berbasis *Role-Based Access Control* (RBAC) untuk peran: **Admin**, **NOC**, dan **Teknisi Field**.
  * Kata sandi (*credential*) router dan OLT yang tersimpan di database wajib dienkripsi.
* **Reliability & Availability (Keandalan):**
  * Apabila koneksi SNMP/API ke salah satu perangkat terputus, sistem tidak boleh *crash* dan wajib menampilkan status perangkat sebagai *Offline*.