<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Khatma extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['group_id', 'khatma_no', 'people_group_no', 'description', 'hijri_date', 'juz_count', 'deleted_at'];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function assignments()
    {
        return $this->hasMany(KhatmaAssignment::class);
    }
}
