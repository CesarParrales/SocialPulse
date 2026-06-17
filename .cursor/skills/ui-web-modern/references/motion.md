# Micro-interacciones y Animación

## Qué son las Micro-interacciones

```
Una micro-interacción es la respuesta visual a una acción específica del usuario.
Comunican estado. No son decoración — son feedback.

Ejemplos de micro-interacciones bien diseñadas:
→ El botón "Me gusta" de Twitter que explota con corazones
→ El toggle que se desliza suavemente al activarse
→ El checkbox que hace "check" con una animación de trazo
→ La notificación que entra desde arriba y desaparece
→ El input que sacude ligeramente cuando hay un error
→ El skeleton screen que pulsa mientras carga contenido
→ El icono de guardar que se transforma en checkmark al éxito

Por qué importan:
→ Hacen la interfaz sentirse "viva" y responsiva
→ Confirman que la acción fue recibida (feedback)
→ Reducen la ansiedad del usuario ("sí, funcionó")
→ Guían la atención a lo que cambió
```

---

## Los Principios de Animación para UI

```
1. PROPÓSITO — cada animación tiene una razón funcional
   → Indicar cambio de estado
   → Guiar la atención del usuario
   → Mostrar relación entre elementos (origen y destino)
   → Dar feedback de una acción

2. DURACIÓN — rápida para lo pequeño, más lenta para lo grande
   Micro (interacciones de botones, toggles):  100-200ms
   Pequeña (hover states, tooltips):          150-250ms
   Media (modales, panels):                   200-350ms
   Grande (transiciones de página):           300-500ms
   Nunca más de 500ms para transiciones de UI (se siente lento)

3. EASING — nunca linear para movimiento físico
   ease-out:   elementos que entran (deceleran al llegar)
   ease-in:    elementos que salen (aceleran al irse)
   ease-in-out: transiciones en el mismo lugar (botones, toggles)
   spring:     rebote natural para elementos que "aterrizan"

4. REDUCIR MOVIMIENTO — respetar preferencias del usuario
   @media (prefers-reduced-motion: reduce) {
     * { animation-duration: 0.01ms !important; }
   }

5. CONSISTENCIA — los elementos similares se animan igual
   Si los modales entran desde arriba, todos los modales entran desde arriba
   Si los toasts salen hacia la derecha, todos los toasts salen hacia la derecha
```

---

## Timing y Easing Reference

```css
/* Valores recomendados para UI */

/* Micro-interacciones (hover, toggle, checkbox) */
transition: all 150ms cubic-bezier(0.4, 0, 0.2, 1);

/* Elementos pequeños que entran */
animation: enter 200ms cubic-bezier(0, 0, 0.2, 1);

/* Elementos pequeños que salen */
animation: exit 150ms cubic-bezier(0.4, 0, 1, 1);

/* Modales y panels */
animation: slide-in 250ms cubic-bezier(0, 0, 0.2, 1);

/* Spring physics (para elementos que "aterrizan") */
/* No disponible en CSS puro — usar Framer Motion o Spring */
spring: { stiffness: 300, damping: 30 }

/* Tablas de referencia de duración:
   Hover state:          100-150ms
   Focus state:          100ms
   Button active:        100ms
   Tooltip appear:       150-200ms
   Dropdown open:        150-200ms
   Modal open:           200-300ms
   Toast notification:   300-400ms (enter), 200ms (exit)
   Page transition:      300-500ms
   Loading skeleton:     1.5s (pulse cycle)
```

---

## Patrones de Animación Comunes

### Loading States

```css
/* Skeleton Screen — el más moderno */
.skeleton {
  background: linear-gradient(
    90deg,
    var(--color-bg-muted) 25%,
    var(--color-bg-subtle) 50%,  /* shimmer más claro */
    var(--color-bg-muted) 75%
  );
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}

@keyframes shimmer {
  0%   { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

/* Spinner clásico (para acciones específicas, no para páginas) */
.spinner {
  border: 2px solid var(--color-border-default);
  border-top-color: var(--color-primary);
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}

/* Progress bar (para operaciones con progreso conocido) */
.progress-bar {
  background: var(--color-primary);
  height: 3px;
  transition: width 300ms ease-out;
  /* Para progreso indeterminado: */
  animation: indeterminate 1.5s ease-in-out infinite;
}
```

