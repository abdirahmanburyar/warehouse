<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReceivedBackorder extends Model
{
    use HasFactory;

    protected $fillable = [
        'received_backorder_number',
        'product_id',
        'received_by',
        'barcode',
        'expire_date',
        'batch_number',
        'uom',
        'received_at',
        'quantity',
        'status',
        'type',
        'warehouse_id',
        'location',
        'facility',
        'warehouse',
        'unit_cost',
        'total_cost',
        'note',
        'reviewed_by',
        'reviewed_at',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'attachments',
        // Additional fields for back order integration
        'back_order_id',
        'packing_list_id',
        'packing_list_number',
        'purchase_order_id',
        'purchase_order_number',
        'supplier_id',
        'supplier_name',
    ];

    protected $casts = [
        'expire_date' => 'date',
        'received_at' => 'date',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'attachments' => 'array',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    /**
     * Get the product that owns the received backorder.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user who received the backorder.
     */
    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /**
     * Get the user who reviewed the backorder.
     */
    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Get the user who approved the backorder.
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the user who rejected the backorder.
     */
    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    /**
     * Generate a unique received backorder number
     */
    public static function generateReceivedBackorderNumber(): string
    {
        $prefix = 'RB';
        $year = date('Y');
        $month = date('m');
        
        // Get the last received backorder for this year/month
        $lastReceivedBackorder = self::where('received_backorder_number', 'like', "{$prefix}-{$year}{$month}-%")
            ->orderBy('received_backorder_number', 'desc')
            ->first();
        
        if ($lastReceivedBackorder) {
            // Extract the sequence number and increment
            $parts = explode('-', $lastReceivedBackorder->received_backorder_number);
            $sequence = (int) end($parts) + 1;
        } else {
            $sequence = 1;
        }
        
        return sprintf('%s-%s%s-%04d', $prefix, $year, $month, $sequence);
    }

    /**
     * Boot method to auto-generate received_backorder_number
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($receivedBackorder) {
            if (empty($receivedBackorder->received_backorder_number)) {
                $receivedBackorder->received_backorder_number = self::generateReceivedBackorderNumber();
            }
        });
    }
}
