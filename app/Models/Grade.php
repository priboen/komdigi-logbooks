<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{

    protected $fillable = ['grade_url', 'internship_id'];

    public function internship()
    {
        return $this->belongsTo(Internship::class);
    }
}
