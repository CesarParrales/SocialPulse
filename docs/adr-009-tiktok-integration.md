# ADR-009 — Integración TikTok Business API

**Estado:** Aceptado · Junio 2026  
**Contexto:** Fase D del roadmap (`docs/platform-roadmap.md`)

## Decisión

Integrar TikTok como plataforma oficial vía **TikTok for Developers OAuth 2.0** y endpoints `open.tiktokapis.com`:

1. **Conexión** — `Platform::TikTok`, credenciales `client_key` / `client_secret` en cascada agencia → plataforma → `.env`.
2. **Activo** — un `ConnectedAsset` de tipo `tiktok_account` por cuenta autorizada (`open_id`).
3. **Ingesta** — job diario `organic_tiktok` que lista vídeos (`/v2/video/list/`) y persiste en `organic_posts` + `organic_post_metric_entries`.
4. **Tokens** — refresh automático con `refresh_token` vía `PlatformTokenRefreshService`.

## Alcance MVP TikTok

| Capacidad | MVP | Notas |
|-----------|-----|-------|
| Analytics orgánico | ✅ | `video.list` + métricas embebidas |
| Analytics paid | ⬜ | TikTok Marketing API — iteración posterior |
| Publicación | ⬜ | Content Posting API — iteración posterior |
| Dashboard / PDF | ✅ | `ReportChannelInsightsService` + métricas normalizadas (`views` → alcance) |

## Scopes OAuth iniciales

- `user.info.basic` — perfil y `open_id`
- `video.list` — listado de vídeos publicados

Ampliar scopes solo cuando se implementen paid o publishing.

## Riesgos

- Aprobación de app TikTok puede tardar; usar sandbox en staging.
- Métricas disponibles dependen del tier de la app y permisos aprobados.
- Un OAuth autoriza una cuenta; Business Center multi-cuenta queda fuera del MVP.

## Consecuencias

- `PlatformCatalog` marca TikTok como `available`.
- Nuevo pipeline en cola `ingestion-daily` a las 02:15 UTC.
- LinkedIn y YouTube siguen el mismo patrón en iteraciones futuras.
