<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ListTask extends Model
{
    use HasFactory;
    public function tasks(): HasMany{
        return $this->hasMany(Task::class);
    }

    public function users(){
        $this->belongsToMany(ListTask::class);
    }

}
