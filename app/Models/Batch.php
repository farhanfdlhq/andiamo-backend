<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'shortDescription', // Pastikan ini ada jika Anda menggunakannya
        'region',
        'departure_date',
        'arrival_date',     // Field baru
        'whatsappLink',
        'status',
        'image_urls',       // Field untuk multiple images (akan disimpan sebagai JSON)
        // Hapus 'price', 'quota', 'duration_days' jika sudah tidak ada di tabel
        // Hapus 'image_url' (single image) jika sudah diganti 'image_urls'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'image_urls' => 'array', // Otomatis konversi ke/dari JSON string
        'departure_date' => 'date:Y-m-d', // Atau 'datetime:Y-m-d H:i:s' jika ada waktu
        'arrival_date' => 'date:Y-m-d',   // Atau 'datetime:Y-m-d H:i:s'
        // 'price' => 'float', // Hapus jika tidak ada lagi
        // 'quota' => 'integer', // Hapus jika tidak ada lagi
        // 'duration_days' => 'integer', // Hapus jika tidak ada lagi
    ];
}
