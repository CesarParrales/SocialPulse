# Política de versiones del stack

```
last_updated: 2026-06
revisar_cada: 90 días
```

> Las skills de la suite **no fijan** Laravel 11, Next 14 u otras versiones antiguas como
> verdad absoluta. El stack del **proyecto** manda; si no hay proyecto, usar **última
> versión estable** y declararla.

---

## Principio

Usar siempre las **últimas versiones estables** de:

- Lenguajes (PHP, TypeScript, JavaScript)
- Frameworks (Laravel, Next.js, NestJS, React)
- Paquetes y complementos (Filament, Sanctum, Pest, nwidart/laravel-modules, etc.)
- Imágenes Docker, runtimes CI (`php-version`, `node-version`)

Salvo que el repo o el cliente exijan otra cosa documentada.

---

## Orden de precedencia

```
1. Manifests del repo     → composer.json / composer.lock, package.json, lockfiles
2. Memoria L2 + PRD         → .cursor/project-memory.md, context.md, socialpulse-prd.md
3. Greenfield / sin repo    → última estable oficial + declarar [SUPOSICIÓN] en el chat
4. Ejemplos en references/  → ilustrativos; NO copiar versión sin verificar manifest
```

---

## Al iniciar cualquier tarea técnica

```
□ Leer composer.json y package.json (requiere PHP/Laravel/Node/React)
□ Si project-memory tiene snapshot de stack → respetarlo
□ Si hay conflicto skill vs manifest → gana el manifest; registrar gap en LEARNINGS.md
□ Proponer upgrade solo si el usuario lo pide o hay CVE; no forzar major bump silencioso
```

---

## Defaults greenfield (jun 2026 — verificar antes de usar)

| Capa | Orientación | Verificar en |
|------|-------------|--------------|
| PHP | Última estable soportada por Laravel del proyecto | getcomposer.org, laravel.com/docs |
| Laravel | **Última major estable** (ej. 12+, 13+ — no asumir 11) | laravel.com/docs/releases |
| Node | LTS activa o Current según política del equipo | nodejs.org |
| Next.js | Última estable con App Router | nextjs.org/docs |
| React | Par de versiones que exija Next/Vite del proyecto | package.json |
| Filament | Última v3/v4 según compat Laravel | filamentphp.com |
| Pest | Incluido en Laravel actual o última vía Composer | pestphp.com |

**Ejemplo real:** SocialToolsDev usa Laravel 13 + PHP 8.3+ — no downgrade a 11 por defaults viejos de skills.

---

## En código y documentación generada

```
✓ "bootstrap/app.php (Laravel actual del proyecto)"
✓ "Pest (incluido en Laravel reciente)"
✓ php-version en CI → leer de composer.json require.php

✗ Fijar "Laravel 11" en propuestas, README o plantillas sin leer el repo
✗ Copiar Dockerfile php:8.3 si el proyecto ya usa 8.4
```

---

## Skills que aplican esta política

Todas las de backend, front, DevOps y análisis de proyecto. Referencia cruzada desde:

- `laravel-backend`, `nextjs-fullstack`, `node-backend`, `devops-base`
- `software-project-analysis`, `team-onboarding`, `supply-chain-security`

Reporte suite: `docs/stack-policy.md` (copia en repo origen; en installs usar este archivo).
