<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'], // Atau ['*'] jika ingin lebih permisif sementara
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        env('FRONTEND_URL', 'https://andiamo.elenmorcreative.com'), // Untuk frontend produksi
        'http://localhost:5173', // Untuk frontend lokal Anda (sesuaikan port jika perlu)
        // Anda bisa menambahkan origin lain di sini jika perlu
    ],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];