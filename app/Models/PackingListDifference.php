<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackingListDifference extends Model
{
    use HasFactory;

    protected $table = 'packing_list_differences';

    protected $fillable = [
        'packing_listitem_id',
        'product_id',
        'quantity',
        'finalized',
        'status'
    ];

    public function packingListItem()
    {
        return $this->belongsTo(PackingListItem::class, 'packing_listitem_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    
}
