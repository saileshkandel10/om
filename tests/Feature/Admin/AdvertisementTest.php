<?php

use App\Models\Advertisement;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function createAdminUser(): User
{
    $admin = User::factory()->create();
    $role = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Administrator']);
    $admin->roles()->syncWithoutDetaching([$role->id]);

    return $admin;
}

test('admin can view advertisements list and analytics', function () {
    $admin = createAdminUser();
    Advertisement::factory()->count(3)->create();

    $response = $this->actingAs($admin)
        ->get(route('admin.advertisements.index'));

    $response->assertOk()
        ->assertViewIs('admin.advertisements.index')
        ->assertViewHas('advertisements')
        ->assertViewHas('stats');
});

test('admin can create text-based advertisement', function () {
    $admin = createAdminUser();

    $response = $this->actingAs($admin)
        ->post(route('admin.advertisements.store'), [
            'title' => 'New Free Checkup Campaign',
            'description' => 'Visit your nearest clinic this weekend for free health screening.',
            'type' => 'text',
            'background_color' => '#CD0B6F',
            'text_color' => '#FFFFFF',
            'button_color' => '#FFFFFF',
            'button_text_color' => '#CD0B6F',
            'cta_text' => 'Book Now',
            'cta_action' => 'https://ommegashreepharmaceuticals.com/campaign',
            'target_role' => 'patient',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(7)->toDateString(),
            'display_order' => 1,
            'is_active' => 1,
        ]);

    $response->assertRedirect(route('admin.advertisements.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('advertisements', [
        'title' => 'New Free Checkup Campaign',
        'type' => 'text',
        'target_role' => 'patient',
        'cta_text' => 'Book Now',
        'is_active' => true,
    ]);
});

test('admin can create image advertisement with file upload', function () {
    Storage::fake('public');
    $admin = createAdminUser();

    $file = UploadedFile::fake()->image('banner.jpg', 1200, 375);

    $response = $this->actingAs($admin)
        ->post(route('admin.advertisements.store'), [
            'title' => 'Mega Pharma Discount Banner',
            'type' => 'image',
            'image' => $file,
            'target_role' => 'pharmacy',
            'cta_text' => 'Order Now',
            'cta_action' => 'https://ommegashreepharmaceuticals.com/sourcing',
            'is_active' => 1,
        ]);

    $response->assertRedirect(route('admin.advertisements.index'));

    $ad = Advertisement::where('title', 'Mega Pharma Discount Banner')->first();
    expect($ad)->not->toBeNull()
        ->and($ad->image_path)->not->toBeNull();

    Storage::disk('public')->assertExists($ad->image_path);
});

test('admin can update advertisement and toggle status', function () {
    $admin = createAdminUser();
    $ad = Advertisement::factory()->create([
        'title' => 'Original Title',
        'is_active' => true,
    ]);

    // Update
    $updateRes = $this->actingAs($admin)
        ->put(route('admin.advertisements.update', $ad), [
            'title' => 'Updated Banner Title',
            'type' => 'text',
            'target_role' => 'doctor',
            'is_active' => 1,
        ]);

    $updateRes->assertRedirect(route('admin.advertisements.index'));
    expect($ad->fresh()->title)->toBe('Updated Banner Title')
        ->and($ad->fresh()->target_role)->toBe('doctor');

    // Toggle Active
    $toggleRes = $this->actingAs($admin)
        ->post(route('admin.advertisements.toggle-active', $ad));

    $toggleRes->assertRedirect();
    expect($ad->fresh()->is_active)->toBeFalse();
});

test('admin can delete advertisement', function () {
    Storage::fake('public');
    $admin = createAdminUser();

    $file = UploadedFile::fake()->image('banner_del.jpg');
    $path = $file->store('advertisements', 'public');

    $ad = Advertisement::factory()->create([
        'type' => 'image',
        'image_path' => $path,
    ]);

    $response = $this->actingAs($admin)
        ->delete(route('admin.advertisements.destroy', $ad));

    $response->assertRedirect(route('admin.advertisements.index'));

    $this->assertDatabaseMissing('advertisements', ['id' => $ad->id]);
    Storage::disk('public')->assertMissing($path);
});
