<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthlyInventoryReport extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'month_year',
        'beginning_balance',
        'stock_received',
        'stock_issued',
        'negative_adjustment',
        'positive_adjustment',
        'closing_balance',
        'generated_at',
    ];
}