<?php

use Livewire\Volt\Component;

new class extends Component {
    
    public string $nameL = '';


    

}; ?>

<div>
    
    <div>
        <flux:field>
            <flux:label>New List</flux:label>

            <flux:description>Name: </flux:description>

            <flux:input />

            <flux:error name="username" />
            <flux:button>Create</flux:button>

        </flux:field>
    </div>

</div>
