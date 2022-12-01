<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    use HasFactory;

    protected $table = 'collections';

    protected $fillable = [
        'id',
        'file',
        'path',
        'type',
        'errors',
        'shop',
        'created_at',
        'updated_at',       
    ];

    protected $casts = [
        'created_at' => 'datetime:D, d M y H:i:s',
    ];
    protected $appends = [
        'created_at_human_readble',
    ];
    public function getCreatedAtHumanReadbleAttribute () {
    return $this->created_at->diffForHumans();
    }
}
