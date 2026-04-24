<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qr_parkirs', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->enum('status', ['tersedia', 'terpakai'])->default('tersedia');
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qr_parkirs');
    }
};
