<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InternshipMember extends Model
{
    protected $fillable = [
        'internship_id',
        'student_id'
    ];

}
