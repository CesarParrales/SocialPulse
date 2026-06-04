# Web Interface Guidelines — Ejemplos

Antes/después para hallazgos frecuentes. Usar como referencia al reportar o al corregir.

---

## Accesibilidad

**Botón solo icono sin aria-label**

```tsx
// ❌
<button onClick={onClose}><XIcon /></button>

// ✅
<button onClick={onClose} aria-label="Cerrar"><XIcon aria-hidden /></button>
```

**Acción con div en lugar de button**

```tsx
// ❌
<div onClick={handleSubmit}>Enviar</div>

// ✅
<button type="button" onClick={handleSubmit}>Enviar</button>
```

**Input sin label**

```tsx
// ❌
<input type="email" placeholder="Email" />

// ✅
<label htmlFor="email">Email</label>
<input id="email" type="email" name="email" autoComplete="email" placeholder="tu@ejemplo.com…" />
```

---

## Focus

**outline-none sin reemplazo**

```css
/* ❌ */
.btn { outline: none; }

/* ✅ */
.btn { outline: none; }
.btn:focus-visible { outline: 2px solid var(--focus); outline-offset: 2px; }
```

```tsx
// ✅ con Tailwind
<button className="focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-blue-500">
  Enviar
</button>
```

---

## Formularios

**Bloquear paste**

```tsx
// ❌
<input onPaste={(e) => e.preventDefault()} />

// ✅ — no bloquear paste; validar onBlur o onSubmit
```

**Placeholder sin ellipsis ni patrón**

```tsx
// ❌
<input placeholder="Buscar" />

// ✅
<input placeholder="Buscar por nombre…" />
```

---

## Animación

**transition: all**

```css
/* ❌ */
.card { transition: all 0.2s; }

/* ✅ */
.card { transition: transform 0.2s, opacity 0.2s; }
```

**Sin prefers-reduced-motion**

```css
/* ❌ */
@keyframes slide { ... }
.panel { animation: slide 0.3s ease; }

/* ✅ */
@media (prefers-reduced-motion: no-preference) {
  .panel { animation: slide 0.3s ease; }
}
```

---

## Tipografía

**Tres puntos rectos**

```tsx
// ❌
"Cargando..."
"Buscar..."

// ✅
"Cargando…"
"Buscar…"
```

**Números en columnas/tablas**

```css
/* ✅ */
.tabular { font-variant-numeric: tabular-nums; }
```

---

## Contenido y layout

**Flex sin min-w-0 (truncado no funciona)**

```tsx
// ❌
<div className="flex">
  <span className="truncate">{longTitle}</span>
</div>

// ✅
<div className="flex min-w-0">
  <span className="truncate">{longTitle}</span>
</div>
```

**Estado vacío no manejado**

```tsx
// ❌
{items.map(i => <Item key={i.id} {...i} />)}

// ✅
{items.length === 0 ? <EmptyState /> : items.map(i => <Item key={i.id} {...i} />)}
```

---

## Imágenes

**Sin dimensiones (CLS)**

```tsx
// ❌
<img src={url} alt="Producto" />

// ✅
<img src={url} alt="Producto" width={400} height={300} loading="lazy" />
```

---

## Modales / sheets

**Sin overscroll-behavior**

```tsx
// ❌
<div className="fixed inset-0 overflow-y-auto">

// ✅
<div className="fixed inset-0 overflow-y-auto overscroll-contain">
```

---

## Navegación

**Navegar con onClick sin Link**

```tsx
// ❌
<span onClick={() => router.push('/settings')}>Configuración</span>

// ✅
<Link href="/settings">Configuración</Link>
```

---

## Copy

**Botón genérico / voz pasiva**

```tsx
// ❌
<button>Continuar</button>
"El archivo será guardado"

// ✅
<button>Guardar API Key</button>
"Guarda el archivo"
```

**Números escritos**

```tsx
// ❌
"Tienes tres notificaciones"

// ✅
"Tienes 3 notificaciones"
```
