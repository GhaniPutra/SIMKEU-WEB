<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PruneNotifications extends Command
{
    protected $signature = 'notifications:prune';

    protected $description = 'Hapus notifikasi yang berusia lebih dari 30 hari';

    public function handle(): int
    {
        if (! Schema::hasTable('notifications')) {
            $this->warn('Tabel notifications belum tersedia. Jalankan migrasi database terlebih dahulu.');

            return self::SUCCESS;
        }

        $deleted = DB::table('notifications')
            ->where('created_at', '<', now()->subDays(30))
            ->delete();

        $this->info("{$deleted} notifikasi yang berusia lebih dari 30 hari dihapus.");

        return self::SUCCESS;
    }
}
