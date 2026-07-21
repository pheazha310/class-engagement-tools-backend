<?php

use App\Models\Country;
use App\Models\Province;
use App\Models\School;
use App\Models\User;
use App\Models\UserProfile;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
});

it('forbids non-admins from the user list', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->getJson('/api/admin/users')
        ->assertForbidden();
});

it('lets an admin view the user list', function () {
    $this->actingAs($this->admin)
        ->getJson('/api/admin/users')
        ->assertOk();
});

it('filters the user list by search term', function () {
    User::factory()->create(['name' => 'Findable Person']);
    User::factory()->create(['name' => 'Someone Else']);

    $response = $this->actingAs($this->admin)
        ->getJson('/api/admin/users?search=Findable')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data.0.name'))->toBe('Findable Person');
});

it('shows school, country, and province columns for registered users', function () {
    $country = Country::create([
        'name' => 'South Africa',
        'code' => 'ZA',
    ]);

    $province = Province::create([
        'country_id' => $country->id,
        'name' => 'Western Cape',
    ]);

    $school = School::create([
        'province_id' => $province->id,
        'name' => 'Brighton High School',
    ]);

    $user = User::factory()->create(['name' => 'Profile User']);
    UserProfile::create([
        'user_id' => $user->id,
        'country_id' => $country->id,
        'province_id' => $province->id,
        'school_id' => $school->id,
    ]);

    $response = $this->actingAs($this->admin)
        ->getJson('/api/admin/users?search=Profile')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data.0.name'))->toBe('Profile User');
    expect($response->json('data.0.school_name'))->toBe('Brighton High School');
    expect($response->json('data.0.country_name'))->toBe('South Africa');
    expect($response->json('data.0.province_name'))->toBe('Western Cape');
});

it('creates a user with roles', function () {
    $this->actingAs($this->admin)->postJson('/api/admin/users', [
        'name' => 'New Teacher',
        'email' => 'teacher@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'country_name' => 'South Africa',
        'province_name' => 'Western Cape',
        'school_name' => 'Brighton High School',
        'roles' => ['teacher'],
    ])->assertCreated();

    $user = User::where('email', 'teacher@example.com')->firstOrFail();

    expect($user->hasRole('teacher'))->toBeTrue();
    expect($user->email_verified_at)->not->toBeNull();
    expect($user->profile)->not->toBeNull();
    expect($user->profile?->country?->name)->toBe('South Africa');
    expect($user->profile?->province?->name)->toBe('Western Cape');
    expect($user->profile?->school?->name)->toBe('Brighton High School');
});

it('validates the user create form', function () {
    $this->actingAs($this->admin)->postJson('/api/admin/users', [
        'name' => '',
        'email' => 'not-an-email',
        'password' => 'short',
    ])->assertUnprocessable();
});

it('updates a user and syncs roles', function () {
    $user = User::factory()->create(['name' => 'Old Name']);
    $user->assignRole('teacher');

    $this->actingAs($this->admin)->putJson("/api/admin/users/{$user->id}", [
        'name' => 'New Name',
        'email' => $user->email,
        'password' => '',
        'country_name' => 'South Africa',
        'province_name' => 'Western Cape',
        'school_name' => 'Brighton High School',
        'roles' => ['student'],
    ])->assertOk();

    $user->refresh();

    expect($user->name)->toBe('New Name');
    expect($user->hasRole('student'))->toBeTrue();
    expect($user->hasRole('teacher'))->toBeFalse();
    expect($user->profile?->country?->name)->toBe('South Africa');
    expect($user->profile?->province?->name)->toBe('Western Cape');
    expect($user->profile?->school?->name)->toBe('Brighton High School');
});

it('leaves the password unchanged when left blank on update', function () {
    $user = User::factory()->create();
    $originalHash = $user->password;

    $this->actingAs($this->admin)->putJson("/api/admin/users/{$user->id}", [
        'name' => $user->name,
        'email' => $user->email,
        'password' => '',
        'roles' => [],
    ])->assertOk();

    expect($user->fresh()->password)->toBe($originalHash);
});

it('deletes a user', function () {
    $user = User::factory()->create();

    $this->actingAs($this->admin)
        ->deleteJson("/api/admin/users/{$user->id}")
        ->assertOk();

    expect(User::find($user->id))->toBeNull();
});

it('prevents an admin from deleting their own account', function () {
    $this->actingAs($this->admin)
        ->deleteJson("/api/admin/users/{$this->admin->id}")
        ->assertForbidden();

    expect(User::find($this->admin->id))->not->toBeNull();
});
