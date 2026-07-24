<?php

namespace App\Events;

use App\Models\Sound;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SoundPlayed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $soundId,
        public string $soundName,
        public string $audioUrl,
        public ?string $category,
        public ?string $icon,
        public ?int $durationSeconds,
    ) {}

    public static function fromModel(Sound $sound): self
    {
        return new self(
            soundId: $sound->id,
            soundName: $sound->name,
            audioUrl: $sound->audio_url,
            category: $sound->category,
            icon: $sound->icon,
            durationSeconds: $sound->duration_seconds,
        );
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('soundboard'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'SoundPlayed';
    }

    public function broadcastWith(): array
    {
        return [
            'soundId' => $this->soundId,
            'soundName' => $this->soundName,
            'audioUrl' => $this->audioUrl,
            'category' => $this->category,
            'icon' => $this->icon,
            'durationSeconds' => $this->durationSeconds,
            'playedAt' => now()->toISOString(),
        ];
    }
}
