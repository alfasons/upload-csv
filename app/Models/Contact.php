<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'number',
        'extra',
        'address',
        'city',
        'state',
        'zip',
        'owner_first_name',
        'owner_last_name',
        'tags',
        'subscribed',
        'last_activity',
        'contact_lists_id',
    ];
}
