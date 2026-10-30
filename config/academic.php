<?php

/*
 * Nilai BAWAAN aturan akademik. Sumber kebenaran runtime = tabel `settings` (dibaca lewat AcademicRules).
 * SEMUA angka di sini adalah CONTOH/USULAN dan harus DIVERIFIKASI dengan kebijakan kampus dan
 * Permendikbudristek terkini sebelum dipakai (rules/06, rules/09, rules/19 Q3-Q4).
 */
return [
    // Skala nilai: huruf, batas bawah skor (0-100, 2 desimal), bobot mutu. Urut menurun. (verifikasi, Q3)
    'grade_scale' => [
        ['letter' => 'A', 'min' => 85.00, 'points' => 4.00],
        ['letter' => 'A-', 'min' => 80.00, 'points' => 3.70],
        ['letter' => 'B+', 'min' => 75.00, 'points' => 3.30],
        ['letter' => 'B', 'min' => 70.00, 'points' => 3.00],
        ['letter' => 'B-', 'min' => 65.00, 'points' => 2.70],
        ['letter' => 'C+', 'min' => 60.00, 'points' => 2.30],
        ['letter' => 'C', 'min' => 55.00, 'points' => 2.00],
        ['letter' => 'D', 'min' => 40.00, 'points' => 1.00],
        ['letter' => 'E', 'min' => 0.00, 'points' => 0.00],
    ],

    // Format identitas (verifikasi dengan kebijakan kampus/PDDikti).
    'nim_pattern' => '/^\d{6,15}$/',
    'nidn_pattern' => '/^\d{10}$/',
    'nip_pattern' => '/^\d{18}$/',
    'phone_pattern' => '/^(\+62|62|0)8\d{7,12}$/',

    // Impor CSV (rules/16).
    'import_max_kb' => 2048,
    'import_max_rows' => 5000,
    'import_chunk' => 200,
    'import_retention_hours' => 24,

    // Dipakai sesi berikutnya; dibaca lewat AcademicRules agar tidak ada angka tersebar.
    'risk_gpa_below' => 2.00,
    'qr_window_seconds' => 30,
    'qr_window_tolerance' => 1,
    'va_expiry_hours' => 72,
];
