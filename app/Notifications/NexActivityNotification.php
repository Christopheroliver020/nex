<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NexActivityNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $action,
        private readonly string $message,
        private readonly ?int $actorId = null,
        private readonly ?string $actorName = null,
        private readonly ?string $url = null,
        private readonly array $context = [],
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'action' => $this->action,
            'message' => $this->message,
            'actor_id' => $this->actorId,
            'actor_name' => $this->actorName,
            'url' => $this->url,
            'context' => $this->context,
        ];
    }
}
