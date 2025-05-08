<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackingListDifference extends Model
{
    use HasFactory;

    protected $table = 'packing_list_differences';

    protected $fillable = [
        'packing_list_id',
        'product_id',
        'quantity',
        'status'
    ];

    protected $casts = [
        'quantity' => 'integer'
    ];

    public function packingList()
    {
        return $this->belongsTo(PackingList::class, 'packing_list_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    
}
