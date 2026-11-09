@extends('layouts.landing')

@section('title', 'Maivile · Layanan dokumen akademik sekolah')

@section('content')
@php
    $features = [
        ['icon' => 'bi-journal-bookmark-fill', 'color' => 'var(--mv-green)', 'title' => 'Peminjaman Rapor',
         'text' => 'Ajukan peminjaman rapor fisik hingga 7 hari. Petugas mencatat penyerahan dan pengembaliannya.'],
        ['icon' => 'bi-patch-check-fill', 'color' => 'var(--mv-purple)', 'title' => 'Legalisir Dokumen',
         'text' => 'Minta legalisir ijazah, rapor, atau dokumen akademik lain, 1 sampai 20 salinan per pengajuan.'],
        ['icon' => 'bi-mortarboard-fill', 'color' => 'var(--mv-orange)', 'title' => 'Pengambilan Ijazah',
         'text' => 'Pilih jadwal pengambilan di hari kerja. Jika diwakilkan, unggah surat kuasa lebih dulu.'],
        ['icon' => 'bi-bell-fill', 'color' => 'var(--mv-blue)', 'title' => 'Status, Notifikasi, dan Bukti',
         'text' => 'Setiap perubahan status dan catatan petugas muncul di akun Anda. Bukti pengajuan bisa dicetak sebagai PDF.'],
    ];

    $steps = [
        ['title' => 'Daftar akun', 'text' => 'Isi nama, NIS, kelas, jurusan, email, nomor telepon, dan kata sandi.'],
        ['title' => 'Tunggu persetujuan', 'text' => 'Petugas Tata Usaha memeriksa data Anda. Akun aktif setelah disetujui.'],
        ['title' => 'Ajukan layanan', 'text' => 'Pilih layanan, isi formulir, dan unggah berkas persyaratan.'],
        ['title' => 'Pantau dan ambil dokumen', 'text' => 'Ikuti statusnya di akun Anda, lalu datang ke sekolah sesuai jadwal.'],
    ];

    $faqs = [
        'Akun dan pendaftaran' => [
            ['q' => 'Siapa yang bisa mendaftar?',
             'a' => 'Siswa sekolah ini. Siapkan NIS, kelas, jurusan, email aktif, dan nomor telepon. Data Anda akan dicocokkan petugas dengan data sekolah.'],
            ['q' => 'Kenapa saya belum bisa masuk setelah mendaftar?',
             'a' => 'Akun baru aktif setelah disetujui petugas Tata Usaha. Sebelum itu, mencoba masuk akan menampilkan status pendaftaran Anda. Jika ditolak, catatan petugas ikut ditampilkan.'],
            ['q' => 'Saya lupa kata sandi. Bagaimana?',
             'a' => 'Hubungi petugas Tata Usaha. Petugas membuatkan kata sandi sementara, dan Anda wajib menggantinya saat masuk pertama kali.'],
        ],
        'Pengajuan layanan' => [
            ['q' => 'Berapa lama rapor boleh dipinjam?',
             'a' => 'Paling lama 7 hari, dan hanya rapor milik Anda sendiri. Satu rapor hanya bisa punya satu pinjaman aktif.'],
            ['q' => 'Berapa salinan legalisir yang bisa diminta?',
             'a' => 'Satu sampai 20 salinan per pengajuan. Lampiran persyaratan wajib diunggah.'],
            ['q' => 'Kapan ijazah bisa diambil?',
             'a' => 'Pada hari kerja, sesuai jadwal yang Anda ajukan dan disetujui petugas. Bawa identitas asli. Jika diwakilkan, surat kuasa wajib diunggah saat mengajukan.'],
            ['q' => 'Pengajuan saya disetujui. Apakah sudah selesai?',
             'a' => 'Belum. Persetujuan hanya berarti pengajuan diterima. Layanan selesai setelah petugas mencatat penyerahan atau pengembalian dokumen.'],
        ],
        'Fitur dan teknis' => [
            ['q' => 'Berkas apa yang boleh diunggah?',
             'a' => 'PDF, JPG, atau PNG dengan ukuran paling besar 2 MB per berkas.'],
            ['q' => 'Bagaimana saya tahu status pengajuan?',
             'a' => 'Buka Riwayat Pengajuan atau lihat ikon lonceng. Setiap perubahan status dan catatan petugas tercatat di sana. Bukti pengajuan bisa dicetak sebagai PDF.'],
            ['q' => 'Apakah berkas saya aman?',
             'a' => 'Berkas disimpan privat dan hanya bisa dibuka oleh Anda dan petugas sekolah.'],
        ],
    ];
@endphp

