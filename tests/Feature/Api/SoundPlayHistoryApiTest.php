<?php

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Sound;
use App\Models\SoundPlayHistory;
use App\Models\User;

beforeEach(function () {
    $this->withoutMiddleware(VerifyCsrfToken::class);
});

test('playing a sound records play history', function () {
    $user = User::factory()->create();
    $sound = Sound::create([
        'name' => 'Applause',
        'audio_url' => '/sounds/applause.mp3',
        'category' => 'Rewards',
        'icon' => '👏',
        'duration_seconds' => 3,
    ]);

    $this->actingAs($user)
        ->postJson("/api/sounds/{$sound->id}/play")
        ->assertOk();

    expect(SoundPlayHistory::count())->toBe(1);

    $history = SoundPlayHistory::first();
    expect($history->sound_id)->toBe($sound->id);
    expect($history->sound_name)->toBe('Applause');
    expect($history->audio_url)->toBe('/sounds/applause.mp3');
    expect($history->sound_category)->toBe('Rewards');
    expect($history->icon)->toBe('👏');
    expect($history->duration_seconds)->toBe(3);
    expect($history->played_by)->toBe($user->id);
    expect($history->played_at)->not->toBeNull();
});

test('sound history stores the correct denormalized data', function () {
    $user = User::factory()->create();
    $sound = Sound::create([
        'name' => 'Drum Roll',
        'audio_url' => '/sounds/drum-roll.mp3',
        'category' => 'Suspense',
        'icon' => '🥁',
        'duration_seconds' => 4,
    ]);

    $this->actingAs($user)
        ->postJson("/api/sounds/{$sound->id}/play")
        ->assertOk();

    expect(SoundPlayHistory::count())->toBe(1);
    $history = SoundPlayHistory::first();
    expect($history->sound_name)->toBe('Drum Roll');
    expect($history->audio_url)->toBe('/sounds/drum-roll.mp3');
    expect($history->sound_category)->toBe('Suspense');
    expect($history->icon)->toBe('🥁');
    expect($history->duration_seconds)->toBe(4);
});

test('authenticated teacher can retrieve play history', function () {
    $user = User::factory()->create();
    $sound = Sound::create([
        'name' => 'Applause',
        'audio_url' => '/sounds/applause.mp3',
        'category' => 'Rewards',
        'icon' => '👏',
        'duration_seconds' => 3,
    ]);

    // Play the sound a couple times
    $this->actingAs($user)->postJson("/api/sounds/{$sound->id}/play");
    $this->actingAs($user)->postJson("/api/sounds/{$sound->id}/play");

    $response = $this
        ->actingAs($user)
        ->getJson('/api/sounds/history');

    $response->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'sound_id',
                    'sound_name',
                    'audio_url',
                    'sound_category',
                    'icon',
                    'duration_seconds',
                    'played_by',
                    'played_at',
                    'created_at',
                ],
            ],
            'meta' => [
                'current_page',
                'last_page',
                'per_page',
                'total',
            ],
        ]);
});

test('play history is ordered by most recent first', function () {
    $user = User::factory()->create();
    $sound = Sound::create([
        'name' => 'Test Sound',
        'audio_url' => '/sounds/test.mp3',
        'category' => 'Fun',
        'icon' => '🎵',
        'duration_seconds' => 1,
    ]);

    SoundPlayHistory::create([
        'sound_id' => $sound->id,
        'sound_name' => $sound->name,
        'audio_url' => $sound->audio_url,
        'sound_category' => $sound->category,
        'icon' => $sound->icon,
        'duration_seconds' => $sound->duration_seconds,
        'played_by' => $user->id,
        'played_at' => now()->subMinutes(5),
    ]);

    SoundPlayHistory::create([
        'sound_id' => $sound->id,
        'sound_name' => $sound->name,
        'audio_url' => $sound->audio_url,
        'sound_category' => $sound->category,
        'icon' => $sound->icon,
        'duration_seconds' => $sound->duration_seconds,
        'played_by' => $user->id,
        'played_at' => now(),
    ]);

    $response = $this
        ->actingAs($user)
        ->getJson('/api/sounds/history');

    $response->assertOk();
    $data = $response->json('data');
    expect($data[0]['played_at'])->toBeGreaterThan($data[1]['played_at']);
});

test('unauthenticated user cannot retrieve play history', function () {
    $this->getJson('/api/sounds/history')
        ->assertStatus(401);
});
