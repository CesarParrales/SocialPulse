---
name: nextjs-fullstack
description: >
  Guía Next.js (App Router, versión actual del proyecto) con Server Components, SSR/SSG/ISR,
  Server Actions, Route Handlers, auth con Auth.js v5, optimización y deploy. Usar cuando el
  usuario mencione Next.js, App Router, Server Components, Server Actions, RSC, SSR, SSG, ISR,
  middleware de Next.js, next/image, next/font, Vercel, o cuando pregunte "cómo hago
  fetch en Next.js", "cuándo usar Server vs Client Component", "cómo manejo auth en
  Next.js", "cómo optimizo imágenes", "cómo hago deploy en Vercel", o cualquier
  variante relacionada con Next.js moderno.
---

# Next.js Fullstack Skill

Next.js con App Router — patrones de producción (versión según `package.json` o última estable).

| Reference | Contenido |
|---|---|
| `references/app-router.md` | Estructura, convenciones de archivos, route groups, parallel/intercepting routes, Route Handlers, middleware, navegación |
| `references/server-client.md` | Server vs Client Components, composición, streaming con Suspense, Server Actions |
| `references/data-fetching.md` | Fetching, caché, revalidación, ISR, metadata dinámica, generateStaticParams |
| `references/auth.md` | Auth.js v5 (NextAuth): setup, middleware de protección, sesión en componentes |
| `references/optimization.md` | next/image, next/font, next.config.ts, OG images, deploy Vercel y self-hosted |
| `../laravel-backend/references/stack-versions.md` | Política de versiones del stack (manifest > memoria > latest stable) |

---

## Memoria

**Al iniciar:**

1. `.cursor/project-memory.md` — stack frontend, gates `npm run build`.
2. `package.json` — versión de Next.js, React y Node requerida.
3. `LEARNINGS.md` de **esta skill** — solo `## Pendientes`.

**Al cerrar:** estrategia de render/auth → project-memory si es decisión nueva; gaps → `LEARNINGS.md`.

---

## Protocolo de ejecución

0. **Memoria** — leer project-memory; detectar si el proyecto usa App o Pages Router.
1. **Detectar el router**: revisa si existe `app/` (App Router) o `pages/` (Pages Router) — `ls src/app app pages src/pages 2>/dev/null`. Proyecto nuevo → App Router siempre. Si es Pages Router existente, no migres salvo petición explícita. Lee `references/app-router.md` para estructura y convenciones.
2. **Decidir Server vs Client Component** para cada pieza de UI con el árbol de decisión de `references/server-client.md`: sin estado/eventos/APIs del browser → Server Component. Marca `'use client'` solo en las hojas interactivas.
3. **Elegir estrategia de datos** con `references/data-fetching.md`: static (SSG), ISR con `revalidate`/`revalidateTag`, o dynamic (SSR). Mutations → Server Actions con validación Zod + check de sesión; endpoints para terceros/webhooks → Route Handlers.
4. **Auth si aplica**: implementa con Auth.js v5 según `references/auth.md` — `auth()` en servidor, `useSession()` en cliente, middleware para proteger rutas. Gate: una ruta protegida sin sesión redirige a `/login` (verifica con `curl -I http://localhost:3000/dashboard` → 307).
5. **Optimizar**: imágenes con `next/image`, fuentes con `next/font`, `loading.tsx` en rutas con fetch async (ver `references/optimization.md` y el checklist de abajo).
6. **Verificar el build**: ejecuta `npm run build` y verifica que termina sin errores; revisa en el output que cada ruta tiene el tipo de render esperado (`○` static, `●` SSG, `ƒ` dynamic).
7. **Deploy** según destino (Vercel o Docker self-hosted) con `references/optimization.md`.
8. **Validación y cierre** — ejecutar `## Validación`; registrar gaps en `LEARNINGS.md`.

---

## Defaults si falta contexto

Asume y **declara** estos supuestos en la respuesta en lugar de preguntar (máx. 1 pregunta solo si es bloqueante):

- Proyecto nuevo → **App Router** + TypeScript + `src/` + **última Next.js estable**.
- Datos personalizados por usuario (dashboard, pedidos) → **dynamic (SSR)** con fetch en Server Component.
- Catálogo/contenido público con cambios infrecuentes → **ISR + `revalidateTag`** on-demand.
- Landing/blog/docs → **static (SSG)**.
- Auth → **Auth.js v5** con estrategia JWT y Prisma adapter.
- Estilos → Tailwind CSS; ORM → Prisma.
- Deploy → Vercel salvo que el usuario mencione VPS/Docker.

