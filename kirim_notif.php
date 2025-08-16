<?php
/**
 * Script ini dijalankan oleh Cron Job untuk mengirim notifikasi
 * dari data yang ada di visitor_log.txt.
 * * Perintah Cron Job di cPanel:
 * /usr/local/bin/php /home/username_cpanel/public_html/kirim_notif.php >/dev/null 2>&1
 */

// Pastikan script hanya bisa dijalankan via command line (CLI), bukan browser
if (php_sapi_name() !== 'cli') {
    die("Akses ditolak. Script ini hanya untuk dijalankan via CLI.");
}

/**
 * Fungsi untuk mengirim notifikasi via WhatsApp API berdasarkan data dari log.
 * @param string $apiKey API Key Anda.
 * @param string $sender Nomor pengirim (nomor device Anda).
 * @param string $recipient Nomor penerima notifikasi.
 * @param array $data Data pengunjung dari file log.
 */
function send_notification_from_log($apiKey, $sender, $recipient, $data) {
    // Format waktu agar mudah dibaca
    $timestamp = date('d-m-Y H:i:s T', $data['timestamp']);

    // Siapkan pesan notifikasi
    $message = "🔔 *Ada Pengunjung Baru!* 🔔\n\n" .
               "*Waktu:* " . $timestamp . "\n" .
               "*IP Address:* " . htmlspecialchars($data['ip']) . "\n" .
               "*Sumber:* " . htmlspecialchars($data['referrer']) . "\n\n" .
               "*User Agent:*\n" . htmlspecialchars($data['user_agent']);

    $footer = "Notifikasi Otomatis dari sejukservice.my.id";

    // Siapkan body request dalam format JSON
    $requestBody = json_encode([
        'api_key' => $apiKey,
        'sender'  => $sender,
        'number'  => $recipient,
        'message' => $message,
        'footer'  => $footer
    ]);

    // Kirim request menggunakan cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://wassap.sejukservice.my.id/send-message');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBody);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Content-Length: ' . strlen($requestBody)]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Timeout bisa sedikit lebih lama di cron
    @curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Memberi tahu di console jika pengiriman berhasil (untuk debugging)
    if ($http_code == 200) {
        echo "Notifikasi untuk IP " . $data['ip'] . " berhasil dikirim.\n";
    } else {
        echo "Gagal mengirim notifikasi untuk IP " . $data['ip'] . ". HTTP Code: " . $http_code . "\n";
    }
}


// --- LOGIKA UTAMA SCRIPT CRON ---

// Definisikan path file log. __DIR__ memastikan path selalu benar.
$log_file = __DIR__ . '/visitor_log.txt';
$log_processing_file = __DIR__ . '/visitor_log.processing.txt';

// 1. Cek jika file log ada dan tidak kosong
if (!file_exists($log_file) || filesize($log_file) === 0) {
    echo "Tidak ada log pengunjung baru. Script berhenti.\n";
    exit;
}

// 2. Ganti nama file log (atomic operation) untuk mencegah proses ganda
if (!rename($log_file, $log_processing_file)) {
    echo "Gagal me-rename file log. Script berhenti untuk mencegah duplikasi.\n";
    exit;
}

// 3. Baca semua data dari file yang sedang diproses
// FILE_IGNORE_NEW_LINES: menghilangkan karakter baris baru
// FILE_SKIP_EMPTY_LINES: mengabaikan baris kosong
$visitors_json = file($log_processing_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

// 4. Hapus file yang sudah selesai dibaca
unlink($log_processing_file);

// 5. Konfigurasi Notifikasi Anda (MASUKKAN DATA ANDA DI SINI)
$notification_api_key = 'Is0gvvDSAeAnYIph5ikA247ftZQKKD';
$notification_sender = '62887437496444';
$notification_recipient = '6281265604716';

echo "Memulai proses pengiriman " . count($visitors_json) . " notifikasi...\n";

// 6. Kirim notifikasi untuk setiap pengunjung yang tercatat
foreach ($visitors_json as $json_line) {
    $visitor_data = json_decode($json_line, true);
    
    // Pastikan JSON valid sebelum mengirim
    if (is_array($visitor_data)) {
        send_notification_from_log(
            $notification_api_key, 
            $notification_sender, 
            $notification_recipient, 
            $visitor_data
        );
        // Beri jeda 1 detik antar pengiriman untuk menghindari pembatasan dari API
        sleep(1); 
    }
}

echo "Proses notifikasi selesai.\n";

?>
