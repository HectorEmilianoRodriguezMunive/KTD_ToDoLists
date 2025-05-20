<?php

use Livewire\Volt\Component;
use App\Models\ListTask;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $nameL = '';
    public array $MyLists = [];

    public function with():array{ 
        return ['mylists'  => auth()->user()->listTasks()->paginate(5)];
    }



    public function saveList():void{ //method for storage an new List
        $this->validate(['nameL' => 'required|min:5|max:15|unique:list_tasks,name|string']);
        $ListTask = ListTask::create([
            'name' => $this->nameL
        ]);
        
        $user = Auth::user();
        $user->listTasks()->attach($ListTask->id);
        //Define the relationship with an user and list.


        session()->flash('success', 'A new List was created!');
        //send a message to frontend about new list
    }


}; ?>

<div>
    
    <div>
        <flux:field>
            <flux:label>New List</flux:label>

            <flux:description>Name: </flux:description>

            <flux:input wire:model="nameL" placeholder="MyNewList"/>

            <flux:error name="nameL" />
            <flux:button wire:click="saveList">Create</flux:button>

        </flux:field>
    </div>

    @if(session('success'))
        <div class  = "mt-5 px-5 py-5 rounded w-full text-white bg-blue-500">
            {{session('success')}}
        </div>
    @endif
    
    <div class  = "mt-5">
        <flux:label>My Lists</flux:label>
    </div>

    

    <div class="mt-10 flex flex-wrap gap-4 w-full flex-col items-center">
    @foreach($mylists as $mylist)
        <button class="bg-blue-500 text-white font-semibold px-4 py-2 rounded-2xl shadow-md transition duration-200 ease-in-out cursor-pointer w-full">
            {{ $mylist->name }}
        </button>
    @endforeach

    {{$mylists->links()}}
    </div>


</div>
