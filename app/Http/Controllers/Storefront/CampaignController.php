<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CampaignController extends Controller
{
    public function show(string $campaign): Response|RedirectResponse
    {
        [$slug, $publicId] = $this->splitSlugAndPublicId($campaign);

        $campaign = Campaign::query()
            ->where('public_id', $publicId)
            ->where('status', 'published')
            ->firstOrFail();

        if ($campaign->slug !== $slug) {
            return redirect()->route('campaigns.show', [
                'campaign' => $campaign->slug.'-'.$campaign->public_id,
            ], 301);
        }

        return Inertia::render('Storefront/CampaignShow', [
            'campaign' => $campaign->only(['name', 'slug', 'public_id', 'description', 'starts_at', 'ends_at']),
        ]);
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function splitSlugAndPublicId(string $value): array
    {
        abort_if(strlen($value) < 28, 404);

        $publicId = substr($value, -26);
        $slug = rtrim(substr($value, 0, -26), '-');

        abort_unless($slug !== '' && preg_match('/^[0-7][0-9a-hjkmnp-tv-zA-HJKMNP-TV-Z]{25}$/', $publicId), 404);

        return [$slug, $publicId];
    }
}
