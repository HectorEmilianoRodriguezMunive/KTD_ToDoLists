<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ListTask extends Model
{   
    
    use HasFactory;
    protected $fillable = [
        'name',
    ];

    
    public function tasks(): HasMany{
        return $this->hasMany(Task::class);
    }

    public function users(): BelongsToMany{
        return $this->belongsToMany(User::class);
    }

}
