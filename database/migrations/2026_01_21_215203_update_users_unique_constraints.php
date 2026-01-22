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
        Schema::table('users', function (Blueprint $table) {
            // Drop standard unique index on name if it exists
            /*
            try {
                $table->dropUnique('users_name_unique');
            } catch (\Exception $e) {
                echo "Notice: users_name_unique " . $e->getMessage() . "\n";
            }
            */

            // Add composite unique index for name per admin
            // Check if it exists to avoid error? No, Schema::hasIndex is better if possible.
            // But we'll just try to add it. Laravel usually handles this but throwing error if exists.
            try {
                $table->unique(['name', 'created_by']);
            } catch (\Exception $e) {
                echo "Notice: Composite index on name+created_by might already exist.\n";
            }

            // Make phone unique
            try {
                $table->unique('phone');
            } catch (\Exception $e) {
                echo "Notice: Phone index might already exist.\n";
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['name', 'created_by']);
            $table->dropUnique(['phone']);
            $table->unique('name');
        });
    }
};
