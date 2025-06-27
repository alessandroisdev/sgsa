<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'users';
    protected $fillable = ['username', 'password_hash', 'role'];
    public $timestamps = true;
    protected $hidden = ['password_hash'];
}