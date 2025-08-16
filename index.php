<?php

// =================================================================================
// LOGGING PENGUNJUNG UNTUK CRON JOB
// =================================================================================
// Hindari logging untuk bot/crawler umum
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
if (!preg_match('/bot|crawler|spider|slurp|google/i', $userAgent)) {
    // Kumpulkan data pengunjung
    $visitor_data = [
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'N/A',
        'referrer' => $_SERVER['HTTP_REFERER'] ?? 'Akses Langsung',
        'user_agent' => $userAgent,
        'timestamp' => time() // Gunakan UNIX timestamp untuk kemudahan
    ];

    // Ubah data menjadi format JSON dan simpan ke file log
    // FILE_APPEND: Menambahkan data baru tanpa menghapus yang lama
    // LOCK_EX: Mencegah file ditulis oleh beberapa proses secara bersamaan
    @file_put_contents('visitor_log.txt', json_encode($visitor_data) . PHP_EOL, FILE_APPEND | LOCK_EX);
}
// =================================================================================


/**
 * Fungsi untuk mengambil data katalog dari URL JSON dengan sistem CACHING.
 * Ini mencegah server melakukan request berulang kali dan mempercepat TTFB.
 *
 * @param string $url URL sumber data JSON.
 * @return array Data katalog layanan.
 */
function get_service_catalog($url) {
    $cache_file = 'harga.cached.json';
    $cache_time_seconds = 3600; // Durasi cache 1 jam (3600 detik)

    // Cek apakah file cache ada dan belum kedaluwarsa
    if (file_exists($cache_file) && (time() - filemtime($cache_file)) < $cache_time_seconds) {
        // Jika cache valid, baca dari file cache
        $json_data = @file_get_contents($cache_file);
    } else {
        // Jika cache tidak valid, ambil data baru dari URL
        $options = [
            'http' => [
                'method' => 'GET',
                'header' => 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36\r\n',
                'ignore_errors' => true
            ]
        ];
        $context = stream_context_create($options);
        $json_data = @file_get_contents($url, false, $context);

        // Jika berhasil, simpan data baru ke file cache
        if ($json_data !== FALSE) {
            @file_put_contents($cache_file, $json_data, LOCK_EX);
        }
    }
    
    // Proses decode JSON
    if ($json_data === FALSE) { return []; }
    $data = json_decode($json_data, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
        return $data;
    }
    return [];
}


// URL Katalog JSON dari sumber online
$catalog_url = 'https://www.sejukservice.my.id/harga.json';
$services = get_service_catalog($catalog_url);


// Variabel Kontak & Tautan
$whatsapp_number = '62887437496444'; 
$whatsapp_message = 'Halo Sejuk Service Indonesia, saya tertarik dengan layanan Anda.';
$whatsapp_link = 'https://api.whatsapp.com/send?phone=' . $whatsapp_number . '&text=' . urlencode($whatsapp_message);
$email_address = 'admin@sejukservice.my.id';
$phone_number = '0887437496444';

// Helper untuk nama kategori
$category_names = [
    'cuci_ac' => 'Cuci AC',
    'perbaikan_ac' => 'Perbaikan',
    'isi_tambah_freon' => 'Isi & Perbaikan Freon',
    'paket' => 'Paket Spesial',
    'instalasi_dan_lainnya' => 'Instalasi & Lainnya',
    'promo_spesial' => 'Promo',
];

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sejuk Service Indonesia - Solusi HVAC Rumah Anda</title>
    <meta name="description" content="Sejuk Service Indonesia menyediakan layanan cuci AC, perbaikan, dan bongkar pasang AC profesional untuk kenyamanan rumah Anda. Cepat, handal, dan terpercaya.">
    <meta name="keywords" content="service ac, cuci ac, perbaikan ac, bongkar pasang ac, hvac, service ac jakarta, sejuk service">
    
    <link rel="preconnect" href="https://unpkg.com" crossorigin>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <link rel="preload" href="https://unpkg.com/aos@2.3.1/dist/aos.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    

    <noscript>
      <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap">
      <link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">
    </noscript>
    
    <style>
