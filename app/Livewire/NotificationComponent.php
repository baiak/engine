<?php

namespace App\Livewire;

use Livewire\Component;

class NotificationComponent extends Component
{
    public $notifications;

    public function mount()
    {
        $this->notifications = auth()->user()->unreadNotifications;
    }

    public function markAsRead($id)
    {
        $notification = auth()->user()->notifications()->find($id);

        if ($notification) {
            $notification->markAsRead();
        }

        $this->notifications = auth()->user()->unreadNotifications;

    }

    public function render()
    {
        return view('livewire.notification-component');
    }
}
