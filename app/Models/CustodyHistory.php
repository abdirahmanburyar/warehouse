<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Asset;
use App\Models\User;

class CustodyHistory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'custody_histories';

    const ACTION_CREATED = 'created';
    const ACTION_UPDATED = 'updated';
    const ACTION_ASSIGNED = 'assigned';
    const ACTION_RETURNED = 'returned';

    protected $fillable = [
        'asset_id',
        'custodian',
        'assigned_by',
        'assigned_at',
        'returned_at',
        'assignment_notes',
        'return_notes',
        'status',
        'status_notes'
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
