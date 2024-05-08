<?php

namespace App\Livewire;

use App\Events\SendRealTimeMessage;
use Livewire\Component;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Attributes\On; 


class RealTimeMessage extends Component
{
    use LivewireAlert;
    public string $message;

    public function triggerEvent()
    {
        event(new SendRealTimeMessage($this->message));
        $this->message = "";
    }

    #[On('echo:my-channel,SendRealTimeMessage')]
    public function handleRealTimeMessage($message):void
    {
        $this->alert('success',$message['message']);
    }

    public function render()
    {
        return view('livewire.real-time-message');
    }
}
