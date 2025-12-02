<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transcript extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'course_id',
        'cgpa',
        'pass_with_distinction',
        'deans_award',
        'completed_date',
    ];

    protected $casts = [
        'completed_date' => 'date',
        'pass_with_distinction' => 'boolean',
        'deans_award' => 'boolean',
        'cgpa' => 'decimal:2',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function moduleResults()
    {
        return $this->hasMany(ModuleResult::class);
    }
}
