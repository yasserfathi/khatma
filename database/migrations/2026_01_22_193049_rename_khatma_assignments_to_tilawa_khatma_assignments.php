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
        Schema::rename('khatma_assignments', 'tilawa_khatma_assignments');
        Schema::table('tilawa_khatma_assignments', function (Blueprint $table) {
            $table->dropColumn('zikr_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tilawa_khatma_assignments', function (Blueprint $table) {
            $table->integer('zikr_count')->nullable();
        });
        Schema::rename('tilawa_khatma_assignments', 'khatma_assignments');
    }
};
