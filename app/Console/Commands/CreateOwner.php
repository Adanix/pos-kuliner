<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateOwner extends Command
{
    protected $signature = 'owner:create {--name=} {--email=} {--password=} {--pin=1234}';
    protected $description = 'Buat akun pemilik (Owner) pertama jika belum ada';

    public function handle(): int
    {
        $name = $this->option('name') ?: env('OWNER_NAME', 'Pemilik Ciora');
        $email = $this->option('email') ?: env('OWNER_EMAIL', 'owner@kuliner.ramadadawah.my.id');
        $password = $this->option('password') ?: env('OWNER_PASSWORD', 'admin12345');
        $pin = $this->option('pin') ?: '1234';

        $user = User::where('email', $email)->first();

        if ($user) {
            $user->name = $name;
            $user->role = UserRole::OWNER;
            $user->password = Hash::make($password);
            $user->pin_hash = Hash::make($pin);
            $user->is_active = true;
            $user->save();
            $this->info("Akun Owner '{$email}' berhasil diperbarui.");
        } else {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'role' => UserRole::OWNER,
                'password' => Hash::make($password),
                'pin_hash' => Hash::make($pin),
                'is_active' => true,
            ]);
            $this->info("Akun Owner '{$email}' berhasil dibuat.");
        }

        // Ensure default outlet profile exists
        if (! Outlet::exists()) {
            Outlet::create([
                'name' => 'Kuliner Ciora',
                'address' => 'Jl. Kuliner No. 10',
                'phone' => '081234567890',
                'receipt_footer' => 'Terima kasih atas kunjungan Anda!',
                'tax_percent' => 0,
                'service_charge_percent' => 0,
                'rounding_mode' => 'none',
                'require_open_shift' => true,
                'allow_negative_stock' => false,
            ]);
            $this->info("Data Outlet default berhasil diinisialisasi.");
        }

        return Command::SUCCESS;
    }
}
