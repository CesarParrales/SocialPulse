# Formularios Complejos en Admin

## Por Qué los Formularios de Admin son Diferentes

```
Formulario de consumer app (registro):
  → 3-5 campos
  → Usuario lo usa 1 vez
  → Flujo lineal y guiado
  → Error = fricción alta

Formulario de admin (crear/editar entidad):
  → 10-30+ campos
  → Operador lo usa 100+ veces al día
  → Estructura compleja con secciones
  → Necesita ser rápido para el experto
  → La validación es una ayuda, no un obstáculo

El objetivo del formulario de admin no es ser amigable para novatos.
Es ser eficiente para expertos repetidores.
```

---

## Layout de Formulario por Complejidad

```
FORMULARIO SIMPLE (< 8 campos):
  Una columna, campos apilados verticalmente.
  ┌──────────────────────────────┐
  │ Nombre *                     │
  │ [_________________________] │
  │ Email *                      │
  │ [_________________________] │
  │ Estado                       │
  │ [Activo ▼]                   │
  │ [Cancelar]        [Guardar]  │
  └──────────────────────────────┘

FORMULARIO MEDIANO (8-20 campos):
  Dos columnas para campos relacionados del mismo ancho.
  ┌──────────────────────────────────────────┐
  │ Información Personal                     │
  ├─────────────────────┬────────────────────┤
  │ Nombre *            │ Apellido *          │
  │ [_______________]   │ [_______________]  │
  ├─────────────────────┴────────────────────┤
  │ Email *                                  │
  │ [_____________________________________]  │
  ├─────────────────────┬────────────────────┤
  │ País *              │ Ciudad             │
  │ [_______________]   │ [_______________]  │
  └──────────────────────────────────────────┘

FORMULARIO COMPLEJO (20+ campos):
  Dividido en secciones con pestañas o acordeón.
  Algunas secciones en 2 columnas, otras en 1.
  
  [Datos Básicos] [Dirección] [Configuración] [Permisos]
  ← tabs o secciones con scroll vertical →
```

---

## Secciones y Agrupación

```
Los campos del formulario deben agruparse por:

1. RELACIÓN SEMÁNTICA
   → Campos sobre la misma entidad o concepto van juntos
   → Información de contacto: nombre + email + teléfono
   → Dirección: calle + ciudad + estado + código postal + país
   → NO mezclar campos de contextos distintos sin separador

2. FRECUENCIA DE EDICIÓN
   → Los campos más editados: al principio o en la primera sección
   → Los campos de configuración avanzada: al final o en sección colapsable
   → Los campos de solo lectura: agrupados o con estilo diferente

3. FLUJO LÓGICO
   → Si un campo depende de otro, el campo padre va primero
   → Los campos condicionales (aparecen según otro valor) van agrupados

SEPARADORES DE SECCIÓN:
  Opción 1 — Título de sección:
  ─────────────────────────────
  Información de Contacto
  ─────────────────────────────

  Opción 2 — Card por sección:
  ┌─────────────────────────────┐
  │ Información de Contacto     │
  │ ─────────────────────────── │
  │ [campos]                    │
  └─────────────────────────────┘

  Opción 3 — Tabs (formularios muy largos):
  [Datos Básicos] [Configuración] [Permisos]
  → Solo si las secciones son completamente independientes
  → Si hay validación cross-section, usar scroll vertical
```

---

## Campos Especiales en Admin

### Selectores con Búsqueda (Combobox)

```
Para campos con muchas opciones (> 10):

❌ MAL: <select> básico con 500 opciones
   → Scroll largo para encontrar la opción
   → Sin búsqueda = ineficiente para el operador experto

✅ BIEN: Combobox con búsqueda
   Usuario *
   [Buscar usuario...              ▼]
   → Al escribir: filtra las opciones en tiempo real
   → Si hay miles: busca en el API (debounced)
   → Muestra avatar/extra info de cada opción
   → Permite limpiar con ×

Para relaciones múltiples:
   Etiquetas *
   [Etiqueta 1 ×] [Etiqueta 2 ×] [Añadir etiqueta... ▼]
   → Tags removibles
   → Input de búsqueda en el mismo campo
```

### Editor de Texto Rico (Rich Text)

