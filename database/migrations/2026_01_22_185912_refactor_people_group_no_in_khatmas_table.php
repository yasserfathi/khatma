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
            $table->dropColumn('people_group_no');
            $table->foreignId('group_reading_id')->nullable()->after('khatma_no')->constrained('group_readings')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('khatmas', function (Blueprint $table) {
            $table->dropForeign(['group_reading_id']);
            $table->dropColumn('group_reading_id');
            $table->string('people_group_no')->nullable()->after('khatma_no');
        });
    }
};
