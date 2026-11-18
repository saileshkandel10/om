<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdvertisementRequest;
use App\Http\Requests\Admin\UpdateAdvertisementRequest;
use App\Models\Advertisement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdvertisementController extends Controller
{
    /**
     * Display a listing of the resource with analytics & filters.
     */
    public function index(Request $request): View
    {
        $query = Advertisement::query();

        if ($request->filled('role')) {
            $query->where('target_role', $request->role);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('cta_text', 'like', "%{$search}%");
            });
        }

        $advertisements = $query->orderBy('display_order', 'asc')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => Advertisement::count(),
            'active' => Advertisement::active()->count(),
            'total_impressions' => Advertisement::sum('impressions_count'),
            'total_clicks' => Advertisement::sum('clicks_count'),
            'avg_ctr' => Advertisement::sum('impressions_count') > 0
                ? round((Advertisement::sum('clicks_count') / Advertisement::sum('impressions_count')) * 100, 2)
                : 0.0,
        ];

        return view('admin.advertisements.index', compact('advertisements', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $presets = $this->getColorPresets();

        return view('admin.advertisements.create', compact('presets'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAdvertisementRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $data['image_path'] = $file->store('advertisements', 'public');
        }

        Advertisement::create($data);

        return redirect()->route('admin.advertisements.index')
            ->with('success', 'Advertisement banner created successfully.');
    }

    /**
     * Display the specified resource with analytics.
     */
    public function show(Advertisement $advertisement): View
    {
        return view('admin.advertisements.show', compact('advertisement'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Advertisement $advertisement): View
    {
        $presets = $this->getColorPresets();

        return view('admin.advertisements.edit', compact('advertisement', 'presets'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAdvertisementRequest $request, Advertisement $advertisement): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('image')) {
            // Delete previous image if exists
            if ($advertisement->image_path && Storage::disk('public')->exists($advertisement->image_path)) {
                Storage::disk('public')->delete($advertisement->image_path);
            }

            $file = $request->file('image');
            $data['image_path'] = $file->store('advertisements', 'public');
        }

        $advertisement->update($data);

        return redirect()->route('admin.advertisements.index')
            ->with('success', 'Advertisement banner updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Advertisement $advertisement): RedirectResponse
    {
        if ($advertisement->image_path && Storage::disk('public')->exists($advertisement->image_path)) {
            Storage::disk('public')->delete($advertisement->image_path);
        }

        $advertisement->delete();

        return redirect()->route('admin.advertisements.index')
            ->with('success', 'Advertisement deleted successfully.');
    }

    /**
     * Toggle active status.
     */
    public function toggleActive(Advertisement $advertisement): RedirectResponse
    {
        $advertisement->update([
            'is_active' => ! $advertisement->is_active,
        ]);

        $statusText = $advertisement->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Advertisement banner has been {$statusText}.");
    }

    /**
     * Color presets for text advertisement templates.
     *
     * @return array<string, array{name: string, bg: string, text: string, btn_bg: string, btn_text: string}>
     */
    protected function getColorPresets(): array
    {
        return [
            'brand_pink' => [
                'name' => 'Brand Magenta (Signature)',
                'bg' => 'linear-gradient(135deg, #CD0B6F 0%, #830444 100%)',
                'text' => '#FFFFFF',
                'btn_bg' => '#FFFFFF',
                'btn_text' => '#CD0B6F',
            ],
            'navy_dark' => [
                'name' => 'Royal Navy & Pink',
                'bg' => 'linear-gradient(135deg, #0F2C59 0%, #1E3A5F 100%)',
                'text' => '#FFFFFF',
                'btn_bg' => '#CD0B6F',
                'btn_text' => '#FFFFFF',
            ],
            'emerald_green' => [
                'name' => 'Health Emerald Green',
                'bg' => 'linear-gradient(135deg, #065F46 0%, #047857 100%)',
                'text' => '#FFFFFF',
                'btn_bg' => '#FDE047',
                'btn_text' => '#064E3B',
            ],
            'royal_purple' => [
                'name' => 'Premium Royal Purple',
                'bg' => 'linear-gradient(135deg, #581C87 0%, #7E22CE 100%)',
                'text' => '#FFFFFF',
                'btn_bg' => '#F3E8FF',
                'btn_text' => '#6B21A8',
            ],
            'sunset_orange' => [
                'name' => 'Sunset Gold & Amber',
                'bg' => 'linear-gradient(135deg, #C2410C 0%, #EA580C 100%)',
                'text' => '#FFFFFF',
                'btn_bg' => '#FFFFFF',
                'btn_text' => '#C2410C',
            ],
            'teal_cyan' => [
                'name' => 'Clinical Ocean Teal',
                'bg' => 'linear-gradient(135deg, #0E7490 0%, #0891B2 100%)',
                'text' => '#FFFFFF',
                'btn_bg' => '#FFFFFF',
                'btn_text' => '#0E7490',
            ],
            'slate_dark' => [
                'name' => 'Modern Slate Dark',
                'bg' => 'linear-gradient(135deg, #1E293B 0%, #0F172A 100%)',
                'text' => '#FFFFFF',
                'btn_bg' => '#38BDF8',
                'btn_text' => '#0F172A',
            ],
        ];
    }
}
