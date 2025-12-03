<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'shortcode',
        'name',
        'level',
    ];

    public function transcripts()
    {
        return $this->hasMany(Transcript::class);
    }
}
