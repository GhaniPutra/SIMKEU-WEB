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
        // Two-factor authentication has been disabled in this project.
        // The migration is intentionally left empty to keep history intact.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No columns to remove because two-factor fields are no longer created.
    }
};
