<?php

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\User;
use App\Models\Wheel;
use Illuminate\Http\UploadedFile;

beforeEach(function () {
    $this->withoutMiddleware(VerifyCsrfToken::class);
});

test('teacher can create a new wheel', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->postJson('/api/wheels', [
            'name' => 'Morning Circle',
            'description' => 'Ice breaker wheel',
            'color' => '#FF5733',
        ]);

    $response->assertCreated()
        ->assertJson([
            'name' => 'Morning Circle',
            'description' => 'Ice breaker wheel',
            'color' => '#FF5733',
        ])
        ->assertJsonStructure([
            'id',
            'name',
            'description',
            'color',
            'participants' => [],
        ]);

    expect(Wheel::count())->toBe(1);
});

test('wheel creation validates required fields', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->postJson('/api/wheels', []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['name']);

    $response = $this
        ->actingAs($user)
        ->postJson('/api/wheels', ['name' => str_repeat('a', 256)]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['name']);
});

test('teacher can update wheel information', function () {
    $user = User::factory()->create();
    $wheel = Wheel::factory()->for($user)->create(['name' => 'Old Name']);

    $response = $this
        ->actingAs($user)
        ->putJson("/api/wheels/{$wheel->id}", [
            'name' => 'New Name',
            'description' => 'Updated description',
        ]);

    $response->assertOk()
        ->assertJson([
            'name' => 'New Name',
            'description' => 'Updated description',
        ]);

    $wheel->refresh();
    expect($wheel->name)->toBe('New Name');
});

test('system returns wheel details with participant list', function () {
    $user = User::factory()->create();
    $wheel = Wheel::factory()->for($user)->create();
    $wheel->participants()->createMany([
        ['name' => 'Alice'],
        ['name' => 'Bob'],
    ]);

    $response = $this
        ->actingAs($user)
        ->getJson("/api/wheels/{$wheel->id}");

    $response->assertOk()
        ->assertJson([
            'id' => $wheel->id,
            'name' => $wheel->name,
            'participants' => [
                ['name' => 'Alice'],
                ['name' => 'Bob'],
            ],
        ]);
});

test('unauthorized user cannot access another users wheel', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $wheel = Wheel::factory()->for($owner)->create();

    $this->actingAs($other)
        ->getJson("/api/wheels/{$wheel->id}")
        ->assertStatus(403);

    $this->actingAs($other)
        ->putJson("/api/wheels/{$wheel->id}", ['name' => 'Hacked'])
        ->assertStatus(403);

    $this->actingAs($other)
        ->deleteJson("/api/wheels/{$wheel->id}")
        ->assertStatus(403);
});

test('unauthenticated user cannot access wheels', function () {
    $this->getJson('/api/wheels')->assertStatus(401);
    $this->postJson('/api/wheels', [])->assertStatus(401);
});

test('teacher can list their own wheels', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $user->wheels()->createMany([
        ['name' => 'Wheel 1', 'description' => 'First'],
        ['name' => 'Wheel 2', 'description' => 'Second'],
    ]);
    $other->wheels()->create(['name' => 'Other Wheel']);

    $response = $this
        ->actingAs($user)
        ->getJson('/api/wheels');

    $response->assertOk()
        ->assertJsonCount(2)
        ->assertJson([
            ['name' => 'Wheel 1'],
            ['name' => 'Wheel 2'],
        ]);
});

test('teacher can add participant to wheel', function () {
    $user = User::factory()->create();
    $wheel = Wheel::factory()->for($user)->create();

    $response = $this
        ->actingAs($user)
        ->postJson("/api/wheels/{$wheel->id}/participants", [
            'name' => 'Charlie',
        ]);

    $response->assertCreated()
        ->assertJson([
            'name' => 'Charlie',
        ]);

    expect($wheel->participants->count())->toBe(1);
});

test('participant creation validates required fields', function () {
    $user = User::factory()->create();
    $wheel = Wheel::factory()->for($user)->create();

    $response = $this
        ->actingAs($user)
        ->postJson("/api/wheels/{$wheel->id}/participants", []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['name']);
});

