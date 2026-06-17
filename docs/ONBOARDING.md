# Onboarding de agencia — SocialPulse

Guía end-to-end para que una agencia nueva use SocialPulse **sin asistencia técnica** (PRD §14). Orientada a admin de agencia y operadores.

---

## Resumen del flujo

```
Super-admin crea agencia (opcional: invita admin)
        ↓
Admin acepta invitación / inicia sesión
        ↓
Crea workspace (marca/cliente)
        ↓
Conecta Meta y/o Google Ads
        ↓
Selecciona activos a monitorear
        ↓
Espera primera ingesta (≤ 24h orgánico; Stories ≤ 6h si hay stories activas)
        ↓
Revisa dashboard → benchmarks → reportes PDF
        ↓
(Opcional) Asigna operador o cliente readonly al workspace
```

**Tiempo estimado:** 20–30 minutos (excluyendo aprobaciones OAuth externas).

---

## 1. Prerrequisitos (plataforma)

Antes de onboarding de clientes reales, el super-admin debe verificar en **Plataforma → Integraciones**:

| Integración | Variables `.env` | Estado esperado |
|-------------|------------------|-----------------|
| Meta OAuth | `META_APP_ID`, `META_APP_SECRET` | Configurado |
| Meta System User (prod) | `META_SYSTEM_USER_ACCESS_TOKEN`, `META_BUSINESS_ID` | Configurado en prod |
| Google Ads | `GOOGLE_ADS_CLIENT_ID`, `GOOGLE_ADS_CLIENT_SECRET`, `GOOGLE_ADS_DEVELOPER_TOKEN` | Configurado |

Horizon y scheduler deben estar activos en el servidor (ingesta automática).

**URLs para Meta App Review:** `{APP_URL}/legal/privacy` y `{APP_URL}/legal/terms`.

---

## 2. Crear agencia (super-admin)

1. Iniciar sesión como `super_admin`.
2. **Plataforma** → **Nueva agencia**.
3. Completar nombre, plan y email de facturación.
4. *(Opcional)* Email del admin inicial → se envía invitación automática.
5. Guardar.

---

## 3. Admin de agencia — primer acceso

### Si recibió invitación por email

1. Abrir enlace del correo (válido 7 días).
2. Nombre + contraseña → **Registrarse**.
3. Redirige al inicio de la agencia.

### Si ya tiene cuenta

1. **Iniciar sesión** en la URL de la app.
2. Menú **Configuración** (agencia) para revisar datos básicos.

---

## 4. Crear workspace (marca / cliente)

1. **Workspaces** → **Nuevo workspace**.
2. Campos recomendados:
   - **Nombre:** marca del cliente.
   - **Industria:** usada en benchmarks de industria (n≥30).
   - **Región:** segmentación de benchmarks.
   - **Zona horaria:** define ventana de ingesta diaria.
3. Guardar → abrir el workspace.

---

## 5. Conectar cuentas (OAuth)

1. Dentro del workspace → **Conexiones**.
2. **Conectar Meta (OAuth)** — flujo Facebook/Google:
   - Aceptar permisos solicitados.
   - Volver a SocialPulse tras el callback.
3. **Conectar Meta (System User)** — solo si la plataforma lo tiene configurado (producción).
4. **Conectar Google Ads** — autorizar cuenta de Ads.

### ⚠️ Comunicar al cliente (obligatorio PRD)

> **Stories de Instagram/Facebook no tienen histórico.** SocialPulse solo captura Stories mientras están activas (24 h) y el sistema ya está conectado. **Conectar la cuenta cuanto antes** evita gaps. Esto es una limitación de Meta, no un error de la plataforma.

Incluir este mensaje en propuesta comercial y en el primer email post-conexión.

---

## 6. Seleccionar activos

Tras conectar Meta, aparece la lista de activos descubiertos:

- Páginas de Facebook
- Cuentas Instagram Business vinculadas
- Cuentas Meta Ads
- *(Google)* cuentas de anuncios

1. Marcar checkboxes de los activos a monitorear.
2. **Guardar activos**.

**Regla:** un mismo activo (ej. una página) **no puede** estar en dos workspaces distintos.

---

## 7. Primera ingesta de datos

| Tipo | Cuándo corre | Qué esperar |
|------|--------------|-------------|
| Orgánico (posts, reels) | Diario ~02:00 (tz workspace) | Datos en dashboard al día siguiente |
| Stories | Cada 6 h si hay stories activas | Solo stories vigentes (< 24 h) |
| Pagado (Ads) | Job dedicado | Métricas de campañas en dashboard |

**No hay botón "sincronizar ahora" en MVP** — depende de colas programadas.

Si tras 24 h no hay datos:

1. Verificar conexión activa (no `expired`).
2. Confirmar activos guardados y `is_active`.
3. Revisar notificaciones / email de fallo de ingesta.
4. Escalar según [RUNBOOK.md](./RUNBOOK.md) §6.

---

## 8. Usar el producto

### Dashboard

**Workspaces** → marca → **Dashboard**

- KPIs orgánicos y pagados.
- Selector de período (7d, 30d, 90d, custom).
- Gráficos de evolución.

### Benchmarks

Comparación vs histórico propio y vs industria (cuando n≥30 en el segmento).

### Compare

Comparación entre canales / períodos en el mismo workspace.

### Reportes

Generar PDF brandeable → cola `reports` → descarga cuando esté listo.

---

## 9. Equipo y roles

| Rol | Cómo se asigna | Permisos |
|-----|----------------|----------|
| **Admin agencia** | Invitación en **Equipo** | Todo en la agencia |
| **Operador** | Invitación + asignación a workspace | Workspaces asignados |
| **Cliente readonly** | Asignación en workspace con rol "Cliente" | Solo dashboard de esa marca |

### Invitar operador

1. **Equipo** → email + rol → enviar invitación.
2. En el workspace → **Overview** → asignar operador al workspace.

### Invitar cliente (solo lectura)

1. Crear usuario en la agencia (o invitar como operador y cambiar rol).
2. Workspace → **Overview** → asignar miembro con rol **Cliente (solo lectura)**.
3. El cliente accede solo al dashboard de esa marca.

---

## 10. Checklist de onboarding exitoso

- [ ] Workspace creado con industria y región
- [ ] Meta y/o Google conectados sin error
- [ ] Activos seleccionados y guardados
- [ ] Cliente informado sobre limitación de Stories
- [ ] Dashboard muestra datos tras primera ingesta
- [ ] (Opcional) Reporte PDF de prueba generado
- [ ] (Opcional) Cliente readonly invitado

---

## 11. Usuarios demo (desarrollo)

Tras `php artisan db:seed --class=Modules\\Workspaces\\Database\\Seeders\\DemoSeeder`:

| Rol | Email | Password |
|-----|-------|----------|
| Super admin | `super@socialpulse.test` | `password` |
| Admin agencia | `admin@agenciademo.test` | `password` |
| Operador | `operador@agenciademo.test` | `password` |
| Cliente readonly | `cliente@agenciademo.test` | `password` |

---

*Ver también: [LAUNCH-CHECKLIST.md](./LAUNCH-CHECKLIST.md), [RUNBOOK.md](./RUNBOOK.md), [socialpulse-prd.md](../socialpulse-prd.md).*
