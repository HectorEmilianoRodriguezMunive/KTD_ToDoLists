<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ListTask;
class Task extends Model
{
    use HasFactory;

    public function list(){
        return $this->belongsTo(ListTask::class);
    }

}
