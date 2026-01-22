<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KhatmaAssignment extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'parts' => 'array',
        'read' => 'boolean',
    ];

    public function khatma()
    {
        return $this->belongsTo(Khatma::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
