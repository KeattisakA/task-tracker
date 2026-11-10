<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Table('tasks')]
#[Fillable(['title','description'])]
class Task extends Model
{
    
}
