1. Cara Kerja Pembayaran Virtual Account (VA) di Production
Di tahap production, pembayaran Virtual Account akan terintegrasi penuh dengan Payment Gateway (seperti Midtrans, Xendit, atau Tripay). Prosesnya berjalan otomatis tanpa campur tangan admin (Zero-Touch Payment):

Pembuatan VA: Saat pelanggan melakukan checkout dan memilih metode Virtual Account (misal: BCA, Mandiri, BNI), Laravel API akan mengirim request ke Payment Gateway untuk men-generate nomor VA unik untuk transaksi tersebut.
Pembayaran oleh Pelanggan: Pelanggan membayar tagihan melalui ATM, Mobile Banking, atau Internet Banking menggunakan nomor VA tersebut.
Notifikasi Otomatis (Webhook):
Setelah pembayaran sukses, Bank akan memberi tahu Payment Gateway secara real-time.
Payment Gateway kemudian akan mengirimkan data sukses pembayaran ke website kita melalui Webhook (Notification URL, biasanya berupa endpoint di Laravel seperti /api/payment/callback atau /api/payment/notification).
Update Status Otomatis: Backend Laravel menerima notifikasi tersebut, memverifikasi keamanannya (signature key), mencocokkan ID order, dan otomatis mengubah status pesanan dari "Pending" menjadi "Paid" (Lunas).
Aksi Lanjutan: Sistem otomatis mengirimkan WhatsApp/Email notifikasi ke pembeli dan memotong stok produk secara otomatis.
2. Cara Kerja Tracking Ekspedisi di Production
Untuk status pelacakan/tracking pengiriman barang, pembagian tugasnya adalah sebagai berikut:

A. Peran Admin (Input Awal)
Hanya menginput Nomor Resi (Airway Bill / AWB) sekali saja.
Saat admin selesai mengemas barang dan mengirimkannya melalui ekspedisi (JNE, J&T, SiCepat, dll.), pihak ekspedisi akan memberikan Nomor Resi. Admin cukup memasukkan Nomor Resi ini ke dalam sistem dashboard admin untuk order terkait, lalu mengubah status order dari "Diproses" (Processing) menjadi "Dikirim" (Shipped).
B. Peran Pihak Ekspedisi & Sistem (Tracking Berjalan Otomatis)
Setelah nomor resi diinput, kita tidak perlu mengupdate posisi paket secara manual di admin panel kita. Status tracking akan di-update secara otomatis menggunakan Tracking API pihak ketiga (seperti RajaOngkir (tipe Pro), Biteship, atau BinderByte):

Sinkronisasi Otomatis (Pull / Polling):
Sistem website Anda akan secara berkala (misal menggunakan Laravel Scheduler / Cron Job setiap 4-6 jam sekali) memanggil API Tracking dengan parameter nomor resi yang aktif.
API akan mengembalikan detail perjalanan paket terbaru langsung dari database ekspedisi (misal: "Paket berada di gudang Jakarta", "Sedang diantar kurir", atau "Diterima oleh [Nama]").
Sistem Anda akan memperbarui riwayat tracking di database dan menampilkannya di halaman akun pembeli.
Notifikasi Otomatis (Push / Webhook):
Jika Anda menggunakan agregator logistik seperti Biteship, mereka memiliki fitur Webhook. Pihak Biteship/ekspedisi akan langsung mengirimkan data ke website Anda setiap kali kurir meng-update status paket di lapangan.
Ketika status kurir berubah menjadi "Delivered" (Sampai), sistem Anda bisa otomatis mengubah status pesanan menjadi "Selesai" (Completed) dan mengirim notifikasi meminta pembeli untuk memberikan ulasan.
Ringkasan Alur Kerja di Production:
Fitur	Siapa yang Input/Proses?	Cara Kerjanya di Sistem
Pembayaran VA	Otomatis (Pembeli & Payment Gateway)	Sistem mendeteksi pembayaran sukses lewat webhook, lalu otomatis mengubah status order menjadi Paid.
Input Pengiriman	Admin Kita	Admin mengirim barang ke ekspedisi, mendapatkan nomor resi, lalu menginput nomor resi tersebut ke sistem.
Update Posisi Paket	Otomatis (Pihak Ekspedisi via API)	Sistem secara otomatis mengambil (fetch) update pengiriman dari server kurir/ekspedisi secara berkala tanpa admin perlu menginput ulang.