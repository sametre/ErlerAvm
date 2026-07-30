<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\Campaign;
use Illuminate\Http\JsonResponse;

class CampaignController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $campaigns = Campaign::query()
            ->where('status', 'published')
            ->latest()
            ->get(['public_id', 'name', 'slug', 'description', 'starts_at', 'ends_at']);

        return $this->success($campaigns);
    }
}
