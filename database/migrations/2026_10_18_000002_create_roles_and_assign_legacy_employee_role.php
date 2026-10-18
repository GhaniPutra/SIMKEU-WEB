<?php

use App\Support\RolePermissions;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->string('name', 80);
            $table->json('permissions');
            $table->boolean('is_system')->default(false);
            $table->timestamps();
            $table->unique(['store_id', 'name']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('role')->constrained('roles')->restrictOnDelete();
            $table->index(['store_id', 'role_id']);
        });

        $now = now();
        foreach (DB::table('stores')->orderBy('id')->get(['id']) as $store) {
            $roleId = DB::table('roles')->insertGetId([
                'store_id' => $store->id,
                'name' => 'Pegawai',
                'permissions' => json_encode(RolePermissions::legacyEmployee()),
                'is_system' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('users')
                ->where('store_id', $store->id)
                ->where('role', 'pegawai')
                ->update(['role_id' => $roleId]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['store_id', 'role_id']);
            $table->dropConstrainedForeignId('role_id');
        });

        Schema::dropIfExists('roles');
    }
};
