<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Table('projects')]
#[Fillable(['name','description'])]
class Project extends Model
{
    
}
