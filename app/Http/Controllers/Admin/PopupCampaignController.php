<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PopupCampaign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PopupCampaignController extends Controller
{
    public function index()
    {
        $campaigns = PopupCampaign::latest()->get();

        return Inertia::render('Admin/PopupCampaigns', [
            'campaigns' => $campaigns,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'target_url' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'image' => 'required|image|max:2048',
        ]);

        if ($validated['is_active'] ?? false) {
            PopupCampaign::query()->update(['is_active' => false]);
        }

        $imagePath = $request->file('image')->store('campaigns', 'public');

        PopupCampaign::create([
            'public_id' => Str::random(10),
            'name' => $validated['name'],
            'target_url' => $validated['target_url'],
            'is_active' => $validated['is_active'] ?? false,
            'image_path' => '/storage/'.$imagePath,
        ]);

        return redirect()->back()->with('success', 'Kampanya oluşturuldu.');
    }

    public function update(Request $request, PopupCampaign $popupCampaign)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'target_url' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($validated['is_active'] ?? false) {
            PopupCampaign::query()->where('id', '!=', $popupCampaign->id)->update(['is_active' => false]);
        }

        $data = [
            'name' => $validated['name'],
            'target_url' => $validated['target_url'],
            'is_active' => $validated['is_active'] ?? false,
        ];

        if ($request->hasFile('image')) {
            if ($popupCampaign->image_path) {
                $oldPath = str_replace('/storage/', '', $popupCampaign->image_path);
                Storage::disk('public')->delete($oldPath);
            }
            $imagePath = $request->file('image')->store('campaigns', 'public');
            $data['image_path'] = '/storage/'.$imagePath;
        }

        $popupCampaign->update($data);

        return redirect()->back()->with('success', 'Kampanya güncellendi.');
    }

    public function destroy(PopupCampaign $popupCampaign)
    {
        if ($popupCampaign->image_path) {
            $oldPath = str_replace('/storage/', '', $popupCampaign->image_path);
            Storage::disk('public')->delete($oldPath);
        }
        $popupCampaign->delete();

        return redirect()->back()->with('success', 'Kampanya silindi.');
    }
}
