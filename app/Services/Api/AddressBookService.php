<?php

namespace App\Services\Api;

use App\Models\CustomerAddress;
use App\Models\User;
use App\Services\Notifications\UserNotificationService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AddressBookService
{
    public function __construct(private readonly UserNotificationService $notifications) {}

    /**
     * @return Collection<int, CustomerAddress>
     */
    public function listForUser(User $user): Collection
    {
        return $user->addresses()
            ->latest()
            ->get(['id', 'public_id', 'type', 'title', 'full_name', 'phone', 'city', 'district', 'postal_code', 'address_line', 'is_default', 'created_at', 'updated_at']);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(User $user, array $data): CustomerAddress
    {
        return DB::transaction(function () use ($user, $data): CustomerAddress {
            $hasAddresses = $user->addresses()->lockForUpdate()->exists();
            $data['is_default'] = ! $hasAddresses || (bool) ($data['is_default'] ?? false);

            if ($data['is_default']) {
                $user->addresses()->update(['is_default' => false]);
            }

            $address = $user->addresses()->create($data);

            $this->notifications->createForUser(
                user: $user,
                type: 'address.created',
                title: 'Adres eklendi',
                body: $address->title.' adresiniz hesabınıza kaydedildi.',
                actionUrl: '/hesabim/adreslerim',
                data: ['address_public_id' => $address->public_id],
            );

            Log::info('Customer address created.', [
                'user_id' => $user->id,
                'address_public_id' => $address->public_id,
            ]);

            return $address;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, CustomerAddress $address, array $data): CustomerAddress
    {
        return DB::transaction(function () use ($user, $address, $data): CustomerAddress {
            $data['is_default'] = (bool) ($data['is_default'] ?? false);

            if ($data['is_default']) {
                $user->addresses()
                    ->where('id', '!=', $address->id)
                    ->update(['is_default' => false]);
            }

            $address->update($data);

            $this->ensureSingleDefault($user);

            $this->notifications->createForUser(
                user: $user,
                type: 'address.updated',
                title: 'Adres güncellendi',
                body: $address->title.' adresiniz güncellendi.',
                actionUrl: '/hesabim/adreslerim',
                data: ['address_public_id' => $address->public_id],
            );

            Log::info('Customer address updated.', [
                'user_id' => $user->id,
                'address_public_id' => $address->public_id,
            ]);

            return $address->refresh();
        });
    }

    public function delete(User $user, CustomerAddress $address): void
    {
        DB::transaction(function () use ($user, $address): void {
            $wasDefault = $address->is_default;
            $publicId = $address->public_id;
            $title = $address->title;

            $address->delete();

            if ($wasDefault) {
                $nextDefault = $user->addresses()->latest()->first();
                $nextDefault?->update(['is_default' => true]);
            }

            $this->notifications->createForUser(
                user: $user,
                type: 'address.deleted',
                title: 'Adres silindi',
                body: $title.' adresiniz hesabınızdan kaldırıldı.',
                actionUrl: '/hesabim/adreslerim',
                data: ['address_public_id' => $publicId],
            );

            Log::warning('Customer address deleted.', [
                'user_id' => $user->id,
                'address_public_id' => $publicId,
            ]);
        });
    }

    private function ensureSingleDefault(User $user): void
    {
        $defaultIds = $user->addresses()
            ->where('is_default', true)
            ->latest()
            ->pluck('id');

        if ($defaultIds->isEmpty()) {
            $user->addresses()->latest()->first()?->update(['is_default' => true]);

            return;
        }

        if ($defaultIds->count() <= 1) {
            return;
        }

        $user->addresses()
            ->whereIn('id', $defaultIds->skip(1)->all())
            ->update(['is_default' => false]);
    }
}
