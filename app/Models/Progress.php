<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Progress extends Model
{
    protected $table = 'progress';
    protected $fillable = ['internship_id', 'meeting', 'date', 'file_url', 'name'];

    public function internship()
    {
        return $this->belongsTo(Internship::class);
    }
}
