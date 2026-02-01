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
        $tables = ['group_readings', 'zikr_group_participants', 'zikr_khatma_assignments', 'tilawa_khatma_assignments'];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (!Schema::hasColumn($tableName, 'created_by')) {
                        $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('cascade');
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['group_readings', 'zikr_group_participants', 'zikr_khatma_assignments', 'tilawa_khatma_assignments'];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (Schema::hasColumn($tableName, 'created_by')) {
                        try {
                            $table->dropForeign(['created_by']);
                        } catch (\Exception $e) {
                            // Foreign key might not exist or have a different name
                        }
                        $table->dropColumn('created_by');
                    }
                });
            }
        }
    }
};
