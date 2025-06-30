<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PkDocument extends Model
{
    protected $fillable = [
        'packing_list_id',
        'document_type',
        'file_name',
        'file_path',
        'mime_type',
        'file_size',
        'uploaded_by'
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PackingList::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
