<?php

/*
 | Data sekolah untuk halaman pembuka (landing) dan footer.
 | Isi lewat file .env. Selama belum diisi, tampil placeholder dalam kurung siku.
 */
return [
    'school_name' => env('MAIVILE_SCHOOL_NAME', '[NAMA SEKOLAH]'),
    'school_address' => env('MAIVILE_SCHOOL_ADDRESS', '[ALAMAT SEKOLAH]'),
    'service_hours' => env('MAIVILE_SERVICE_HOURS', '[JAM LAYANAN]'),
    'contact' => env('MAIVILE_CONTACT', '[KONTAK]'),
];
