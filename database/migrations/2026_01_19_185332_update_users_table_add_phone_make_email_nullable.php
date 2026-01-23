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
            $table->string('email')->nullable()->change();
            $table->string('phone')->nullable()->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Fix records with null email before reverting to NOT NULL
        \Illuminate\Support\Facades\DB::table('users')
            ->whereNull('email')
            ->update(['email' => \Illuminate\Support\Facades\DB::raw('CONCAT("user_", id, "@placeholder.com")')]);

        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable(false)->change();
            $table->dropColumn('phone');
        });
    }
};
