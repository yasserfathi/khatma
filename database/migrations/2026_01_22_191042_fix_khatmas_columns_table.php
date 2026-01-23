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
        if (!Schema::hasColumn('khatmas', 'group_reading_id')) {
            Schema::table('khatmas', function (Blueprint $table) {
                $table->foreignId('group_reading_id')->nullable()->after('khatma_no')->constrained('group_readings')->onDelete('set null');
            });
        }

        if (Schema::hasColumn('khatmas', 'people_group_no')) {
            Schema::table('khatmas', function (Blueprint $table) {
                $table->dropColumn('people_group_no');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('khatmas', function (Blueprint $table) {
            if (Schema::hasColumn('khatmas', 'group_reading_id')) {
                $table->dropForeign(['group_reading_id']);
                $table->dropColumn('group_reading_id');
            }
            if (!Schema::hasColumn('khatmas', 'people_group_no')) {
                $table->string('people_group_no')->nullable()->after('khatma_no');
            }
        });
    }
};
