<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Table('projects')]
#[Fillable(['owner_id', 'name', 'description'])]

class Project extends Model
{
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}
