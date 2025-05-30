<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GlobalPermissionChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The user instance.
     *
     * @var \App\Models\User
     */
    public $user;

    /**
     * Create a new event instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
        \Illuminate\Support\Facades\Log::info('GlobalPermissionChanged: Event constructed', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'timestamp' => now()->toDateTimeString()
        ]);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        \Illuminate\Support\Facades\Log::info('GlobalPermissionChanged: broadcastOn called');
        // Use a public channel with a simple name
        return new Channel('app-events');
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        \Illuminate\Support\Facades\Log::info('GlobalPermissionChanged: broadcastAs called');
        return 'permissions-changed';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        \Illuminate\Support\Facades\Log::info('GlobalPermissionChanged: broadcastWith called');
        $data = [
            'user_id' => $this->user->id,
            'timestamp' => now()->toDateTimeString(),
        ];
        \Illuminate\Support\Facades\Log::info('GlobalPermissionChanged: Broadcasting data', $data);
        return $data;
    }
}
