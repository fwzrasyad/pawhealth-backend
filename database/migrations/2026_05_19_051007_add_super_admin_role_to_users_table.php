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
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('owner', 'vet', 'manager', 'super_admin') DEFAULT 'owner'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Fallback super_admins to owners before reverting enum
        \Illuminate\Support\Facades\DB::statement("UPDATE users SET role = 'owner' WHERE role = 'super_admin'");
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('owner', 'vet', 'manager') DEFAULT 'owner'");
    }
};
