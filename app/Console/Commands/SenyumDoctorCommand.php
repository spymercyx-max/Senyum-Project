<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class SenyumDoctorCommand extends Command
{
    protected $signature = 'senyum:doctor {--fix : Buat akun yang hilang & reset password dev ke default}';

    protected $description = 'Cek akun developer/distributor & database (jalanin dengan --fix untuk perbaiki)';

    public function handle(): int
    {
        $this->info('SENYUM doctor — cek akses login (development)');
        $this->newLine();

        try {
            DB::connection()->getPdo();
            $this->info('[OK] Koneksi database: '.config('database.default'));
        } catch (\Throwable $e) {
            $this->error('[FAIL] Koneksi database gagal: '.$e->getMessage());
            return 1;
        }

        foreach (['users', 'distributor_profiles', 'territories', 'sessions'] as $table) {
            if (! Schema::hasTable($table)) {
                $this->error("[FAIL] Tabel '{$table}' tidak ada. Jalankan: php artisan migrate --seed");
                return 1;
            }
        }
        $this->info('[OK] Tabel utama tersedia.');
        $this->newLine();

        $devPassword = (string) (env('DEV_PASSWORD', 'fullsenyum') ?: 'fullsenyum');
        $problems = 0;

        foreach (['X-Mercy', 'Madcapone', 'Ahmadalkaff'] as $username) {
            $u = User::where('username', $username)->first();
            if (! $u) {
                $this->error("[FAIL] Akun developer '{$username}' TIDAK ADA.");
                $problems++;
            } elseif ($u->role !== 'developer' || $u->status !== 'active') {
                $this->error("[FAIL] '{$username}': role={$u->role} status={$u->status} (harusnya developer/active).");
                $problems++;
            } elseif (! Hash::check($devPassword, $u->password)) {
                $this->error("[FAIL] Password '{$username}' BUKAN default.");
                $problems++;
            } else {
                $this->info("[OK] {$username} siap dipakai.");
            }
        }

        // Identitas distributor: X-Mercy-Dist (username unik, deterministik —
        // "X-Mercy" dipakai developer; lihat DistributorSeeder).
        $d = User::where('username', 'X-Mercy-Dist')->first();
        if (! $d) {
            $this->error("[FAIL] Akun distributor 'X-Mercy-Dist' TIDAK ADA.");
            $problems++;
        } else {
            $status = $d->distributorStatus() ?? 'null';
            $pwOk = Hash::check($devPassword, $d->password);
            if ($d->role !== 'distributor' || $d->status !== 'active' || $status !== 'approved' || ! $pwOk) {
                $this->error("[FAIL] X-Mercy-Dist: role={$d->role} status={$d->status} dist={$status} pwDefault=".($pwOk ? 'ya' : 'bukan'));
                $problems++;
            } else {
                $this->info('[OK] X-Mercy-Dist (nama tampilan: X-Mercy, approved) siap dipakai.');
            }
        }

        $this->newLine();

        if ($problems === 0) {
            $this->info('Semua akun OK. Kalau tetap tidak bisa login, baca pesan error di halaman login:');
            $this->line(' - "Kredensial tidak cocok" = username/password salah (password case-sensitive).');
            $this->line(' - "Akun Anda tidak aktif" = status user bukan active.');
            $this->line(' - "Too Many Requests" (429) = kebanyakan percobaan, tunggu 1 menit.');
            $this->line(' - "Page Expired" (419) = cookie/session browser bermasalah, coba mode incognito.');
            return 0;
        }

        if (! $this->option('fix')) {
            $this->warn("Ditemukan {$problems} masalah. Jalankan: php artisan senyum:doctor --fix");
            return 1;
        }

        if (app()->isProduction()) {
            $this->error('Ditolak di production. Jangan reset password default di production.');
            return 1;
        }

        $this->call('db:seed', ['--class' => 'Database\\Seeders\\DeveloperSeeder', '--force' => true]);
        $this->call('db:seed', ['--class' => 'Database\\Seeders\\DistributorSeeder', '--force' => true]);
        $this->info('Selesai. Coba login lagi dengan kredensial default.');
        return 0;
    }
}
