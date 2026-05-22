<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('owner', 'vet', 'manager') DEFAULT 'owner'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Fallback managers to owners before reverting enum
        \Illuminate\Support\Facades\DB::statement("UPDATE users SET role = 'owner' WHERE role = 'manager'");
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('owner', 'vet') DEFAULT 'owner'");
    }
};
