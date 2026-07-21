<?php

namespace Database\Seeders;

use App\Models\Sound;
use Illuminate\Database\Seeder;

class SoundSeeder extends Seeder
{
    public function run(): void
    {
        $sounds = [
            [
                'name' => 'Applause',
                'audio_url' => '/sounds/applause.mp3',
                'category' => 'Rewards',
                'icon' => '👏',
                'duration_seconds' => 3,
            ],
            [
                'name' => 'Correct Answer',
                'audio_url' => '/sounds/correct.mp3',
                'category' => 'Quiz',
                'icon' => '✅',
                'duration_seconds' => 2,
            ],
            [
                'name' => 'Wrong Answer',
                'audio_url' => '/sounds/wrong.mp3',
                'category' => 'Quiz',
                'icon' => '❌',
                'duration_seconds' => 2,
            ],
            [
                'name' => 'Drum Roll',
                'audio_url' => '/sounds/drum-roll.mp3',
                'category' => 'Suspense',
                'icon' => '🥁',
                'duration_seconds' => 4,
            ],
            [
                'name' => 'Fanfare',
                'audio_url' => '/sounds/fanfare.mp3',
                'category' => 'Rewards',
                'icon' => '🎺',
                'duration_seconds' => 5,
            ],
            [
                'name' => 'Bell Ring',
                'audio_url' => '/sounds/bell.mp3',
                'category' => 'Transitions',
                'icon' => '🔔',
                'duration_seconds' => 2,
            ],
            [
                'name' => 'Timer Alert',
                'audio_url' => '/sounds/timer.mp3',
                'category' => 'Timers',
                'icon' => '⏰',
                'duration_seconds' => 1,
            ],
            [
                'name' => 'Ticking Clock',
                'audio_url' => '/sounds/tick-tock.mp3',
                'category' => 'Timers',
                'icon' => '🕐',
                'duration_seconds' => 10,
            ],
            [
                'name' => 'Celebration',
                'audio_url' => '/sounds/celebration.mp3',
                'category' => 'Rewards',
                'icon' => '🎉',
                'duration_seconds' => 4,
            ],
            [
                'name' => 'Transition Whoosh',
                'audio_url' => '/sounds/whoosh.mp3',
                'category' => 'Transitions',
                'icon' => '💨',
                'duration_seconds' => 1,
            ],
            [
                'name' => 'Game Show',
                'audio_url' => '/sounds/game-show.mp3',
                'category' => 'Suspense',
                'icon' => '🎯',
                'duration_seconds' => 3,
            ],
            [
                'name' => 'Classroom Chime',
                'audio_url' => '/sounds/chime.mp3',
                'category' => 'Transitions',
                'icon' => '🎵',
                'duration_seconds' => 2,
            ],
            [
                'name' => 'Laughter',
                'audio_url' => '/sounds/laughter.mp3',
                'category' => 'Fun',
                'icon' => '😂',
                'duration_seconds' => 3,
            ],
            [
                'name' => 'Boing',
                'audio_url' => '/sounds/boing.mp3',
                'category' => 'Fun',
                'icon' => '🦘',
                'duration_seconds' => 1,
            ],
            [
                'name' => 'High Score',
                'audio_url' => '/sounds/high-score.mp3',
                'category' => 'Rewards',
                'icon' => '🏆',
                'duration_seconds' => 4,
            ],
        ];

        foreach ($sounds as $sound) {
            Sound::create($sound);
        }
    }
}
