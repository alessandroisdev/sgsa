<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketType extends Model
{
    protected $table = 'ticket_types';
    protected $fillable = ['name', 'priority'];

    public $timestamps = true;
}