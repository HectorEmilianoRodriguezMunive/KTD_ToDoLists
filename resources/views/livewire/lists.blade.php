<?php

use Livewire\Volt\Component;
use App\Models\ListTask;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $nameL = '';
    public array $MyLists = [];
    public int $idList;

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

        $this->deleteFields();
        session()->flash('success', 'A new list was created!');
        //send a message to frontend about new list
    }


    public function editList($id){
        $list = ListTask::findOrfail($id);
        $this->nameL = $list->name;
        $this->idList = $list->id;
    }

    public function saveEditList(){
        $this->validate(['nameL' => 'required|min:5|max:15|unique:list_tasks,name|string']);
        ListTask::find($this->idList)->update(['name' => $this->nameL]);
        session()->flash('success', 'The data of the list was updated successfully!');
        $this->deleteFields();

    }

    public function deleteFields(){
        $this->nameL = '';
    }

    public function deleteList($id){
       $this->idList = $id;
    }

    public function confirmDeleteList(){
       ListTask::findOrfail($this->idList)->delete();
       session()->flash('success', 'The task was deleted successfully!');
       $this->deleteFields();
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
            <flux:modal.trigger name="edit_list">
                <button wire:click = "editList({{ $mylist->id }})" wire:click = "deleteList({{ $mylist->id }})" class="bg-blue-500 text-white font-semibold px-4 py-2 rounded-2xl shadow-md transition duration-200 ease-in-out cursor-pointer w-full">
                    {{ $mylist->name }}
                </button>
            </flux:modal.trigger>
        @endforeach

        {{$mylists->links()}}
    </div>

     

    <flux:modal name="edit_list" class="max-w-5xl w-full">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Your list data</flux:heading>
                <flux:text class="mt-2">Check your list.</flux:text>
            </div>

            <flux:input label="Name"  wire:model="nameL"/>

            <div class = "flex gap-5 w-full">
                 <flux:modal.trigger name="details_list">
                     <flux:button type="submit" variant="primary" >See details</flux:button>                
                    </flux:modal.trigger>
                  <flux:button type="submit" variant="primary" wire:click="saveEditList">Save changes</flux:button>
                    <flux:modal.trigger name="delete_list">
                                            <button  class="bg-red-500 text-white font-semibold px-4 py-2 rounded-2xl shadow-md transition duration-200 ease-in-out cursor-pointer w-50">
                                                Delete list             
                                            </button>                 
                    </flux:modal.trigger>
            </div>

            <div class = "flex">
                    
            </div>
        </div>
    </flux:modal>

      <flux:modal name="delete_list" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Delete the list</flux:heading>
                <flux:text class="mt-2">¿Are you sure to delete this list?</flux:text>
            </div>

            <div class="flex">
                <flux:spacer />

                <flux:button type="submit" variant="primary" wire:click="confirmDeleteList">Yes</flux:button>
               
            </div>
        </div>
    </flux:modal>

     <flux:modal name="details_list" class="w-full">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Details of the list</flux:heading>
                <flux:text class="mt-2">Check the activities</flux:text>

            </div>

        </div>
    </flux:modal>

</div>
