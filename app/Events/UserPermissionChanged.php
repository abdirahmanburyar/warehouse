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

class UserPermissionChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The user instance.
     *
     * @var \App\Models\User
     */
    public $user;

    /**
     * The permission that was changed.
     *
     * @var string
     */
    public $permission;

    /**
     * The type of change (added or removed).
     *
     * @var string
     */
    public $action;

    /**
     * The user who made the change.
     *
     * @var \App\Models\User|null
     */
    public $changedBy;

    /**
     * Create a new event instance.
     *
     * @param  \App\Models\User  $user
     * @param  string  $permission
     * @param  string  $action
     * @param  \App\Models\User|null  $changedBy
     * @return void
     */
    public function __construct(User $user, string $permission, string $action, ?User $changedBy = null)
    {
        $this->user = $user;
        $this->permission = $permission;
        $this->action = $action; // 'added' or 'removed'
        $this->changedBy = $changedBy;
    }
    
    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('user.' . $this->user->id);
    }
    
    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'permission.changed';
    }
    
    /**
     * Determine if this event should broadcast.
     *
     * @return bool
     */
    public function broadcastWhen()
    {
        return true;
    }
    
    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'user_id' => $this->user->id,
            'permission' => $this->permission,
            'action' => $this->action,
            'changed_by' => $this->changedBy ? $this->changedBy->name : 'System',
            'timestamp' => now()->toDateTimeString(),
        ];
    }
}