/* === TEMA MODERN & RAMAH LINGKUNGAN === */
:root {
    --primary-color: #28a745;    /* Hijau segar dan positif */
    --secondary-color: #218838;   /* Hijau lebih gelap untuk hover */
    --tertiary-color: #ffc107;     /* Kuning sebagai aksen tambahan (misal: badge promo) */
    --primary-gradient: linear-gradient(135deg, #28a745, #218838);
    
    --background-color: #ffffff;  /* Latar belakang putih bersih */
    --text-color: #343a40;        /* Abu-abu gelap untuk kontras yang nyaman dibaca */
    --card-bg: #fdfdfd;           /* Warna kartu sedikit berbeda dari BG */
    --footer-bg: #212529;         /* Footer abu-abu sangat gelap, modern */
    --shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    --border-radius: 10px;
}
/* === AKHIR TEMA === */

* { margin: 0; padding: 0; box-sizing: border-box; }
html { scroll-behavior: smooth; }
body { font-family: 'Poppins', sans-serif; background-color: var(--background-color); color: var(--text-color); line-height: 1.6; }
.container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
header { background-color: var(--card-bg); padding: 10px 0; position: sticky; top: 0; z-index: 1000; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05); }
.nav-container { display: flex; justify-content: space-between; align-items: center; }

.logo-container { display: flex; align-items: center; text-decoration: none; }
/* [OPTIMASI] Pastikan logo memiliki dimensi yang ditetapkan untuk mencegah layout shift */
.logo-img { height: 45px; width: 45px; } 
.logo-text { font-size: 1.6em; font-weight: 700; color: var(--text-color); margin-left: 10px; }
.logo-text span { color: var(--primary-color); }

nav ul { list-style: none; display: flex; gap: 30px; }
nav a { text-decoration: none; color: var(--text-color); font-weight: 600; transition: color 0.3s ease; }
nav a:hover { color: var(--primary-color); }
.menu-toggle { display: none; font-size: 2em; cursor: pointer; color: var(--text-color); }

.hero {
    background-color: #f8f9fa; /* Latar hero abu-abu terang, bukan gradasi */
    color: var(--text-color);
    padding: 120px 0;
    text-align: center;
}
.hero h1 { font-size: 3.5em; margin-bottom: 20px; font-weight: 700; }
.hero h1 span { color: var(--primary-color); } /* Tambahkan <span> di H1 jika mau */
.hero p { font-size: 1.2em; margin-bottom: 30px; max-width: 700px; margin-left: auto; margin-right: auto; }
.cta-button { background-color: var(--primary-color); color: white; padding: 15px 35px; border-radius: 50px; text-decoration: none; font-weight: 700; font-size: 1.1em; transition: all 0.3s ease; box-shadow: var(--shadow); }
.cta-button:hover { background-color: var(--secondary-color); transform: translateY(-3px); }

