<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

use App\Models\Project;
use App\Models\Comment;

#[Table('tasks')]
#[Fillable(['project_id', 'assignee_id', 'title', 'description', 'status'])]
class Task extends Model
{
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
