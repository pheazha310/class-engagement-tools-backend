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
                'audio_url' => '/sounds/applause.wav',
                'category' => 'Rewards',
                'icon' => '👏',
                'duration_seconds' => 4,
            ],
            [
                'name' => 'Correct Answer',
                'audio_url' => '/sounds/correct.wav',
                'category' => 'Quiz',
                'icon' => '✅',
                'duration_seconds' => 2,
            ],
            [
                'name' => 'Wrong Answer',
                'audio_url' => '/sounds/wrong.wav',
                'category' => 'Quiz',
                'icon' => '❌',
                'duration_seconds' => 2,
            ],
            [
                'name' => 'Drum Roll',
                'audio_url' => '/sounds/drum-roll.wav',
                'category' => 'Suspense',
                'icon' => '🥁',
                'duration_seconds' => 5,
            ],
            [
                'name' => 'Fanfare',
                'audio_url' => '/sounds/fanfare.wav',
                'category' => 'Rewards',
                'icon' => '🎺',
                'duration_seconds' => 5,
            ],
            [
                'name' => 'Bell Ring',
                'audio_url' => '/sounds/school-bell.wav',
                'category' => 'Transitions',
                'icon' => '🔔',
                'duration_seconds' => 3,
            ],
            [
                'name' => 'Timer Alert',
                'audio_url' => '/sounds/alarm.wav',
                'category' => 'Timers',
                'icon' => '⏰',
                'duration_seconds' => 1,
            ],
            [
                'name' => 'Ticking Clock',
                'audio_url' => '/sounds/beep.wav',
                'category' => 'Timers',
                'icon' => '🕐',
                'duration_seconds' => 20,
            ],
            [
                'name' => 'Celebration',
                'audio_url' => '/sounds/celebration.wav',
                'category' => 'Rewards',
                'icon' => '🎉',
                'duration_seconds' => 5,
            ],
            [
                'name' => 'Transition Whoosh',
                'audio_url' => '/sounds/whoosh.wav',
                'category' => 'Transitions',
                'icon' => '💨',
                'duration_seconds' => 1,
            ],
            [
                'name' => 'Game Show',
                'audio_url' => '/sounds/game-show.wav',
                'category' => 'Suspense',
                'icon' => '🎯',
                'duration_seconds' => 4,
            ],
            [
                'name' => 'Classroom Chime',
                'audio_url' => '/sounds/chime.wav',
                'category' => 'Transitions',
                'icon' => '🎵',
                'duration_seconds' => 2,
            ],
            [
                'name' => 'Laughter',
                'audio_url' => '/sounds/laughter.wav',
                'category' => 'Fun',
                'icon' => '😂',
                'duration_seconds' => 3,
            ],
            [
                'name' => 'Boing',
                'audio_url' => '/sounds/boing.wav',
                'category' => 'Fun',
                'icon' => '🦘',
                'duration_seconds' => 1,
            ],
            [
                'name' => 'High Score',
                'audio_url' => '/sounds/high-score.wav',
                'category' => 'Rewards',
                'icon' => '🏆',
                'duration_seconds' => 4,
            ],
            [
                'name' => 'Classroom Ambience',
                'audio_url' => '/sounds/ambience.wav',
                'category' => 'Fun',
                'icon' => '🌿',
                'duration_seconds' => 30,
            ],
            [
                'name' => 'Extended Applause',
                'audio_url' => '/sounds/extended-applause.wav',
                'category' => 'Rewards',
                'icon' => '👏',
                'duration_seconds' => 12,
            ],
            [
                'name' => 'Extended Drum Roll',
                'audio_url' => '/sounds/extended-drum-roll.wav',
                'category' => 'Suspense',
                'icon' => '🥁',
                'duration_seconds' => 15,
            ],
            [
                'name' => 'Countdown Timer',
                'audio_url' => '/sounds/countdown.wav',
                'category' => 'Timers',
                'icon' => '⏱️',
                'duration_seconds' => 20,
            ],
            [
                'name' => 'Gentle Rain',
                'audio_url' => '/sounds/rain.wav',
                'category' => 'Fun',
                'icon' => '🌧️',
                'duration_seconds' => 30,
            ],
        ];

        foreach ($sounds as $sound) {
            Sound::create($sound);
        }
    }
}
