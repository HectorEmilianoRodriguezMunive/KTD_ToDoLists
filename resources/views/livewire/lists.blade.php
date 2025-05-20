<?php

use Livewire\Volt\Component;
use App\Models\ListTask;

new class extends Component {
    
    public string $nameL = '';

    public function saveList(){
        $this->validate(['nameL' => 'required|min:5|max:15|unique:lists_tasks,name']);
        ListTask::create([
            'name' => $this->nameL
        ]);

        session()->flash('success', 'A new List was created!');
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
        <div class  = "px-5 py-5 rounded w-6 text-white bg-green-600">
            {{session('success')}}
        </div>
    @endif
    
    <div class = "mt-10">
        <flux:label>My Lists</flux:label>

    </div>

</div>
