<?php

/*
| Pesan validasi Bahasa Indonesia. Kunci yang tidak ada jatuh ke bahasa Inggris (fallback).
*/

return [
    'accepted' => ':attribute harus diterima.',
    'array' => ':attribute harus berupa daftar.',
    'boolean' => ':attribute harus benar atau salah.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'date' => ':attribute bukan tanggal yang valid.',
    'email' => ':attribute harus berupa alamat email yang valid.',
    'exists' => ':attribute yang dipilih tidak valid.',
    'image' => ':attribute harus berupa gambar.',
    'in' => ':attribute yang dipilih tidak valid.',
    'integer' => ':attribute harus berupa bilangan bulat.',
    'mimes' => ':attribute harus berupa berkas bertipe: :values.',
    'numeric' => ':attribute harus berupa angka.',
    'regex' => 'Format :attribute tidak valid.',
    'required' => ':attribute wajib diisi.',
    'string' => ':attribute harus berupa teks.',
    'unique' => ':attribute sudah dipakai.',
    'uploaded' => ':attribute gagal diunggah.',
    'max' => [
        'numeric' => ':attribute tidak boleh lebih dari :max.',
        'file' => ':attribute tidak boleh lebih dari :max kilobyte.',
        'string' => ':attribute tidak boleh lebih dari :max karakter.',
        'array' => ':attribute tidak boleh memiliki lebih dari :max item.',
    ],
    'min' => [
        'numeric' => ':attribute minimal :min.',
        'file' => ':attribute minimal :min kilobyte.',
        'string' => ':attribute minimal :min karakter.',
        'array' => ':attribute minimal memiliki :min item.',
    ],
    'attributes' => [
        'email' => 'Email',
        'password' => 'Password',
        'name' => 'Nama',
        'phone' => 'Nomor telepon',
        'role' => 'Peran',
        'tenant' => 'Perusahaan',
    ],
];
