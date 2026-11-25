<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Table('comments')]
#[Fillable(['task_id', 'body'])]

class Comment extends Model
{
    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
