<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class TestBroadcast implements ShouldBroadcastNow
{
    use Dispatchable;

    public string $message;
    public string $timestamp;

    public function __construct(string $message)
    {
        $this->message = $message;
        $this->timestamp = now()->toDateTimeString();
    }

    public function broadcastOn(): array
    {
        return [new Channel('test-channel')];
    }
}
