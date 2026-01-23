<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Khatma extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['group_id', 'group_reading_id', 'khatma_no', 'description', 'hijri_date', 'juz_count', 'deleted_at', 'created_by'];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function groupReading()
    {
        return $this->belongsTo(GroupReading::class);
    }

    public function tilawaAssignments()
    {
        return $this->hasMany(TilawaKhatmaAssignment::class);
    }

    public function zikrAssignments()
    {
        return $this->hasMany(ZikrKhatmaAssignment::class);
    }
}
