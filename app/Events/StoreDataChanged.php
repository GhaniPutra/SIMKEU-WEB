<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class StoreDataChanged implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private string $eventId;

    /**
     * @param  list<string>  $resources
     */
    public function __construct(
        public int $storeId,
        public array $resources,
    ) {
        $this->eventId = (string) Str::uuid();
    }

    public function broadcastOn(): array
    {
        return array_map(
            fn (string $resource) => new PrivateChannel("store.{$this->storeId}.{$resource}"),
            $this->resources,
        );
    }

    public function broadcastAs(): string
    {
        return 'store.data.changed';
    }

    public function broadcastWith(): array
    {
        return ['id' => $this->eventId];
    }
}
