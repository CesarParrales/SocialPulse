<x-mail::message>
# Invitación a {{ $invitation->agency->name }}

Has sido invitado a unirte a **SocialPulse** como **{{ str_replace('_', ' ', $invitation->role->value) }}**.

<x-mail::button :url="$invitation->acceptUrl()">
Aceptar invitación
</x-mail::button>

Este enlace expira el {{ $invitation->expires_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}.

Si no esperabas este correo, puedes ignorarlo.

Saludos,<br>
{{ config('app.name') }}
</x-mail::message>