<header class="mv-lp-nav sticky-top">
    <nav class="navbar navbar-expand-lg container" aria-label="Menu utama">
        <a class="navbar-brand mv-logo mv-logo-anim" href="{{ route('home') }}">Maivile</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#lpMenu"
                aria-controls="lpMenu" aria-expanded="false" aria-label="Buka menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="lpMenu">
            <ul class="navbar-nav mx-lg-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="#fitur">Fitur</a></li>
                <li class="nav-item"><a class="nav-link" href="#panduan">Panduan</a></li>
                <li class="nav-item"><a class="nav-link" href="#faq">FAQ</a></li>
            </ul>
            <div class="d-flex flex-column flex-lg-row gap-2 pb-3 pb-lg-0">
                <a href="{{ route('login') }}" class="btn btn-outline-light">Masuk</a>
                <a href="{{ route('register') }}" class="btn btn-primary">Daftar</a>
            </div>
        </div>
    </nav>
</header>

<main>
    {{-- Hero --}}
    <section class="mv-lp-hero">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <span class="mv-lp-pill"><i class="bi bi-stars" aria-hidden="true"></i> Layanan Tata Usaha online</span>
                    <h1 class="mv-lp-title">Urus rapor, legalisir, dan ijazah tanpa bolak-balik ke sekolah</h1>
                    <p class="mv-lp-lead">
                        Ajukan layanan dokumen akademik dari ponsel Anda, unggah persyaratan, lalu pantau
                        prosesnya sampai dokumen siap diambil.
                    </p>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('register') }}" class="btn btn-primary btn-lg">Daftar Sekarang</a>
                        <a href="#fitur" class="btn btn-outline-light btn-lg">Lihat Fitur</a>
                    </div>
                    <p class="text-secondary small mt-3 mb-0">Akun baru aktif setelah disetujui petugas Tata Usaha.</p>
                </div>

                <div class="col-lg-6">
                    <div class="mv-lp-stack" role="img" aria-label="Contoh tiga pengajuan: peminjaman rapor, legalisir, dan pengambilan ijazah beserta statusnya">
                        <div class="mv-lp-doc mv-lp-doc-1" style="--c: var(--mv-green)">
                            <i class="bi bi-journal-bookmark-fill" aria-hidden="true"></i>
                            <div>
                                <div class="fw-bold">Peminjaman Rapor</div>
                                <div class="small">Rapor Semester 5</div>
                            </div>
                            <span class="mv-lp-chip">Dipinjam</span>
                        </div>
                        <div class="mv-lp-doc mv-lp-doc-2" style="--c: var(--mv-purple)">
                            <i class="bi bi-patch-check-fill" aria-hidden="true"></i>
                            <div>
                                <div class="fw-bold">Legalisir Dokumen</div>
                                <div class="small">Ijazah, 3 salinan</div>
                            </div>
                            <span class="mv-lp-chip">Sedang diproses</span>
                        </div>
                        <div class="mv-lp-doc mv-lp-doc-3" style="--c: var(--mv-orange)">
                            <i class="bi bi-mortarboard-fill" aria-hidden="true"></i>
                            <div>
                                <div class="fw-bold">Pengambilan Ijazah</div>
                                <div class="small">Jadwal dikonfirmasi</div>
                            </div>
                            <span class="mv-lp-chip">Siap diambil</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Aturan singkat layanan (sesuai aturan aplikasi) --}}
    <section class="mv-lp-facts" aria-label="Aturan singkat layanan">
        <div class="container">
            <div class="row g-3 text-center">
                <div class="col-6 col-md-3"><div class="mv-lp-fact-num">7 hari</div><div class="text-secondary small">Maksimal peminjaman rapor</div></div>
                <div class="col-6 col-md-3"><div class="mv-lp-fact-num">1-20</div><div class="text-secondary small">Salinan legalisir per pengajuan</div></div>
                <div class="col-6 col-md-3"><div class="mv-lp-fact-num">2 MB</div><div class="text-secondary small">Ukuran maksimal tiap berkas</div></div>
                <div class="col-6 col-md-3"><div class="mv-lp-fact-num">PDF</div><div class="text-secondary small">Bukti pengajuan bisa dicetak</div></div>
            </div>
        </div>
    </section>

    {{-- Fitur --}}
    <section class="mv-lp-section" id="fitur" aria-labelledby="fitur-title">
        <div class="container">
            <h2 id="fitur-title" class="mv-lp-h2">Satu akun untuk semua layanan dokumen</h2>
            <p class="mv-lp-sub">Tiga layanan utama sekolah, dengan status yang jelas di setiap tahap.</p>
            <div class="row g-3">
                @foreach ($features as $f)
                    <div class="col-sm-6 col-lg-3">
                        <article class="mv-lp-feature h-100" style="--c: {{ $f['color'] }}">
                            <span class="mv-lp-icon"><i class="bi {{ $f['icon'] }}" aria-hidden="true"></i></span>
                            <h3>{{ $f['title'] }}</h3>
                            <p class="text-secondary mb-0">{{ $f['text'] }}</p>
                        </article>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Panduan --}}
    <section class="mv-lp-section mv-lp-alt" id="panduan" aria-labelledby="panduan-title">
        <div class="container">
            <h2 id="panduan-title" class="mv-lp-h2">Hanya 4 langkah</h2>
            <p class="mv-lp-sub">Dari mendaftar sampai dokumen di tangan Anda.</p>
            <ol class="mv-lp-steps list-unstyled row g-3 mb-0">
                @foreach ($steps as $i => $step)
                    <li class="col-sm-6 col-lg-3">
                        <div class="mv-lp-step h-100">
                            <span class="mv-lp-step-no" aria-hidden="true">{{ $i + 1 }}</span>
                            <h3>{{ $step['title'] }}</h3>
                            <p class="text-secondary mb-0">{{ $step['text'] }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>
            <div class="text-center mt-4">
                <a href="{{ route('register') }}" class="btn btn-primary btn-lg">Mulai dengan mendaftar</a>
            </div>
        </div>
    </section>

    {{-- FAQ --}}
    <section class="mv-lp-section" id="faq" aria-labelledby="faq-title">
        <div class="container mv-lp-narrow">
            <h2 id="faq-title" class="mv-lp-h2">Pertanyaan yang sering diajukan</h2>
            <p class="mv-lp-sub">Tidak menemukan jawabannya? Hubungi petugas Tata Usaha.</p>

            @php $n = 0; @endphp
            @foreach ($faqs as $group => $items)
                <h3 class="mv-lp-faq-group">{{ $group }}</h3>
                <div class="accordion accordion-flush mv-lp-accordion mb-4" id="faq-{{ $loop->iteration }}">
                    @foreach ($items as $item)
                        @php $n++; @endphp
                        <div class="accordion-item">
                            <h4 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#faq-item-{{ $n }}" aria-expanded="false" aria-controls="faq-item-{{ $n }}">
                                    {{ $item['q'] }}
                                </button>
                            </h4>
                            <div id="faq-item-{{ $n }}" class="accordion-collapse collapse" data-bs-parent="#faq-{{ $loop->parent->iteration }}">
                                <div class="accordion-body text-secondary">{{ $item['a'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </section>

    {{-- Ajakan akhir --}}
    <section class="mv-lp-cta" aria-labelledby="cta-title">
        <div class="container text-center">
            <h2 id="cta-title" class="mv-lp-h2">Siap mengurus dokumen akademik Anda?</h2>
            <p class="mv-lp-sub mb-4">Daftar sekarang. Petugas akan menyetujui akun Anda, lalu semua layanan bisa diajukan dari satu tempat.</p>
            <div class="d-flex flex-wrap justify-content-center gap-2">
                <a href="{{ route('register') }}" class="btn btn-primary btn-lg">Daftar Sekarang</a>
                <a href="{{ route('login') }}" class="btn btn-outline-light btn-lg">Sudah punya akun? Masuk</a>
            </div>
        </div>
    </section>
</main>

<footer class="mv-lp-footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-5">
                <div class="mv-logo mb-2">Maivile</div>
                <p class="text-secondary mb-0">{{ config('maivile.school_name') }}</p>
            </div>
            <div class="col-md-4">
                <h2 class="h6">Layanan Tata Usaha</h2>
                <p class="text-secondary mb-1"><i class="bi bi-geo-alt me-2" aria-hidden="true"></i>{{ config('maivile.school_address') }}</p>
                <p class="text-secondary mb-1"><i class="bi bi-clock me-2" aria-hidden="true"></i>{{ config('maivile.service_hours') }}</p>
                <p class="text-secondary mb-0"><i class="bi bi-telephone me-2" aria-hidden="true"></i>{{ config('maivile.contact') }}</p>
            </div>
            <div class="col-md-3">
                <h2 class="h6">Akun</h2>
                <ul class="list-unstyled mb-0">
                    <li><a href="{{ route('login') }}">Masuk</a></li>
                    <li><a href="{{ route('register') }}">Daftar</a></li>
                </ul>
            </div>
        </div>
        <p class="text-secondary small mt-4 mb-0">&copy; {{ now()->year }} Maivile</p>
    </div>
</footer>
@endsection
