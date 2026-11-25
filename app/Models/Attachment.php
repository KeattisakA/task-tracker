<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

#[Table('attachments')]
#[Fillable(['task_id', 'path', 'original_name', 'size'])]
class Attachment extends Model
{
    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
