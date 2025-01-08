<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Internship extends Model
{
    use HasFactory;
    protected $fillable = [
        'leader_id',
        'project_id',
        'supervisor_id',
        'status',
        'campus',
        'letter_url',
        'member_photo_url',
    ];

    public function leader()
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'internship_members', 'internship_id', 'student_id');
    }

    public function progress()
    {
        return $this->hasMany(Progress::class);
    }
}