### Feedback de Acciones

```css
/* Success checkmark (SVG animado) */
.checkmark {
  stroke-dasharray: 100;
  stroke-dashoffset: 100;
  animation: draw 400ms ease-out forwards;
}

@keyframes draw {
  to { stroke-dashoffset: 0; }
}

/* Error shake (input con error) */
@keyframes shake {
  0%, 100% { transform: translateX(0); }
  20%       { transform: translateX(-8px); }
  40%       { transform: translateX(8px); }
  60%       { transform: translateX(-4px); }
  80%       { transform: translateX(4px); }
}

.input-error {
  animation: shake 400ms ease-out;
}

/* Button click ripple */
.button::after {
  content: '';
  position: absolute;
  inset: 0;
  border-radius: inherit;
  background: currentColor;
  opacity: 0;
  transition: opacity 200ms;
}
.button:active::after { opacity: 0.1; }
```

### Entrada y Salida de Elementos

```css
/* Toast notification — entra desde arriba */
@keyframes toast-enter {
  from {
    opacity: 0;
    transform: translateY(-100%) scale(0.9);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

/* Modal — escala desde el centro */
@keyframes modal-enter {
  from {
    opacity: 0;
    transform: scale(0.95);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

/* Dropdown — crece desde el origen */
@keyframes dropdown-enter {
  from {
    opacity: 0;
    transform: scaleY(0.9);
    transform-origin: top;
  }
  to {
    opacity: 1;
    transform: scaleY(1);
  }
}

/* Tooltip — fade in */
@keyframes tooltip-enter {
  from { opacity: 0; transform: translateY(4px); }
  to   { opacity: 1; transform: translateY(0); }
}
```

---

## Framer Motion — Micro-interacciones en React

```tsx
import { motion, AnimatePresence } from 'framer-motion';

// Hover state con spring physics
<motion.button
  whileHover={{ scale: 1.02 }}
  whileTap={{ scale: 0.98 }}
  transition={{ type: 'spring', stiffness: 400, damping: 30 }}
>
  Click me
</motion.button>

// Entrada/salida con AnimatePresence
<AnimatePresence>
  {isOpen && (
    <motion.div
      initial={{ opacity: 0, y: -10 }}
      animate={{ opacity: 1, y: 0 }}
      exit={{ opacity: 0, y: -10 }}
      transition={{ duration: 0.2, ease: 'easeOut' }}
    >
      Dropdown content
    </motion.div>
  )}
</AnimatePresence>

// Stagger para listas (cada item entra con delay)
const containerVariants = {
  hidden: {},
  visible: {
    transition: { staggerChildren: 0.05 }
  }
};

const itemVariants = {
  hidden: { opacity: 0, y: 10 },
  visible: { opacity: 1, y: 0, transition: { duration: 0.2 } }
};

<motion.ul variants={containerVariants} initial="hidden" animate="visible">
  {items.map(item => (
    <motion.li key={item.id} variants={itemVariants}>
      {item.name}
    </motion.li>
  ))}
</motion.ul>

// Layout animation (reordenar lista)
<motion.div layout layoutId={item.id}>
  {/* Framer Motion anima el reposicionamiento automáticamente */}
</motion.div>
```

---

## Cuándo NO Animar

```
Animaciones que dañan la experiencia:

❌ Animaciones en elementos de texto largo (hace el texto más difícil de leer)
❌ Animaciones que bloquean la interacción (el usuario espera que termine)
❌ Animaciones que se repiten sin trigger del usuario (distraen y cansan)
❌ Animaciones de más de 500ms en interacciones frecuentes
❌ Efectos parallax agresivos (causan mareo en algunos usuarios)
❌ Múltiples elementos animándose simultáneamente sin jerarquía

Señal de demasiada animación:
Si el usuario tiene que esperar para interactuar con algo → demasiada animación.
Si el usuario nota la animación antes de notar el contenido → demasiada animación.

La prueba del prefers-reduced-motion:
Activar "Reducir movimiento" en el sistema operativo.
Si el producto se rompe o pierde información → las animaciones eran funcionales
y necesitan un fallback sin movimiento.
Si el producto se ve igual → las animaciones eran decorativas (perfecto).
```
