<?php

namespace Modules\Notifications\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Connections\Models\ConnectedAsset;
use Modules\Notifications\Support\NotificationLocale;

class IngestionFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly ConnectedAsset $asset,
        public readonly string $jobClass,
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
        return NotificationLocale::mail($notifiable, fn () => (new MailMessage)
            ->subject(__('app.notifications.mail.ingestion_failed.subject'))
            ->greeting(__('app.notifications.mail.greeting', ['name' => $notifiable->name]))
            ->line(__('app.notifications.mail.ingestion_failed.line1'))
            ->line(__('app.notifications.mail.ingestion_failed.asset', ['asset' => $this->asset->name]))
            ->line(__('app.notifications.mail.ingestion_failed.job', ['job' => class_basename($this->jobClass)]))
            ->line(__('app.notifications.mail.ingestion_failed.error', ['error' => $this->errorMessage]))
            ->action(
                __('app.notifications.mail.ingestion_failed.action'),
                url('/workspaces/'.$this->asset->connection?->workspace_id),
            ));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'asset_id' => $this->asset->id,
            'asset_name' => $this->asset->name,
            'workspace_id' => $this->asset->connection?->workspace_id,
            'job_class' => class_basename($this->jobClass),
            'error_message' => $this->errorMessage,
        ];
    }
}
