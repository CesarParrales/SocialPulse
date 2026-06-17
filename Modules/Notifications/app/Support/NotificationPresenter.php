<?php

namespace Modules\Notifications\Support;

use Illuminate\Notifications\DatabaseNotification;
use Modules\Notifications\Notifications\ConnectionTokenExpiringNotification;
use Modules\Notifications\Notifications\ConnectionTokenRefreshFailedNotification;
use Modules\Notifications\Notifications\IngestionFailedNotification;

class NotificationPresenter
{
    /**
     * @return array<string, mixed>
     */
    public static function from(DatabaseNotification $notification): array
    {
        $data = $notification->data;
        $typeKey = self::typeKey($notification->type);

        return [
            'id' => $notification->id,
            'type' => $typeKey,
            'title' => __("app.notifications.types.{$typeKey}.title"),
            'message' => self::message($typeKey, $data),
            'action_url' => self::actionUrl($typeKey, $data),
            'action_label' => __("app.notifications.types.{$typeKey}.action"),
            'read_at' => $notification->read_at?->toIso8601String(),
            'created_at' => $notification->created_at?->toIso8601String(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function message(string $typeKey, array $data): string
    {
        return match ($typeKey) {
            'ingestion_failed' => __('app.notifications.types.ingestion_failed.message', [
                'asset' => $data['asset_name'] ?? '',
                'error' => $data['error_message'] ?? '',
            ]),
            'token_refresh_failed' => __('app.notifications.types.token_refresh_failed.message', [
                'platform' => ucfirst($data['platform'] ?? ''),
                'error' => $data['error_message'] ?? '',
            ]),
            'token_expiring' => __('app.notifications.types.token_expiring.message', [
                'platform' => ucfirst($data['platform'] ?? ''),
                'expires_at' => $data['expires_at'] ?? '',
            ]),
            default => '',
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function actionUrl(string $typeKey, array $data): ?string
    {
        $workspaceId = $data['workspace_id'] ?? null;

        if ($workspaceId === null) {
            return null;
        }

        return match ($typeKey) {
            'ingestion_failed' => url("/workspaces/{$workspaceId}"),
            'token_refresh_failed', 'token_expiring' => url("/workspaces/{$workspaceId}/connections"),
            default => null,
        };
    }

    private static function typeKey(string $notificationClass): string
    {
        return match ($notificationClass) {
            IngestionFailedNotification::class => 'ingestion_failed',
            ConnectionTokenRefreshFailedNotification::class => 'token_refresh_failed',
            ConnectionTokenExpiringNotification::class => 'token_expiring',
            default => 'unknown',
        };
    }
}
