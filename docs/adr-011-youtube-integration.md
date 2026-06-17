# ADR-011 — Integración YouTube Data API v3

**Estado:** Aceptado · Junio 2026  
**Contexto:** Fase D del roadmap (`docs/platform-roadmap.md`)

## Decisión

Integrar YouTube como plataforma oficial vía **Google OAuth 2.0** y **YouTube Data API v3**:

1. **Conexión** — `Platform::YouTube`, credenciales `client_id` / `client_secret` en cascada agencia → plataforma → `.env`.
2. **Activo** — `ConnectedAsset` tipo `youtube_channel` por canal administrado (`channels.list?mine=true`).
3. **Ingesta** — job diario `organic_youtube` que lista vídeos recientes del playlist de uploads y persiste `statistics`.
4. **Tokens** — refresh con `refresh_token` vía `PlatformTokenRefreshService` (mismo endpoint OAuth de Google).

## Alcance MVP YouTube

| Capacidad | MVP | Notas |
|-----------|-----|-------|
| Analytics orgánico | ✅ | Vídeos del canal + view/like/comment counts |
| Analytics paid | ⬜ | YouTube Ads — iteración posterior |
| Publicación | ⬜ | Upload API — iteración posterior |
| Dashboard / PDF | ✅ | Mismo contrato que Meta/TikTok/LinkedIn |

## Scopes OAuth iniciales

- `https://www.googleapis.com/auth/youtube.readonly` — listar canales y leer estadísticas de vídeos

## Riesgos

- Cuotas diarias de YouTube Data API; batching y límites por job.
- Métricas de alcance único no disponibles en API pública; se mapea `viewCount` → reach/impressions.
- Canales sin playlist de uploads requieren lookup adicional vía `channels.contentDetails`.

## Consecuencias

- `PlatformCatalog` marca YouTube como `available`.
- Pipeline diario a las 02:25 UTC en cola `ingestion-daily`.
- Fase D del roadmap queda cerrada con TikTok, LinkedIn y YouTube.
