<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'certificate_serial_number',
        'name',
        'national_id',
        'batch_no',
        'student_identifier',
        'program',
        'level',
    ];

    public function transcripts()
    {
        return $this->hasMany(Transcript::class);
    }
}
