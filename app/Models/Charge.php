<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Charge extends Model
{
    use HasFactory;
    protected $table = 'subscription';

    protected $fillable = [
        'id',
        'charge_id',
        'shop',
        'created_at',
        'updated_at',       
    ];
}
