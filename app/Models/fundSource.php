<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class fundSource extends Model
{
    protected $fillable = ['name'];

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }
}