test('unauthorized user cannot add participant to another users wheel', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $wheel = Wheel::factory()->for($owner)->create();

    $this->actingAs($other)
        ->postJson("/api/wheels/{$wheel->id}/participants", ['name' => 'Eve'])
        ->assertStatus(403);
});

test('teacher can remove participant from wheel', function () {
    $user = User::factory()->create();
    $wheel = Wheel::factory()->for($user)->create();
    $participant = $wheel->participants()->create(['name' => 'Dave']);

    $response = $this
        ->actingAs($user)
        ->deleteJson("/api/wheels/{$wheel->id}/participants/{$participant->id}");

    $response->assertNoContent();
    expect($wheel->participants()->count())->toBe(0);
});

test('teacher can delete their wheel', function () {
    $user = User::factory()->create();
    $wheel = Wheel::factory()->for($user)->create();

    $response = $this
        ->actingAs($user)
        ->deleteJson("/api/wheels/{$wheel->id}");

    $response->assertNoContent();
    expect(Wheel::find($wheel->id))->toBeNull();
});

test('teacher can spin wheel with participants array', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->postJson('/api/wheel/spin', [
            'participants' => [
                ['id' => 1, 'name' => 'Alice'],
                ['id' => 2, 'name' => 'Bob'],
            ],
        ]);

    $response->assertOk()
        ->assertJsonStructure([
            'participant' => [
                'id',
                'name',
            ],
        ]);

    $name = $response->json('participant.name');
    expect(in_array($name, ['Alice', 'Bob']))->toBeTrue();
});

test('teacher can spin wheel tied to a wheel', function () {
    $user = User::factory()->create();
    $wheel = Wheel::factory()->for($user)->create(['removal_mode' => false]);
    $wheel->participants()->createMany([
        ['name' => 'Alice'],
        ['name' => 'Bob'],
    ]);

    $response = $this
        ->actingAs($user)
        ->postJson('/api/wheel/spin', [
            'participants' => [
                ['id' => 1, 'name' => 'Alice'],
                ['id' => 2, 'name' => 'Bob'],
            ],
            'wheel_id' => $wheel->id,
        ]);

    $response->assertOk()
        ->assertJsonStructure([
            'participant',
            'history',
        ]);

    $name = $response->json('participant.name');
    expect(in_array($name, ['Alice', 'Bob']))->toBeTrue();

    expect($wheel->participants()->count())->toBe(2);
    expect($wheel->spinHistories()->count())->toBe(1);
});

test('spin removes participant when removal mode is enabled', function () {
    $user = User::factory()->create();
    $wheel = Wheel::factory()->for($user)->create(['removal_mode' => true]);
    $wheel->participants()->createMany([
        ['name' => 'Alice'],
        ['name' => 'Bob'],
    ]);

    $response = $this
        ->actingAs($user)
        ->postJson('/api/wheel/spin', [
            'participants' => [
                ['id' => 1, 'name' => 'Alice'],
                ['id' => 2, 'name' => 'Bob'],
            ],
            'wheel_id' => $wheel->id,
            'removal_mode' => true,
        ]);

    $response->assertOk()
        ->assertJsonStructure([
            'participant',
            'history',
        ]);

    $name = $response->json('participant.name');
    expect(in_array($name, ['Alice', 'Bob']))->toBeTrue();

    $history = $wheel->spinHistories()->first();
    expect($history->participant_name)->toBe($name);

    $wheel->refresh();
    expect($wheel->participants()->count())->toBe(1);
    expect($wheel->participants()->where('name', $name)->exists())->toBeFalse();
});

test('spin returns 404 when participants list is empty', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->postJson('/api/wheel/spin', []);

    $response->assertStatus(404)
        ->assertJson(['message' => 'No participants available to spin']);
});

test('spin returns 404 when wheel has no participants', function () {
    $user = User::factory()->create();
    $wheel = Wheel::factory()->for($user)->create();

    $response = $this
        ->actingAs($user)
        ->postJson('/api/wheel/spin', [
            'participants' => [
                ['id' => 1, 'name' => 'Ghost'],
            ],
            'wheel_id' => $wheel->id,
        ]);

    $response->assertStatus(404)
        ->assertJson(['message' => 'No participants available to spin']);
});

