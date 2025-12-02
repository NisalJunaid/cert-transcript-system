<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModuleResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'transcript_id',
        'name',
        'code',
        'marks',
        'grade',
        'gp',
        'cp',
        'position',
    ];

    protected $casts = [
        'gp' => 'integer',
        'cp' => 'float',
    ];

    public function transcript()
    {
        return $this->belongsTo(Transcript::class);
    }
}
