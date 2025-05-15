<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SubLocation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'asset_location_id'
    ];

    public function location()
    {
        return $this->belongsTo(AssetLocation::class, 'asset_location_id');
    }

    public function assets()
    {
        return $this->hasMany(Asset::class, 'sub_location_id');
    }
}
