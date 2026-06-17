<?php

namespace Modules\Notifications\Support;

use Illuminate\Notifications\Messages\MailMessage;

class NotificationLocale
{
    /**
     * @param  callable(): MailMessage  $callback
     */
    public static function mail(object $notifiable, callable $callback): MailMessage
    {
        $locale = $notifiable->locale ?? 'es';
        $previous = app()->getLocale();
        app()->setLocale($locale);

        try {
            return $callback();
        } finally {
            app()->setLocale($previous);
        }
    }
}
