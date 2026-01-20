<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('khatma_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('khatma_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // For Quran Khatma (Group Type 1)
            $table->json('parts')->nullable(); // Arrays of Juz numbers e.g., [1, 2]
            $table->boolean('read')->default(false); // Arrays of verse numbers e.g., [1, 2]

            // For Zikr Khatma (Group Type 2)
            $table->integer('zikr_count')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('khatma_assignments');
    }
};
