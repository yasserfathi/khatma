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
        Schema::table('khatmas', function (Blueprint $table) {
            $table->integer('juz_count')->default(30)->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('khatmas', function (Blueprint $table) {
            $table->dropColumn('juz_count');
        });
    }
};