```
Para campos de contenido largo con formato:
  → Usar un editor rico (Tiptap, Quill, Slate.js)
  → Barra de herramientas minimalista para admin (no necesita todo)
  → Modos: Visual + HTML Source para operadores avanzados

Campos donde usar editor rico:
  → Descripción de producto (e-commerce)
  → Contenido de artículo (CMS)
  → Notas internas de un caso/ticket
  → Email templates

Campos donde NO usar editor rico:
  → Nombre, email, dirección (plain text suficiente)
  → SEO meta description (texto plano, con contador de chars)
  → Notas cortas (textarea estándar)
```

### Upload de Archivos/Imágenes

```
Para productos, usuarios, documentos:

Simple (un archivo):
  ┌─────────────────────────────────────────┐
  │  📎  Arrastrar aquí o                  │
  │      [Seleccionar archivo]              │
  │      Formatos: JPG, PNG. Máx: 5MB      │
  └─────────────────────────────────────────┘
  → Preview inmediato al subir
  → Barra de progreso durante la carga
  → Botón de eliminar en la preview

Múltiples imágenes (galería de producto):
  ┌────┬────┬────┬────┬──────────────────┐
  │img1│img2│img3│img4│  + Añadir        │
  │[×] │[×] │[×] │[×] │  imágenes       │
  └────┴────┴────┴────┴──────────────────┘
  → Drag-and-drop para reordenar
  → Click en thumbnail para eliminar
  → Primera imagen = imagen principal (indicador visual)
```

### Campos Condicionales

```
Campos que aparecen o desaparecen según otros valores:

Ejemplo: método de pago determina los campos necesarios
  Tipo: [Tarjeta de crédito ▼]
  → Si Tarjeta: muestra Número, Fecha, CVV
  → Si Transferencia: muestra IBAN, Banco
  → Si PayPal: muestra Email de PayPal

Implementación:
  → Los campos condicionales se animan suavemente (altura 0 → auto)
  → No usar display:none sin animación (el salto visual es confuso)
  → Limpiar los valores de los campos escondidos al esconderse

Otro ejemplo: "mismo que facturación" checkbox en dirección de envío
  [☑ Usar la misma dirección de facturación]
  → Si está marcado: ocultar los campos de envío
  → Si se desmarca: mostrar los campos de envío (vacíos)
```

---

## Validación en Admin

```
La validación en admin debe ser FUNCIONAL, no burocrática.

CUÁNDO VALIDAR:
  onBlur:   al salir del campo (el más balanceado)
  onSubmit: solo para validaciones que requieren llamada al server
  onChange: solo si el feedback inmediato es valioso (contador de chars, formato)

MENSAJES DE ERROR PARA ADMIN:
  Los operadores de admin son expertos — los mensajes pueden ser más técnicos.
  Pero deben seguir siendo claros y accionables.

  ❌ MAL: "Error de validación en el campo email"
  ✅ BIEN: "Ingresa un email válido. Ejemplo: usuario@empresa.com"

  ❌ MAL: "El valor no cumple los requisitos"
  ✅ BIEN: "El precio debe ser mayor a 0 y menor a 999,999"

VALIDACIÓN CROSS-FIELD:
  Cuando la validación involucra múltiples campos:
  → Mostrar el error en el campo más relevante
  → O mostrar un banner de error al inicio de la sección
  → NO solo al hacer submit — validar tan pronto como sea posible

GUARDAR SIN COMPLETAR (draft mode):
  Para formularios muy largos, permitir guardar como borrador:
  → El operador puede guardar y continuar después
  → Los campos requeridos solo bloquean la publicación/activación, no el guardado
  → El estado "Borrador" es visible en la tabla principal
```

---

## Wireframes portables — formularios complejos (ASCII)

```
Generar wireframes ASCII directamente en markdown
(no depender de herramientas de visualización externas al editor):

Formulario de 2 columnas con secciones:
  → Información básica (2 cols) + dirección (2 cols) + notas (1 col)
  → Con campos requeridos marcados y helper text

Formulario con campos especiales:
  → Combobox con búsqueda, upload de imagen, campo condicional

Estados de validación:
  → Campos en estado default, focus, error, success
  → El formulario completo con múltiples errores simultáneos

Formulario con tabs para entidades complejas:
  → Tabs de navegación + contenido de cada tab
  → Con indicador de tab que tiene errores de validación

Uso:
"genera el formulario para [crear/editar entidad: producto/usuario/pedido]"
"genera el formulario de dirección con validación"
"genera el formulario con tabs para [entidad compleja]"
```