section { padding: 80px 0; }
.section-title { text-align: center; font-size: 2.5em; margin-bottom: 10px; font-weight: 700; }
.section-title span { color: var(--primary-color); }
.section-subtitle { text-align: center; font-size: 1.1em; color: #666; margin-bottom: 50px; max-width: 600px; margin-left: auto; margin-right: auto; }

/* Tabs for Catalog */
.tabs-container { display: flex; flex-wrap: wrap; justify-content: center; gap: 10px; margin-bottom: 40px; }
.tab-button { padding: 10px 20px; font-size: 1em; font-weight: 600; border: 2px solid #ddd; color: #555; background-color: transparent; border-radius: 50px; cursor: pointer; transition: all 0.3s ease; }
.tab-button.active {
    background: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
}
.tab-content { display: none; }
.tab-content.active { display: block; }

.services-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; }
.service-card { background-color: var(--card-bg); border-radius: var(--border-radius); box-shadow: var(--shadow); padding: 30px; text-align: left; transition: transform 0.3s ease, box-shadow 0.3s ease; display: flex; flex-direction: column; border: 1px solid #eee; }
.service-card:hover { transform: translateY(-10px); box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08); border-color: var(--primary-color); }
.service-card h3 { font-size: 1.4em; color: var(--text-color); margin-bottom: 10px; }
.service-card .price { font-size: 1.8em; font-weight: 700; color: var(--primary-color); margin-bottom: 15px; }
.service-card .description { color: #555; margin-bottom: 20px; flex-grow: 1; }
.service-card .order-button { background-color: var(--primary-color); color: white; padding: 12px 30px; border-radius: 50px; text-decoration: none; font-weight: 600; transition: background-color 0.3s ease; text-align: center; margin-top: auto; border: none; }
.service-card .order-button:hover { background-color: var(--secondary-color); }

.package-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 30px; }
.package-card { background: var(--primary-gradient); color: white; border-radius: var(--border-radius); box-shadow: var(--shadow); padding: 30px; text-align: center; transition: all 0.3s ease; position: relative; overflow: hidden; display: flex; flex-direction: column; }
.package-card:hover { transform: translateY(-10px); box-shadow: 0 8px 25px rgba(40, 167, 69, 0.2); }
.package-card .hemat-badge { position: absolute; top: 15px; right: -40px; background-color: var(--tertiary-color); color: var(--text-color); padding: 5px 40px; font-weight: 700; transform: rotate(45deg); }
.package-card h3 { font-size: 1.6em; color: white; margin-bottom: 15px; text-transform: uppercase; }
.package-card .price { font-size: 2.5em; font-weight: 700; color: white; margin-bottom: 20px; }
.package-card ul { list-style: none; margin-bottom: 25px; color: #f0f0f0; text-align: left; flex-grow: 1; }
.package-card ul li { margin-bottom: 10px; padding-left: 25px; position: relative; }
.package-card ul li::before { content: '✓'; color: var(--tertiary-color); position: absolute; left: 0; font-weight: bold; }
.package-card .order-button { background-color: white; color: var(--primary-color); padding: 12px 30px; border-radius: 50px; text-decoration: none; font-weight: 600; transition: background-color 0.3s ease; text-align: center; margin-top: auto; border: none; }
.package-card .order-button:hover { background-color: #f0f0f0; }

.promo-list { display: flex; flex-wrap: wrap; gap: 20px; justify-content: center; }
.promo-item, .area-card { background: #f8f9fa; border-left: 5px solid var(--primary-color); padding: 20px; border-radius: var(--border-radius); box-shadow: var(--shadow); flex-basis: 300px; }
.area-card h3 { color: var(--primary-color); margin-bottom: 10px; }

.gallery-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
.gallery-item { overflow: hidden; border-radius: var(--border-radius); box-shadow: var(--shadow); position: relative; }
.gallery-item img { width: 100%; height: 100%; object-fit: cover; display: block; transition: transform 0.4s ease; background-color: #eee; } /* Tambah BG color untuk placeholder */
.gallery-item:hover img { transform: scale(1.1); }
.gallery-item .overlay { position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(to top, rgba(0,0,0,0.8), transparent); color: white; padding: 40px 20px 20px; font-weight: 600; font-size: 1.1em; opacity: 0; transform: translateY(20px); transition: all 0.4s ease; }
.gallery-item:hover .overlay { opacity: 1; transform: translateY(0); }

footer { background-color: var(--footer-bg); color: #ecf0f1; padding: 60px 0 30px; }
.footer-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 40px; }
.footer-col h4 { font-size: 1.2em; margin-bottom: 20px; color: white; position: relative; }
.footer-col h4::after { content: ''; position: absolute; left: 0; bottom: -5px; width: 50px; height: 2px; background-color: var(--primary-color); }
.footer-col p, .footer-col a { color: #bdc3c7; text-decoration: none; transition: color 0.3s ease; }
.footer-col a:hover { color: white; }
.footer-col ul { list-style: none; }
.footer-col ul li { margin-bottom: 10px; }
.copyright { text-align: center; margin-top: 50px; padding-top: 20px; border-top: 1px solid #34495e; font-size: 0.9em; color: #95a5a6; }
.error-message { text-align: center; padding: 40px; background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba; border-radius: var(--border-radius); }

@media (max-width: 992px) { .gallery-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 768px) {
    .nav-container { flex-direction: column; align-items: flex-start; }
    nav ul { display: none; flex-direction: column; width: 100%; gap: 0; margin-top: 20px; }
    nav ul.active { display: flex; }
    nav ul li { width: 100%; text-align: center; }
    nav ul li a { padding: 15px; display: block; border-bottom: 1px solid #eee; }
    .menu-toggle { display: block; position: absolute; top: 20px; right: 20px; }
    .hero h1 { font-size: 2.5em; }
    .gallery-grid, .package-grid { grid-template-columns: 1fr; }
    .footer-container { grid-template-columns: 1fr; text-align: center; }
    .footer-col h4::after { left: 50%; transform: translateX(-50%); }
}
    </style>
</head>
<body>

    <header>
        <div class="container nav-container">
            <a href="#" class="logo-container">
                <picture>
                    <source srcset="/logo.webp" type="image/webp">
                    <source srcset="/logo.png" type="image/png">
                    <img src="/logo.png" alt="Logo Sejuk Service" class="logo-img" width="45" height="45">
                </picture>
                <div class="logo-text">Sejuk<span>Service</span></div>
            </a>
            <div class="menu-toggle" id="menu-toggle">&#9776;</div>
            <nav>
                <ul id="nav-links">
                    <li><a href="#katalog">Layanan</a></li>
                    <li><a href="#galeri">Galeri</a></li>
                    <li><a href="#area-layanan">Area</a></li>
                    <li><a href="#kontak">Kontak</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <section class="hero">
        <div class="container" data-aos="fade-in" data-aos-duration="1000">
            <h1>Rumah Sejuk, Hati Tenang</h1>
            <p>Solusi profesional untuk semua kebutuhan AC Anda. Dari pencucian rutin, perbaikan cepat, hingga instalasi baru, kami siap melayani.</p>
            <a href="<?php echo htmlspecialchars($whatsapp_link); ?>" class="cta-button" target="_blank">Hubungi Kami di WhatsApp</a>
        </div>
    </section>

    <section id="katalog">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up"><span>Katalog Layanan Kami</span></h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">Pilih kategori layanan yang Anda butuhkan.</p>
            
            <?php if (!empty($services)): ?>
                <div class="tabs-container" data-aos="fade-up" data-aos-delay="200">
                    <?php
                    $is_first_tab = true;
                    foreach ($services as $key => $value) {
                        if (isset($category_names[$key]) && !empty($value)) {
                            $active_class = $is_first_tab ? 'active' : '';
                            echo "<button class='tab-button {$active_class}' data-tab='{$key}'>" . htmlspecialchars($category_names[$key]) . "</button>";
                            $is_first_tab = false;
                        }
                    }
                    ?>
                </div>

                <?php
                $is_first_content = true;
                foreach ($services as $key => $value):
                    if (isset($category_names[$key]) && !empty($value)):
                        $active_class = $is_first_content ? 'active' : '';
                ?>
                <div id="<?php echo $key; ?>" class="tab-content <?php echo $active_class; ?>">
                    <?php if ($key === 'paket'): ?>
                        <div class="package-grid">
                            <?php foreach ($value as $i => $paket): ?>
                                <div class="package-card" data-aos="fade-up" data-aos-delay="<?php echo $i * 100; ?>">
                                    <?php if(!empty($paket['hemat'])): ?>
                                    <div class="hemat-badge">Hemat Rp <?php echo number_format($paket['hemat'], 0, ',', '.'); ?></div>
                                    <?php endif; ?>
                                    <h3><?php echo htmlspecialchars($paket['nama']); ?></h3>
                                    <div class="price">Rp <?php echo is_numeric($paket['harga']) ? number_format($paket['harga'], 0, ',', '.') : htmlspecialchars($paket['harga']); ?></div>
                                    <ul>
                                        <?php foreach ($paket['fitur'] as $fitur): ?>
                                            <li><?php echo htmlspecialchars($fitur); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <a href="<?php echo htmlspecialchars($whatsapp_link); ?>" class="order-button" target="_blank">Ambil Paket</a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php elseif ($key === 'promo_spesial'): ?>
                        <div class="promo-list">
                            <?php foreach ($value as $i => $promo): ?>
                                <div class="promo-item" data-aos="fade-up" data-aos-delay="<?php echo $i * 100; ?>">
                                    <p>✓ <?php echo htmlspecialchars($promo); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="services-grid">
                            <?php foreach ($value as $i => $service): ?>
                                <div class="service-card" data-aos="fade-up" data-aos-delay="<?php echo $i * 100; ?>">
                                    <h3><?php echo htmlspecialchars($service['nama']); ?></h3>
                                    <div class="price"><?php echo is_numeric($service['harga']) ? 'Rp ' . number_format($service['harga'], 0, ',', '.') : htmlspecialchars($service['harga']); ?></div>
                                    <p class="description"><?php echo htmlspecialchars($service['deskripsi']); ?></p>
                                    <a href="<?php echo htmlspecialchars($whatsapp_link); ?>" class="order-button" target="_blank"><?php echo ($key === 'perbaikan_ac') ? 'Konsultasi Kerusakan' : 'Pesan Sekarang'; ?></a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <?php
                        $is_first_content = false;
                    endif;
                endforeach;
                ?>

            <?php else: ?>
                <div class="error-message">
                    <p>Maaf, saat ini kami tidak dapat menampilkan katalog layanan. Silakan hubungi kami langsung untuk informasi lebih lanjut atau coba muat ulang halaman.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section id="galeri" style="background-color: #fff;">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up"><span>Galeri Pekerjaan Kami</span></h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">Lihat bagaimana tim profesional kami bekerja dengan teliti dan rapi untuk memberikan hasil terbaik.</p>
            <div class="gallery-grid">
                 <div class="gallery-item" data-aos="fade-up">
                    <picture>
                        <source srcset="/gambar/cuciac.webp" type="image/webp">
                        <source srcset="/gambar/cuciac.jpg" type="image/jpeg">
                        <img src="/gambar/cuciac.jpg" alt="Teknisi sedang mencuci AC" loading="lazy" width="372" height="186">
                    </picture>
                    <div class="overlay">Cuci & Perawatan Rutin</div>
                </div>
                <div class="gallery-item" data-aos="fade-up" data-aos-delay="100">
                    <picture>
                        <source srcset="/gambar/perbaikanac.webp" type="image/webp">
                        <source srcset="/gambar/perbaikanac.jpg" type="image/jpeg">
                        <img src="/gambar/perbaikanac.jpg" alt="Teknisi sedang memperbaiki AC" loading="lazy" width="372" height="248">
                    </picture>
                    <div class="overlay">Perbaikan & Solusi Masalah</div>
                </div>
                <div class="gallery-item" data-aos="fade-up" data-aos-delay="200">
                     <picture>
                        <source srcset="/gambar/pasangac.webp" type="image/webp">
                        <source srcset="/gambar/pasangac.jpg" type="image/jpeg">
                        <img src="/gambar/pasangac.jpg" alt="Teknisi sedang memasang AC baru" loading="lazy" width="372" height="213">
                    </picture>
                    <div class="overlay">Bongkar & Pasang Unit Baru</div>
                </div>
            </div>
        </div>
    </section>

    <?php if (!empty($services['area_layanan'])): ?>
    <section id="area-layanan">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up"><span>Area Layanan Kami</span></h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">Kami menjangkau berbagai lokasi untuk memastikan Anda mendapatkan layanan terbaik.</p>
            <div class="promo-list">
                <?php foreach ($services['area_layanan'] as $i => $area): ?>
                    <div class="area-card" data-aos="fade-up" data-aos-delay="<?php echo $i * 100; ?>">
                        <h3><?php echo htmlspecialchars($area['kota_utama']); ?></h3>
                        <p><?php echo implode(', ', array_map('htmlspecialchars', $area['cakupan'])); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <footer id="kontak">
        <div class="container footer-container">
            <div class="footer-col" data-aos="fade-up">
                <h4>Sejuk Service Indonesia</h4>
                <p>Penyedia jasa layanan HVAC profesional yang berdedikasi untuk memberikan kesejukan dan kenyamanan di setiap rumah.</p>
            </div>
            <div class="footer-col" data-aos="fade-up" data-aos-delay="100">
                <h4>Navigasi</h4>
                <ul>
                    <li><a href="#">Beranda</a></li>
                    <li><a href="#katalog">Layanan</a></li>
                    <li><a href="#galeri">Galeri</a></li>
                    <li><a href="#area-layanan">Area</a></li>
                    <li><a href="#kontak">Kontak</a></li>
                </ul>
            </div>
            <div class="footer-col" data-aos="fade-up" data-aos-delay="200">
                <h4>Hubungi Kami</h4>
                <ul>
                    <li><p>Telepon: <?php echo htmlspecialchars($phone_number); ?></p></li>
                    <li><p>Email: <?php echo htmlspecialchars($email_address); ?></p></li>
                    <li><a href="<?php echo htmlspecialchars($whatsapp_link); ?>" target="_blank">WhatsApp: Klik di sini</a></li>
                    <li><p>Alamat: Jakarta, Indonesia</p></li>
                </ul>
            </div>
        </div>
        <div class="container">
            <div class="copyright">
                &copy; <?php echo date("Y"); ?> Sejuk Service Indonesia. All Rights Reserved.
            </div>
        </div>
    </footer>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Inisialisasi AOS
        AOS.init({
            duration: 800,
            once: true,
            offset: 50,
        });

        // Menu Toggle
        const menuToggle = document.getElementById('menu-toggle');
        const navLinks = document.getElementById('nav-links');
        menuToggle.addEventListener('click', () => { navLinks.classList.toggle('active'); });
        document.querySelectorAll('#nav-links a').forEach(link => {
            link.addEventListener('click', () => {
                if (navLinks.classList.contains('active')) { navLinks.classList.remove('active'); }
            });
        });

        // Tab Logic
        const tabButtons = document.querySelectorAll('.tab-button');
        const tabContents = document.querySelectorAll('.tab-content');

        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                tabButtons.forEach(btn => btn.classList.remove('active'));
                tabContents.forEach(content => content.classList.remove('active'));

                button.classList.add('active');
                document.getElementById(button.dataset.tab).classList.add('active');
            });
        });
    </script>

</body>
</html>
