<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ManualAttendanceNotification extends Notification
{
    use Queueable;

    protected $action;
    protected $adminName;
    protected $note;

    /**
     * Create a new notification instance.
     *
     * @param string $action "clocked in" or "clocked out"
     * @param string $adminName The name of the admin who did it
     * @param string $note The reason provided by the admin
     */
    public function __construct(string $action, string $adminName, string $note)
    {
        $this->action = $action;
        $this->adminName = $adminName;
        $this->note = $note;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // Use Laravel's built-in database notification system
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => "Your attendance has been manually {$this->action} by Administration ({$this->adminName}).",
            'action' => $this->action,
            'admin_name' => $this->adminName,
            'note' => $this->note,
        ];
    }
}