---

## App Router vs Pages Router

```
App Router (Next.js moderno) — usar en proyectos nuevos:
  ✅ Server Components por defecto (menos JS al cliente)
  ✅ Layouts anidados sin re-mount
  ✅ Server Actions (mutations sin API route)
  ✅ Streaming y Suspense nativo
  ✅ Mejor performance por defecto

Pages Router — solo en proyectos existentes:
  ✅ Más maduro, mejor documentación histórica
  ✅ Ecosistema de librerías más amplio
  ❌ Todo es Client Component por defecto
  ❌ No tiene Server Actions
  ❌ Layouts se complican con _app.tsx

REGLA: App Router en proyectos nuevos. Migrar Pages Router solo si hay razón.
```

---

## Checklist Next.js Producción

### Performance
- [ ] Imágenes con `next/image` (no `<img>`)
- [ ] Fuentes con `next/font` (no Google Fonts CDN)
- [ ] Server Components para contenido estático y fetches
- [ ] `dynamic('...', { ssr: false })` para componentes solo-cliente pesados
- [ ] `loading.tsx` en rutas con fetch async
- [ ] Metadata dinámica en páginas SEO-importantes

### Seguridad
- [ ] Middleware para proteger rutas autenticadas
- [ ] Server Actions con validación de sesión
- [ ] Variables de entorno: `NEXT_PUBLIC_*` solo para lo que va al cliente
- [ ] Headers de seguridad en `next.config.ts`
- [ ] Rate limiting en API routes críticas

### SEO
- [ ] `metadata` o `generateMetadata` en cada página pública
- [ ] `sitemap.ts` generado dinámicamente
- [ ] `robots.ts` configurado
- [ ] OG images con `ImageResponse`

---

## Ejemplo input → output

**Input:** "Página dashboard con datos de usuario autenticado."

**Output:** ruta `app/dashboard/page.tsx` Server Component + fetch dynamic; `'use client'` solo en widgets interactivos; middleware protege `/dashboard`; build muestra `ƒ` en dashboard. Gate: `npm run build` exit 0; `curl -I /dashboard` → 307 sin sesión.

---

## Validación

| Gate | Comando | Criterio |
|------|---------|----------|
| Build | `npm run build` | exit 0 |
| Render types | output del build | static/ISR/dynamic según diseño |
| Auth | `curl -I <url-ruta-protegida>` | redirect sin sesión |
| Checklist | sección Checklist Next.js Producción | ítems aplicables ✓ |

---

## Entregable

Al cerrar una tarea con esta skill, entrega:

```markdown
## Implementación Next.js — <feature>

**Router**: App Router | Pages Router
**Estrategia de render**: static | ISR (revalidate Ns / tag) | dynamic — por ruta
**Componentes**: lista de Server vs Client Components creados y por qué
**Auth**: rutas protegidas y mecanismo (middleware / auth() / useSession)

### Verificación
- [ ] `npm run build` pasa sin errores
- [ ] Tipos de render por ruta confirmados en output del build
- [ ] Rutas protegidas redirigen sin sesión

### Pendientes / riesgos
- ...
```

---

## Skills relacionadas

- `react-patterns` — hooks, estado cliente (React Query), formularios y performance de React
- `api-design` — diseño de los endpoints que consumen los Route Handlers
- `devops-base` — Docker, CI/CD y deploy self-hosted
- `performance-web` — Core Web Vitals y optimización más allá de Next.js
- `security-checklist` — revisión de seguridad antes de producción
- `testing-strategy` — estrategia de tests del proyecto

---

## Aprendizaje continuo

Al cerrar una tarea donde se usó esta skill, registra en `LEARNINGS.md` (misma carpeta) cualquier hallazgo:

- **Gap**: información que faltó o estaba desactualizada
- **Corrección**: instrucción que resultó incorrecta o ambigua
- **Mejora**: default o plantilla que habría acelerado la tarea

Formato: fecha, contexto (1 línea), hallazgo, cambio propuesto. La skill `skill-evolution` consolida estas entradas en el SKILL.md periódicamente.
