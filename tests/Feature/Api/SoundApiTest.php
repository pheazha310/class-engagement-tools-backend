<?php

use App\Events\SoundPlayed;
use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Sound;
use App\Models\User;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    $this->withoutMiddleware(VerifyCsrfToken::class);
});

test('unauthenticated user can list sounds (public route)', function () {
    $this->getJson('/api/sounds')
        ->assertOk();
});

test('api returns list of available sounds', function () {
    Sound::create([
        'name' => 'Applause',
        'audio_url' => '/sounds/applause.mp3',
        'category' => 'Rewards',
        'icon' => '👏',
        'duration_seconds' => 3,
    ]);

    Sound::create([
        'name' => 'Bell Ring',
        'audio_url' => '/sounds/bell.mp3',
        'category' => 'Transitions',
        'icon' => '🔔',
        'duration_seconds' => 2,
    ]);

    $response = $this->getJson('/api/sounds');

    $response->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'audio_url',
                    'category',
                    'icon',
                    'duration_seconds',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);
});

test('authenticated teacher can play a sound', function () {
    Event::fake([SoundPlayed::class]);

    $user = User::factory()->create();
    $sound = Sound::create([
        'name' => 'Fanfare',
        'audio_url' => '/sounds/fanfare.mp3',
        'category' => 'Rewards',
        'icon' => '🎺',
        'duration_seconds' => 5,
    ]);

    $response = $this
        ->actingAs($user)
        ->postJson("/api/sounds/{$sound->id}/play");

    $response->assertOk()
        ->assertJson([
            'message' => 'Sound played successfully.',
            'data' => [
                'soundId' => $sound->id,
                'soundName' => 'Fanfare',
                'audioUrl' => '/sounds/fanfare.mp3',
            ],
        ]);

    Event::assertDispatched(SoundPlayed::class, function ($event) use ($sound) {
        return $event->soundId === $sound->id
            && $event->soundName === 'Fanfare'
            && $event->audioUrl === '/sounds/fanfare.mp3'
            && $event->category === 'Rewards'
            && $event->icon === '🎺'
            && $event->durationSeconds === 5;
    });
});

test('unauthenticated user cannot play a sound', function () {
    $sound = Sound::create([
        'name' => 'Applause',
        'audio_url' => '/sounds/applause.mp3',
        'category' => 'Rewards',
        'icon' => '👏',
        'duration_seconds' => 3,
    ]);

    $this->postJson("/api/sounds/{$sound->id}/play")
        ->assertStatus(401);
});

test('playing non-existent sound returns 404', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson('/api/sounds/non-existent-id/play')
        ->assertStatus(404);
});
