<?php

namespace Modules\Notifications\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Connections\Models\PlatformConnection;
use Modules\Notifications\Support\NotificationLocale;

class ConnectionTokenRefreshFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly PlatformConnection $platformConnection,
        public readonly string $errorMessage,
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

        return NotificationLocale::mail($notifiable, fn () => (new MailMessage)
            ->subject(__('app.notifications.mail.token_refresh_failed.subject'))
            ->greeting(__('app.notifications.mail.greeting', ['name' => $notifiable->name]))
            ->line(__('app.notifications.mail.token_refresh_failed.line1', ['platform' => ucfirst($platform)]))
            ->line(__('app.notifications.mail.token_refresh_failed.line2'))
            ->line(__('app.notifications.mail.token_refresh_failed.error', ['error' => $this->errorMessage]))
            ->action(
                __('app.notifications.mail.token_refresh_failed.action'),
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
            'error_message' => $this->errorMessage,
        ];
    }
}
