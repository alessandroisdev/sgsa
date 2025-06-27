<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    protected $table = 'appointments';
    protected $fillable = [
        'full_name', 'contact_info', 'document_info', 'sector_id', 'appointment_date', 'ticket_id'
    ];
    public $timestamps = true;

    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class);
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }
}