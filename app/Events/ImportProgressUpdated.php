<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ImportProgressUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $importId;
    public int $progress;
    public int $total;

    public function __construct(string $importId, int $progress, int $total)
    {
        $this->importId = $importId;
        $this->progress = $progress;
        $this->total = $total;
    }

    public function broadcastOn()
    {
        return new Channel("import-progress.{$this->importId}");
    }

    public function broadcastAs()
    {
        return 'ImportProgressUpdated';
    }
}
