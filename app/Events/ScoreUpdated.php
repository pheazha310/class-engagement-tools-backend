<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ScoreUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $gameSessionId,
        public ?string $participantId,
        public string $participantName,
        public int $score,
        public int $pointsAwarded,
        public bool $isCorrect,
        public ?string $questionId,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('game-session.'.$this->gameSessionId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'ScoreUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'gameSessionId' => $this->gameSessionId,
            'participantId' => $this->participantId,
            'participantName' => $this->participantName,
            'score' => $this->score,
            'pointsAwarded' => $this->pointsAwarded,
            'isCorrect' => $this->isCorrect,
            'questionId' => $this->questionId,
        ];
    }
}
