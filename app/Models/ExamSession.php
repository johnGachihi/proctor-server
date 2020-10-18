<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamSession extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'started_by'];

    public function started_by()
    {
        return $this->belongsTo('App\Models\Users', 'started_by');
    }
}
