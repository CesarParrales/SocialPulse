# ADR-010 — Integración LinkedIn Company Page API

**Estado:** Aceptado · Junio 2026  
**Contexto:** Fase D del roadmap (`docs/platform-roadmap.md`)

## Decisión

Integrar LinkedIn como plataforma oficial vía **OAuth 2.0** y APIs REST de LinkedIn:

1. **Conexión** — `Platform::LinkedIn`, credenciales `client_id` / `client_secret` en cascada agencia → plataforma → `.env`.
2. **Activo** — `ConnectedAsset` tipo `linkedin_page` por organización administrada (`organizationAcls`).
3. **Ingesta** — job diario `organic_linkedin` que lista posts (`/rest/posts`) y persiste métricas de `totalShareStatistics`.
4. **Tokens** — refresh con `refresh_token` vía `PlatformTokenRefreshService`.

## Alcance MVP LinkedIn

| Capacidad | MVP | Notas |
|-----------|-----|-------|
| Analytics orgánico | ✅ | Posts de página + estadísticas embebidas |
| Analytics paid | ⬜ | Campaign Manager — iteración posterior |
| Publicación | ⬜ | Share API — iteración posterior |
| Dashboard / PDF | ✅ | Mismo contrato que Meta/TikTok |

## Scopes OAuth iniciales

- `r_organization_admin` — listar páginas administradas
- `r_organization_social` — leer posts orgánicos

## Riesgos

- Productos LinkedIn deprecan endpoints con frecuencia; versionar con `LinkedIn-Version`.
- Métricas completas pueden requerir permisos adicionales según tier de la app.
- Multi-página vía Business Manager: MVP soporta selección manual de activos.

## Consecuencias

- `PlatformCatalog` marca LinkedIn como `available`.
- Pipeline diario a las 02:20 UTC en cola `ingestion-daily`.
- YouTube sigue el mismo patrón en la siguiente iteración.
