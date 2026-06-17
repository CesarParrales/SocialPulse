<?php

namespace Modules\Notifications\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Connections\Models\PlatformConnection;
use Modules\Notifications\Support\NotificationLocale;

class ConnectionTokenExpiringNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly PlatformConnection $platformConnection,
    ) {
        $this->onQueue('notifications');
    }

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $platform = $this->platformConnection->platform->value;
        $workspaceId = $this->platformConnection->workspace_id;
        $expiresAt = $this->platformConnection->token_expires_at?->toDateString() ?? '';

        return NotificationLocale::mail($notifiable, fn () => (new MailMessage)
            ->subject(__('app.notifications.mail.token_expiring.subject'))
            ->greeting(__('app.notifications.mail.greeting', ['name' => $notifiable->name]))
            ->line(__('app.notifications.mail.token_expiring.line1', ['platform' => ucfirst($platform)]))
            ->line(__('app.notifications.mail.token_expiring.line2', ['date' => $expiresAt]))
            ->action(
                __('app.notifications.mail.token_expiring.action'),
                url('/workspaces/'.$workspaceId.'/connections'),
            ));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'connection_id' => $this->platformConnection->id,
            'platform' => $this->platformConnection->platform->value,
            'workspace_id' => $this->platformConnection->workspace_id,
            'expires_at' => $this->platformConnection->token_expires_at?->toDateString(),
        ];
    }
}
