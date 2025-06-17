<?php 

return [
    // Core Messages
    'noRuleSets' => 'Pengaturan validasi tidak ditemukan.',

    // default error message
    'default' => 'Form {label} tidak valid.',

    // comparative rule error message
    'required'                 => 'Form {label} harus diisi.',
    'empty'                    => 'Form {label} harus kosong.',
    'exact_length'             => 'Form {label} harus berisi {param} karakter.',
    'not_exact_length'         => 'Form {label} tidak boleh berisi {param} karakter.',
    'exact_length_in'          => 'Jumlah karakter form {label} harus salah satu dari: {param}',
    'not_exact_length_in'      => 'Jumlah karakter form {label} tidak boleh salah satu dari: {param}',
    'max_length'               => 'Form {label} tidak boleh melebihi {param} karakter.',
    'min_length'               => 'Form {label} minimal memiliki {param} karakter.',
    'equal'                    => 'Form {label} harus diisi dengan: {param}.',
    'not_equal'                => 'Form {label} tidak boleh diisi dengan: {param}.',
    'in_list'                  => 'Form {label} harus salah satu dari: {param}.',
    'not_in_list'              => 'Form {label} tidak boleh salah satu dari: {param}.',
    'greater_than'             => 'Form {label} harus lebih dari {param}.',
    'greater_than_or_equal_to' => 'Form {label} harus lebih dari atau sama dengan {param}.',
    'less_than'                => 'Form {label} harus kurang dari {param}.',
    'less_than_or_equal_to'    => 'Form {label} harus kurang dari atau sama dengan {param}.',

    // comparative field error message
    'differ'                         => 'Form {label} harus berbeda dengan form {param}.',
    'match'                          => 'Form {label} harus sama persis dengan form {param}.',
    'exact_length_with'              => 'Form {label} harus memiliki jumlah karakter yang sama dengan form {param}.',
    'not_exact_length_with'          => 'Form {label} harus memiliki jumlah karakter yang berbeda dengan form {param}.',
    'greater_than_field'             => 'Form {label} harus lebih besar dari form {param}.',
    'greater_than_or_equal_to_field' => 'Form {label} harus lebih besar dari atau sama dengan form {param}.',
    'less_than_field'                => 'Form {label} harus kurang dari form {param}.',
    'less_than_or_equal_to_field'    => 'Form {label} harus kurang dari atau sama dengan form {param}.',

    // credit card error message
    'valid_cc_number' => '{label} bukan merupakan nomor kartu kredit yang valid.',

    // database error message
    'is_unique'     => '{label} sudah ada sebelumnya.',
    'is_not_unique' => '{label} tidak ditemukan.',

    // format error message
    'alpha'                      => 'Form {label} hanya bisa diisi dengan karakter alfabet.',
    'alpha_space'                => 'Form {label} hanya bisa diisi dengan karakter alfabet dan spasi.',
    'alpha_numeric'              => 'Form {label} hanya bisa diisi dengan karakter alfabet dan angka.',
    'alpha_numeric_dash'         => 'Form {label} hanya bisa diisi dengan karakter alfabet, angka, underscore, dan garis strip.',
    'alpha_numeric_punct'        => 'Form {label} hanya bisa diisi dengan karakter alfabet, angka, underscore, garis strip, dan ~ ! # $ % & * - _ + = | : .',
    'alpha_numeric_space'        => 'Form {label} hanya bisa diisi dengan karakter alfabet, angka, dan spasi.',
    'string'                     => 'Form {label} harus berupa string.',
    'decimal'                    => 'Form {label} harus berupa angka desimal.',
    'hex'                        => 'Form {label} harus berupa hex.',
    'integer'                    => 'Form {label} harus berupa integer.',
    'is_natural_number'          => 'Form {label} harus merupakan angka natural (tanpa negatif).',
    'is_natural_number_not_zero' => 'Form {label} harus merupakan angka natural (tanpa negatif) dan tanpa nol.',
    'numeric'                    => 'Form {label} harus berupa nomor.',
    'regex_match'                => '{label} tidak valid.',
    'valid_timezone'             => '{label} bukan zona waktu yang valid.',
    'valid_base64'               => '{label} bukan base64 yang valid.',
    'valid_json'                 => '{label} bukan JSON yang valid.',
    'valid_email'                => '{label} bukan email yang valid.',
    'valid_emails'               => 'Salah satu dari {label} bukan email yang valid.',
    'valid_ip'                   => '{label} bukan Alamat IP yang valid.',
    'valid_url'                  => '{label} bukan alamat URL yang valid.',
    'valid_url_strict'           => '{label} bukan alamat URL yang valid.',
    'valid_date'                 => '{label} bukan tanggal yang valid.',

    // file error message
    'uploaded' => '{label} gagal diunggah',
    'max_size' => 'Ukuran {label} tidak boleh melebihi {param} KB',
    'is_image' => '{label} harus diisi oleh gambar',
    'mime_in'  => 'Jenis file {label} harus salah satu dari: {param}',
    'ext_in'   => 'Ekstensi file {label} harus salah satu dari: {param}',
    'max_dims' => 'Dimensi ukuran {label} harus lebih kecil dari {param}',
    'min_dims' => 'Dimensi ukuran {label} harus lebih besar dari {param}',
];