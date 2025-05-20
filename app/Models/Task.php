<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ListTask;
class Task extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'priority'
    ];

    public function list(){
        return $this->belongsTo(ListTask::class);
    }

}
