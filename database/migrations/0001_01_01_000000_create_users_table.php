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
        // =====================================================
        // 1. Tabel stores (profil toko/cabang)
        // =====================================================
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->notNullable();
            $table->text('address')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('logo', 255)->nullable();
            $table->timestamps();
        });

        // =====================================================
        // 2. Tabel users (pengguna sistem)
        // =====================================================
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->nullable()->constrained('stores')->onDelete('set null');
            $table->string('name', 100)->notNullable();
            $table->string('email', 100)->unique()->notNullable();
            $table->string('password');
            $table->enum('role', ['admin', 'pegawai'])->default('pegawai');
            $table->rememberToken();
            $table->timestamps();
            $table->index(['store_id'], 'idx_users_store');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('stores');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