test('teacher can generate a share token for their wheel', function () {
    $user = User::factory()->create();
    $wheel = Wheel::factory()->for($user)->create();

    $response = $this
        ->actingAs($user)
        ->postJson("/api/wheels/{$wheel->id}/share-token");

    $response->assertCreated()
        ->assertJsonStructure([
            'share_token',
            'shared_url',
        ]);

    $token = $response->json('share_token');
    expect(strlen($token))->toBe(64);

    $wheel->refresh();
    expect($wheel->share_token)->not->toBeNull();
});

test('unauthorized user cannot generate share token for another users wheel', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $wheel = Wheel::factory()->for($owner)->create();

    $this->actingAs($other)
        ->postJson("/api/wheels/{$wheel->id}/share-token")
        ->assertStatus(403);
});

test('unauthenticated user cannot generate share token', function () {
    $wheel = Wheel::factory()->create();

    $this->postJson("/api/wheels/{$wheel->id}/share-token")
        ->assertStatus(403);
});

test('public user can view shared wheel with valid token', function () {
    $user = User::factory()->create();
    $wheel = Wheel::factory()->for($user)->create();
    $wheel->participants()->createMany([
        ['name' => 'Alice'],
        ['name' => 'Bob'],
    ]);

    $token = $wheel->generateShareToken();

    $response = $this->getJson("/api/wheels/shared/{$token}");

    $response->assertOk()
        ->assertJson([
            'id' => $wheel->id,
            'name' => $wheel->name,
            'participants' => [
                ['name' => 'Alice'],
                ['name' => 'Bob'],
            ],
        ]);
});

test('shared wheel endpoint returns 404 for invalid token', function () {
    $this->getJson('/api/wheels/shared/invalid-token-12345')
        ->assertStatus(404)
        ->assertJson(['message' => 'Shared wheel not found']);
});

test('public user cannot modify wheel via shared endpoint', function () {
    $user = User::factory()->create();
    $wheel = Wheel::factory()->for($user)->create();
    $token = $wheel->generateShareToken();

    $this->putJson("/api/wheels/shared/{$token}", ['name' => 'Hacked'])
        ->assertStatus(405);

    $this->deleteJson("/api/wheels/shared/{$token}")
        ->assertStatus(405);
});

test('public user cannot add participant to shared wheel', function () {
    $user = User::factory()->create();
    $wheel = Wheel::factory()->for($user)->create();
    $token = $wheel->generateShareToken();

    $this->postJson("/api/wheels/shared/{$token}/participants", ['name' => 'Eve'])
        ->assertStatus(404);
});

test('share token is unique across wheels', function () {
    $user = User::factory()->create();
    $wheel1 = Wheel::factory()->for($user)->create();
    $wheel2 = Wheel::factory()->for($user)->create();

    $token1 = $wheel1->generateShareToken();

    $token2 = $wheel2->generateShareToken();

    expect($token1)->not->toBe($token2);

    $this->getJson("/api/wheels/shared/{$token1}")
        ->assertOk()
        ->assertJson(['id' => $wheel1->id]);

    $this->getJson("/api/wheels/shared/{$token2}")
        ->assertOk()
        ->assertJson(['id' => $wheel2->id]);
});

test('teacher can import participants from csv file', function () {
    $user = User::factory()->create();
    $wheel = Wheel::factory()->for($user)->create();

    $csv = "Alice,Bob,Charlie\nDiana,Eve";

    $file = tempnam(sys_get_temp_dir(), 'csv').'.csv';
    file_put_contents($file, $csv);

    $response = $this
        ->actingAs($user)
        ->postJson("/api/wheels/{$wheel->id}/participants/import", [
            'file' => new UploadedFile(
                $file,
                'participants.csv',
                'text/csv',
                null,
                true
            ),
        ]);

    $response->assertCreated()
        ->assertJsonCount(5, 'imported');

    expect($wheel->participants()->count())->toBe(5);

    unlink($file);
});

