<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('shortDescription', 500)->nullable(); // Tambahkan ini jika belum ada
            $table->string('region')->nullable();
            $table->date('departure_date')->nullable();
            $table->date('arrival_date')->nullable();    // Sesuai revisi
            $table->text('image_urls')->nullable();      // Diubah dari image_url, tipe TEXT untuk JSON array
            $table->string('whatsappLink')->nullable(); // Tambahkan ini jika belum ada
            $table->string('status')->default('active');
            $table->timestamps(); // Otomatis membuat kolom created_at dan updated_at
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batches');
    }
};
