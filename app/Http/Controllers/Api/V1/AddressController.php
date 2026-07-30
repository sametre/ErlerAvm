<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerAddressRequest;
use App\Http\Traits\ApiResponse;
use App\Models\CustomerAddress;
use App\Services\Api\AddressBookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly AddressBookService $addresses) {}

    public function index(Request $request): JsonResponse
    {
        return $this->success(
            $this->addresses->listForUser($request->user())
                ->map(fn (CustomerAddress $address) => $this->payload($address))
                ->values()
        );
    }

    public function store(CustomerAddressRequest $request): JsonResponse
    {
        $address = $this->addresses->create($request->user(), $request->validated());

        return $this->success($this->payload($address), 'Adres eklendi.', 201);
    }

    public function show(Request $request, string $publicId): JsonResponse
    {
        $address = $request->user()->addresses()->where('public_id', $publicId)->firstOrFail();

        return $this->success($this->payload($address));
    }

    public function update(CustomerAddressRequest $request, string $publicId): JsonResponse
    {
        $address = $request->user()->addresses()->where('public_id', $publicId)->firstOrFail();

        $address = $this->addresses->update($request->user(), $address, $request->validated());

        return $this->success($this->payload($address), 'Adres güncellendi.');
    }

    public function destroy(Request $request, string $publicId): JsonResponse
    {
        $address = $request->user()->addresses()->where('public_id', $publicId)->firstOrFail();

        $this->addresses->delete($request->user(), $address);

        return $this->success(null, 'Adres silindi.');
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(CustomerAddress $address): array
    {
        return [
            'public_id' => $address->public_id,
            'type' => $address->type,
            'title' => $address->title,
            'full_name' => $address->full_name,
            'phone' => $address->phone,
            'city' => $address->city,
            'district' => $address->district,
            'postal_code' => $address->postal_code,
            'address_line' => $address->address_line,
            'is_default' => $address->is_default,
            'created_at' => $address->created_at?->toIso8601String(),
            'updated_at' => $address->updated_at?->toIso8601String(),
        ];
    }
}
