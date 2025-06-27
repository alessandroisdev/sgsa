<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Counter extends Model
{
    protected $table = 'counters';
    protected $fillable = ['sector_id', 'name', 'description'];

    public $timestamps = true;

    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class);
    }
}