<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transfer extends Model
{
    protected $table = 'transfers';
    protected $fillable = ['ticket_id', 'from_sector_id', 'to_sector_id', 'user_id', 'timestamp'];
    public $timestamps = false;

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function fromSector(): BelongsTo
    {
        return $this->belongsTo(Sector::class, 'from_sector_id');
    }

    public function toSector(): BelongsTo
    {
        return $this->belongsTo(Sector::class, 'to_sector_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}