test('teacher can import participants from txt file', function () {
    $user = User::factory()->create();
    $wheel = Wheel::factory()->for($user)->create();

    $txt = "Alice\nBob\nCharlie";

    $file = tempnam(sys_get_temp_dir(), 'txt').'.txt';
    file_put_contents($file, $txt);

    $response = $this
        ->actingAs($user)
        ->postJson("/api/wheels/{$wheel->id}/participants/import", [
            'file' => new UploadedFile(
                $file,
                'participants.txt',
                'text/plain',
                null,
                true
            ),
        ]);

    $response->assertCreated()
        ->assertJsonCount(3, 'imported');

    expect($wheel->participants()->count())->toBe(3);

    unlink($file);
});

test('import removes empty values', function () {
    $user = User::factory()->create();
    $wheel = Wheel::factory()->for($user)->create();

    $txt = "Alice\n\nBob\n  \nCharlie\n";

    $file = tempnam(sys_get_temp_dir(), 'txt').'.txt';
    file_put_contents($file, $txt);

    $response = $this
        ->actingAs($user)
        ->postJson("/api/wheels/{$wheel->id}/participants/import", [
            'file' => new UploadedFile(
                $file,
                'participants.txt',
                'text/plain',
                null,
                true
            ),
        ]);

    $response->assertCreated()
        ->assertJsonCount(3, 'imported');

    expect($wheel->participants()->count())->toBe(3);

    unlink($file);
});

test('import handles duplicate names within file', function () {
    $user = User::factory()->create();
    $wheel = Wheel::factory()->for($user)->create();

    $txt = "Alice\nBob\nAlice\nCharlie\nBob";

    $file = tempnam(sys_get_temp_dir(), 'txt').'.txt';
    file_put_contents($file, $txt);

    $response = $this
        ->actingAs($user)
        ->postJson("/api/wheels/{$wheel->id}/participants/import", [
            'file' => new UploadedFile(
                $file,
                'participants.txt',
                'text/plain',
                null,
                true
            ),
        ]);

    $response->assertCreated()
        ->assertJsonCount(3, 'imported');

    expect($wheel->participants()->count())->toBe(3);

    unlink($file);
});

test('import skips duplicates against existing participants', function () {
    $user = User::factory()->create();
    $wheel = Wheel::factory()->for($user)->create();
    $wheel->participants()->create(['name' => 'Alice']);

    $txt = "Alice\nBob\nCharlie";

    $file = tempnam(sys_get_temp_dir(), 'txt').'.txt';
    file_put_contents($file, $txt);

    $response = $this
        ->actingAs($user)
        ->postJson("/api/wheels/{$wheel->id}/participants/import", [
            'file' => new UploadedFile(
                $file,
                'participants.txt',
                'text/plain',
                null,
                true
            ),
        ]);

    $response->assertCreated()
        ->assertJsonCount(2, 'imported')
        ->assertJsonCount(1, 'skipped');

    expect($wheel->participants()->count())->toBe(3);

    unlink($file);
});

test('invalid file type returns validation error', function () {
    $user = User::factory()->create();
    $wheel = Wheel::factory()->for($user)->create();

    $response = $this
        ->actingAs($user)
        ->postJson("/api/wheels/{$wheel->id}/participants/import", [
            'file' => 'not-a-file',
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['file']);
});

test('missing file returns validation error', function () {
    $user = User::factory()->create();
    $wheel = Wheel::factory()->for($user)->create();

    $response = $this
        ->actingAs($user)
        ->postJson("/api/wheels/{$wheel->id}/participants/import", []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['file']);
});

test('unauthorized user cannot import participants to another wheel', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $wheel = Wheel::factory()->for($owner)->create();

    $txt = "Alice\nBob";

    $file = tempnam(sys_get_temp_dir(), 'txt').'.txt';
    file_put_contents($file, $txt);

    $this->actingAs($other)
        ->postJson("/api/wheels/{$wheel->id}/participants/import", [
            'file' => new UploadedFile(
                $file,
                'participants.txt',
                'text/plain',
                null,
                true
            ),
        ])
        ->assertStatus(403);

    unlink($file);
});
