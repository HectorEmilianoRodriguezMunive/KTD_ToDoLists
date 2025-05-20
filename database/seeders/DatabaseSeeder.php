<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Task;
use App\Models\ListTask;
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
       $users = User::factory(10)->create();

       $lists = ListTask::factory(5)->create();
       $tasks = Task::factory(5)->create();

        /*User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);*/

        foreach ($tasks as $task) {
            $randomList = $lists->random(); 
            $task->list_task_id = $randomList->id;
            $task->save();
        }


       foreach ($users as $user) {
        $user->listTasks()->attach(
            $lists->random(rand(1, 3))->pluck('id')->toArray()
        );
    }
        

    }
}
