<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ZikrKhatmaAssignment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'zikr_khatma_assignments';

    protected $fillable = [
        'khatma_id',
        'user_id',
        'zikr_count',
        'created_by',
    ];

    public function khatma()
    {
        return $this->belongsTo(Khatma::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
