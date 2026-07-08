<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VoteUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $pollId,
        public int $totalVotes,
        public array $results,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('poll.' . $this->pollId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'VoteUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'pollId' => $this->pollId,
            'totalVotes' => $this->totalVotes,
            'results' => $this->results,
        ];
    }
}
