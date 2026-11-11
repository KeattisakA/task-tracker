<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use App\Models\Task;

#[Table('projects')]
#[Fillable(['name','description'])]

class Project extends Model
{
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

}
