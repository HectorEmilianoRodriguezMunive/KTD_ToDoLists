<?php

use Livewire\Volt\Component;
use App\Models\ListTask;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $nameL = '';
    public string $search = '';
    public int $idList = 0;


    public function with():array{ 
            return [
            'mylists' => auth()->user()->listTasks()->paginate(5, ['*'], 'page_mylists'),
            'sharedUserLists' => $this->idList
                ? ListTask::find($this->idList)?->users()->paginate(5, ['*'], 'page_shared_users')
                : null,
        ];
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
        $this->dispatch('close-modal', modalName: 'edit_list');
    }


    public function editList($idList):void{
        $list = ListTask::findOrfail($idList);
        $this->nameL = $list->name;
        $this->idList = $list->id;
    }

    public function saveEditList():void{
        $this->validate(['nameL' => 'required|min:5|max:15|unique:list_tasks,name|string']);
        ListTask::find($this->idList)->update(['name' => $this->nameL]);
        session()->flash('success', 'The data of the list was updated successfully!');
        $this->deleteFields();
        $this->dispatch('close-modal', modalName: 'edit_list'); 
    }

    public function deleteFields():void{
        $this->nameL = '';
    }

    public function getIDList($idList):void{
       $this->idList = $idList;
    }

    public function confirmDeleteList():void{
       ListTask::findOrfail($this->idList)->delete();
       session()->flash('success', 'The task was deleted successfully!');
       $this->deleteFields();
       $this->dispatch('close-modal', modalName: 'delete_list');
    }

    public function deleteSharedUser($id){
        $user = User::find($id);
        $user->listTasks()->detach($this->idList);
        session()->flash('success_shared_lists', 'The user was removed successfully!');
        
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
                <button wire:click = "editList({{ $mylist->id }})" wire:click = "getIDList({{ $mylist->id }})"  class="bg-blue-500 text-white font-semibold px-4 py-2 rounded-2xl shadow-md transition duration-200 ease-in-out cursor-pointer w-full">
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

                    @if(session('success_shared_lists'))
                        <div class  = "mt-5 px-5 py-5 rounded w-full text-white bg-blue-500">
                            {{session('success_shared_lists')}}
                        </div>
                    @endif
                <flux:text class="mt-2">Collaborators</flux:text>
                
               @if($sharedUserLists)
                   <div class="overflow-x-auto mt-6 rounded-xl shadow-md">
                    <table class="min-w-full divide-y divide-gray-200 bg-white">
                        <thead class="bg-gray-100">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Name
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Email
                                </th>

                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">



                            @foreach ($sharedUserLists as $user)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-black">
                                        {{ $user->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-black">
                                        {{ $user->email }}
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-white">
                                         <Button type="submit" variant="primary" class = "cursor-pointer text-black rounded px-2 py-2 bg-red-500" wire:click="deleteSharedUser({{$user->id}})">Remove</Button>
                                    </td>
                                </tr>
                            @endforeach   
                        </tbody>
                    </table>
                     @if($sharedUserLists)
                             {{$sharedUserLists->links()}}
                            @endif
                </div>
               @endif

            </div>

        </div>
    </flux:modal>

</div>
