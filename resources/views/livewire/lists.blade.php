<?php

use Livewire\Volt\Component;
use App\Models\ListTask;
use App\Models\User;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $nameL     = '';
    public string $nameA     = '';
    public string $priority  = '';
    public int    $idList    = 0;
    public int    $idAct     = 0;
    public string $email = '';
    // Carga de datos para la vista
    public function with(): array
    {
        $user = Auth::user();

        // Intentar buscar la lista si hay un ID válido (> 0)
        $list = $this->idList
            ? ListTask::find($this->idList)
            : null;

        return [
            'mylists'           => $user->listTasks()->paginate(5, ['*'], 'page_mylists'),
            'sharedUserLists'   => $list
                ? $list->users()->paginate(5, ['*'], 'page_shared_users')
                : null,
            'actsList'          => $list
                ? $list->tasks()->paginate(5, ['*'], 'page_shared_lists')
                : null,
        ];
    }

    // Crear una nueva lista
    public function saveList(): void
    {
        $this->validate([
            'nameL' => 'required|min:5|max:15|unique:list_tasks,name|string',
        ]);

        $new = ListTask::create([ 'name' => $this->nameL ]);
        Auth::user()->listTasks()->attach($new->id);

        session()->flash('success_list_c', 'List created!');
        $this->deleteFields();
         
    }

    // Preparar edición de lista
    public function editList(int $idList): void
    {
        $l = ListTask::findOrFail($idList);
        $this->idList = $l->id;
        $this->nameL  = $l->name;
       
    }

    // Guardar cambios en la lista
    public function saveEditList(): void
    {
        $this->validate([
            'nameL' => 'required|min:5|max:15|unique:list_tasks,name|string',
        ]);

        ListTask::find($this->idList)->update([ 'name' => $this->nameL ]);
        session()->flash('success_list_u', 'List updated!');
        $this->deleteFields();
        
    }

    // Eliminar lista
    public function confirmDeleteList(): void
    {
        ListTask::findOrFail($this->idList)->delete();
        session()->flash('success_list_d', 'List deleted!');
        $this->deleteFields();
       
    }

    // Quitar campos del formulario
    protected function deleteFields(): void
    {
        $this->nameL    = '';
        $this->nameA    = '';
        $this->priority = '';
    }

    // Seleccionar lista actual
    public function getIDList(int $idList): void
    {
        $this->idList = $idList;
    }

    // Quitar un usuario compartido
    public function deleteSharedUser(int $userId): void
    {
        User::find($userId)->listTasks()->detach($this->idList);
        session()->flash('success_shared_lists', 'User removed!');
       
    }

    // Preparar edición/creación de actividad
    public function getIDAct(int $idAct): void
    {
        $this->idAct = $idAct;
        if ($idAct) {
            $task = Task::findOrFail($idAct);
            $this->nameA    = $task->name;
            $this->priority = $task->priority;
        }
    }

    // Eliminar actividad
    public function confirmDeleteAct(): void
    {
        Task::findOrFail($this->idAct)->delete();
        session()->flash('success_act_d', 'Activity deleted!');
        $this->deleteFields();
     
    }

    // Crear u​o actualizar actividad
    public function saveAct(): void
    {
        $this->validate([
            'nameA'    => 'required|min:5|max:15|string',
            'priority' => 'required|min:1|max:15|string', 
        ]);

        Task::updateOrCreate(
            ['id' => $this->idAct],
            [
                'name'         => $this->nameA,
                'priority'     => $this->priority,
                'list_task_id' => $this->idList,
            ]
        );

        session()->flash('success_act_c', $this->idAct ? 'Activity updated!' : 'Activity created!');
        $this->deleteFields();
       
    }

    public function shareList(){
                    $this->validate([
                    'email' => 'required|email|exists:users,email',
                ]);

                $user = User::where('email', $this->email)->first();

                // Evitar que se registre nuevamente a la tabla intermedia
                $alreadyShared = $user
                    ->listTasks()
                    ->wherePivot('list_task_id', $this->idList)
                    ->exists();

                if ($alreadyShared) {
                    // Mensaje de advertencia
                    session()->flash('warning_share_list', 'This user already has access to the list.');
                } else {
                    // Lo sincronizamos (evita duplicados) y mensaje de éxito
                    $user->listTasks()->syncWithoutDetaching($this->idList);
                    session()->flash('success_share_list', 'List shared!');
                } //sirve también para la cuestión de mensajes

    }


};
?>

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

    @if(session('success_list'))
        <div class  = "mt-5 px-5 py-5 rounded w-full text-white bg-blue-500">
            {{session('success_list_c')}}
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

                 @if(session('success_list_u'))
                    <div class  = "mt-5 px-5 py-5 rounded w-full text-white bg-blue-500">
                        {{session('success_list_u')}}
                    </div>
                @endif
        </div>
    </flux:modal>

    <flux:modal name="add_act" class="max-w-5xl w-full">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Your activity data</flux:heading>
                <flux:text class="mt-2">Check your activity.</flux:text>
            </div>

            <flux:input label="Name"  wire:model="nameA"/>
            <flux:input label="Priority"  wire:model="priority"/>
            
            <div class = "flex gap-5 w-full">
             
                  <flux:button type="submit" variant="primary" wire:click="saveAct">Save changes</flux:button>
                  
            </div>

            @if(session('success_act_c'))
                    <div class  = "mt-5 px-5 py-5 rounded w-full text-white bg-blue-500">
                        {{session('success_act_c')}}
                    </div>
                @endif

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
                @if(session('success_list_d'))
                    <div class  = "mt-5 px-5 py-5 rounded w-full text-white bg-blue-500">
                        {{session('success_list_d')}}
                    </div>
                @endif
            </div>
        </div>
    </flux:modal>

    <flux:modal name="delete_act" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Delete the activity</flux:heading>
                <flux:text class="mt-2">¿Are you sure to delete this activity?</flux:text>
            </div>

            <div class="flex">
                <flux:spacer />

                <flux:button type="submit" variant="primary" wire:click="confirmDeleteAct">Yes</flux:button>
               @if(session('success_act_d'))
                    <div class  = "mt-5 px-5 py-5 rounded w-full text-white bg-blue-500">
                        {{session('success_act_d')}}
                    </div>
                @endif
            </div>
        </div>
    </flux:modal>


     <flux:modal name="details_list" class="w-full">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Details of the list</flux:heading>
                <flux:text class="mt-2">Check the activities</flux:text>

                    @if($actsList)
                                                
                                <div class="overflow-x-auto mt-6 rounded-xl shadow-md">
                            <table class="min-w-full divide-y divide-gray-200 bg-white">
                                <flux:modal.trigger name="add_act">
                                                    <Button type="submit" variant="primary" class = "cursor-pointer text-black rounded px-2 py-2 bg-red-500">Create</Button>
                                </flux:modal.trigger>
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                            Name
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                            Priority
                                        </th>

                                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">



                                    @foreach ($actsList as $actL)
                                        <tr class="hover:bg-gray-50 transition">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-black">
                                                {{ $actL->name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-black">
                                                {{ $actL->priority }}
                                            </td>

                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-white">
                                                <flux:modal.trigger name="delete_act">
                                                     <Button type="submit" variant="primary" class = "cursor-pointer text-black rounded px-2 py-2 bg-red-500" wire:click="getIDAct({{$actL->id}})">Delete</Button>                                 
                                                </flux:modal.trigger>

                                                <flux:modal.trigger name="add_act">
                                                    <Button type="submit" variant="primary" class = "cursor-pointer text-black rounded px-2 py-2 bg-red-500" wire:click="getIDAct({{$actL->id}})">Edit</Button>
                                                </flux:modal.trigger>
                                            </td>
                                        </tr>
                                    @endforeach   
                                </tbody>
                            </table>
                            @if($actsList)
                                    {{$actsList->links()}}
                                    @endif
                        </div>

                    @endif

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

                     <div class = "mt-5">
                        <flux:field>
                        <flux:label>Share with other user:</flux:label>

                        <flux:description>Please, introduce his or her email.</flux:description>

                        <flux:input wire:model="email"/>

                        <flux:error name="email" />

                         <flux:button type="submit" variant="primary" wire:click="shareList">Share</flux:button>

                         @if(session('success_share_list'))

                            <div class  = "mt-5 px-5 py-5 rounded w-full text-white bg-blue-500">
                                {{session('success_share_list')}}
                            </div>

                         @endif  

                         @if(session('warning_share_list'))

                            <div class  = "mt-5 px-5 py-5 rounded w-full text-white bg-red-500">
                                {{session('warning_share_list')}}
                            </div>

                         @endif
                     </flux:field>
                     </div>
               

            </div>

        </div>
    </flux:modal>

</div>
