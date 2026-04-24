<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class Area extends Model implements \OwenIt\Auditing\Contracts\Auditable
{
    use \OwenIt\Auditing\Auditable, HasUuids, SoftDeletes;

    protected $guarded = [];

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function totems()
    {
        return $this->hasMany(Totem::class);
    }

    public function tvs()
    {
        return $this->hasMany(Tv::class);
    }

    public function counters()
    {
        return $this->hasMany(Counter::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
