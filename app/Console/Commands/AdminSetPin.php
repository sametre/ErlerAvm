<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

#[Signature('admin:set-pin {email : Yönetici kullanıcının e-posta adresi} {--pin= : 6 haneli PIN. Boş bırakılırsa güvenli rastgele PIN üretilir.}')]
#[Description('Hashlenmiş yönetici PIN kodu oluşturur veya yeniler.')]
class AdminSetPin extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = (string) $this->argument('email');
        $user = User::query()->where('email', $email)->first();

        if (! $user instanceof User) {
            $this->error('Kullanıcı bulunamadı.');

            return self::FAILURE;
        }

        if (! $user->isAdmin()) {
            $this->error('Bu kullanıcı admin yetkisine sahip değil.');

            return self::FAILURE;
        }

        $pin = (string) ($this->option('pin') ?: str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT));

        if (! preg_match('/^\d{6}$/', $pin)) {
            $this->error('PIN tam 6 haneli olmalıdır.');

            return self::FAILURE;
        }

        $user->forceFill([
            'admin_pin_hash' => Hash::make($pin),
            'admin_2fa_verified_at' => null,
        ])->save();

        $this->info('Admin PIN hash güncellendi.');

        if (! $this->option('pin')) {
            $this->warn('Bu PIN yalnızca şimdi gösterilir: '.$pin);
        }

        return self::SUCCESS;
    }
}
