<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use App\Models\Task;

#[Table('attachments')]
#[Fillable(['task_id', 'path', 'original_name', 'size'])]
class Attachment extends Model
{
    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
