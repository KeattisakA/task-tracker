<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use App\Models\Task;


#[Table('comments')]
#[Fillable(['task_id','body'])]

class Comment extends Model
{
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

}
