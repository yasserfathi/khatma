<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TilawaKhatmaAssignment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tilawa_khatma_assignments';

    protected $fillable = [
        'khatma_id',
        'user_id',
        'parts',
        'read'
    ];

    protected $casts = [
        'parts' => 'array',
        'read' => 'boolean'
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
