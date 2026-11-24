<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homepage_images', function (Blueprint $table) {
            $table->id();
            $table->string('path');
            $table->boolean('is_active')->default(false)->index();
            $table->timestamps();
        });

        DB::table('homepage_images')->insert([
            'path' => 'images/img-fondo-inicio.jpeg',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('homepage_images');
    }
};
