<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sector extends Model
{
    protected $table = 'sectors';
    protected $fillable = ['name', 'description'];
    public $timestamps = true;

    public function counters(): HasMany
    {
        return $this->hasMany(Counter::class);
    }
}