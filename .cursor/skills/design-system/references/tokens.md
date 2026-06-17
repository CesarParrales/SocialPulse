# Design Tokens — La Base de Todo

## Qué Son los Design Tokens

```
Un design token es la mínima unidad de decisión de diseño
almacenada como un par nombre-valor.

Sin tokens:
  color: #1A56DB;           → hardcoded, difícil de cambiar
  font-size: 16px;          → sin semántica, sin sistema

Con tokens:
  color: var(--color-primary-600);   → semántico, cambiable en un lugar
  font-size: var(--text-body-md);    → parte de una escala

Beneficio real:
  Cambiar el color primario de todo el producto → 1 línea
  vs 200 archivos con el hex hardcodeado
```

---

## Jerarquía de Tokens: Raw → Semánticos → Componente

```
Los tokens tienen 3 niveles de abstracción:

NIVEL 1 — RAW TOKENS (la paleta pura)
  Los valores sin semántica. Nunca se usan directamente en componentes.
  Solo existen para alimentar los tokens semánticos.

  --blue-100: #EFF6FF;
  --blue-200: #DBEAFE;
  --blue-300: #BFDBFE;
  --blue-400: #93C5FD;
  --blue-500: #60A5FA;
  --blue-600: #3B82F6;    ← el "primary" de la marca
  --blue-700: #2563EB;
  --blue-800: #1D4ED8;
  --blue-900: #1E3A8A;

NIVEL 2 — SEMANTIC TOKENS (el rol en el sistema)
  Asignan significado a los raw tokens.
  Estos son los que se usan en el código de componentes.

  --color-primary:           var(--blue-600);
  --color-primary-hover:     var(--blue-700);
  --color-primary-active:    var(--blue-800);
  --color-primary-subtle:    var(--blue-100);

  --color-text-primary:      var(--gray-900);
  --color-text-secondary:    var(--gray-600);
  --color-text-disabled:     var(--gray-400);
  --color-text-inverse:      var(--white);

  --color-bg-default:        var(--white);
  --color-bg-subtle:         var(--gray-50);
  --color-bg-raised:         var(--white);    ← con sombra

  --color-border-default:    var(--gray-200);
  --color-border-strong:     var(--gray-400);
  --color-border-focus:      var(--blue-500);

  --color-success:           var(--green-600);
  --color-warning:           var(--yellow-500);
  --color-error:             var(--red-600);

NIVEL 3 — COMPONENT TOKENS (específicos de un componente)
  Solo cuando un componente necesita valores propios.
  Se usan dentro del componente, no fuera.

  --button-primary-bg:       var(--color-primary);
  --button-primary-bg-hover: var(--color-primary-hover);
  --button-primary-text:     var(--color-text-inverse);
  --button-border-radius:    var(--radius-md);
  --button-padding-y:        var(--spacing-2);
  --button-padding-x:        var(--spacing-4);
```

---

## Tokens de Color — Paleta Completa

```css
/* ── RAW TOKENS ── */
:root {
  /* Grises */
  --gray-50:  #F9FAFB;
  --gray-100: #F3F4F6;
  --gray-200: #E5E7EB;
  --gray-300: #D1D5DB;
  --gray-400: #9CA3AF;
  --gray-500: #6B7280;
  --gray-600: #4B5563;
  --gray-700: #374151;
  --gray-800: #1F2937;
  --gray-900: #111827;

  /* Azul / Primary */
  --blue-50:  #EFF6FF;
  --blue-100: #DBEAFE;
  --blue-500: #3B82F6;
  --blue-600: #2563EB;
  --blue-700: #1D4ED8;

  /* Verde / Success */
  --green-50:  #F0FDF4;
  --green-500: #22C55E;
  --green-600: #16A34A;
  --green-700: #15803D;

  /* Rojo / Error */
  --red-50:  #FEF2F2;
  --red-500: #EF4444;
  --red-600: #DC2626;
  --red-700: #B91C1C;

  /* Amarillo / Warning */
  --yellow-50:  #FEFCE8;
  --yellow-500: #EAB308;
  --yellow-600: #CA8A04;
}

/* ── SEMANTIC TOKENS — LIGHT MODE ── */
:root {
  --color-primary:        var(--blue-600);
  --color-primary-hover:  var(--blue-700);
  --color-primary-subtle: var(--blue-50);

  --color-success:        var(--green-600);
  --color-success-subtle: var(--green-50);
  --color-error:          var(--red-600);
  --color-error-subtle:   var(--red-50);
  --color-warning:        var(--yellow-600);
  --color-warning-subtle: var(--yellow-50);

  --color-text-primary:   var(--gray-900);
  --color-text-secondary: var(--gray-600);
  --color-text-tertiary:  var(--gray-400);
  --color-text-disabled:  var(--gray-300);
  --color-text-inverse:   #FFFFFF;
  --color-text-link:      var(--blue-600);

  --color-bg-default:     #FFFFFF;
  --color-bg-subtle:      var(--gray-50);
  --color-bg-muted:       var(--gray-100);
  --color-bg-overlay:     rgba(0, 0, 0, 0.5);

  --color-border-default: var(--gray-200);
  --color-border-strong:  var(--gray-300);
  --color-border-focus:   var(--blue-500);
  --color-border-error:   var(--red-500);
}

/* ── SEMANTIC TOKENS — DARK MODE ── */
@media (prefers-color-scheme: dark) {
  :root {
    --color-text-primary:   var(--gray-50);
    --color-text-secondary: var(--gray-400);
    --color-text-tertiary:  var(--gray-500);
    --color-text-disabled:  var(--gray-600);

    --color-bg-default:     var(--gray-900);
    --color-bg-subtle:      var(--gray-800);
    --color-bg-muted:       var(--gray-700);

    --color-border-default: var(--gray-700);
    --color-border-strong:  var(--gray-600);
  }
}
```

---

## Tokens de Tipografía

```css
:root {
  /* Familias */
  --font-sans:    'Inter Variable', system-ui, sans-serif;
  --font-mono:    'JetBrains Mono', 'Fira Code', monospace;
  --font-display: 'Cal Sans', var(--font-sans);

  /* Escala de tamaños */
  --text-xs:   0.75rem;   /* 12px */
  --text-sm:   0.875rem;  /* 14px */
  --text-base: 1rem;      /* 16px */
  --text-lg:   1.125rem;  /* 18px */
  --text-xl:   1.25rem;   /* 20px */
  --text-2xl:  1.5rem;    /* 24px */
  --text-3xl:  1.875rem;  /* 30px */
  --text-4xl:  2.25rem;   /* 36px */
  --text-5xl:  3rem;      /* 48px */

  /* Pesos */
  --font-normal:   400;
  --font-medium:   500;
  --font-semibold: 600;
  --font-bold:     700;

  /* Line heights */
  --leading-none:    1;
  --leading-tight:   1.25;
  --leading-snug:    1.375;
  --leading-normal:  1.5;
  --leading-relaxed: 1.625;

  /* Tracking (letter-spacing) */
  --tracking-tight:  -0.025em;
  --tracking-normal:  0em;
  --tracking-wide:    0.025em;
  --tracking-wider:   0.05em;

  /* Roles semánticos tipográficos */
  --type-display:  var(--font-display) var(--font-bold) / var(--leading-tight) var(--text-4xl);
  --type-h1:       var(--font-sans) var(--font-bold) / var(--leading-tight) var(--text-3xl);
  --type-h2:       var(--font-sans) var(--font-semibold) / var(--leading-snug) var(--text-2xl);
  --type-h3:       var(--font-sans) var(--font-semibold) / var(--leading-snug) var(--text-xl);
  --type-body-lg:  var(--font-sans) var(--font-normal) / var(--leading-relaxed) var(--text-lg);
  --type-body:     var(--font-sans) var(--font-normal) / var(--leading-normal) var(--text-base);
  --type-body-sm:  var(--font-sans) var(--font-normal) / var(--leading-normal) var(--text-sm);
  --type-label:    var(--font-sans) var(--font-medium) / var(--leading-none) var(--text-sm);
  --type-caption:  var(--font-sans) var(--font-normal) / var(--leading-normal) var(--text-xs);
  --type-code:     var(--font-mono) var(--font-normal) / var(--leading-relaxed) var(--text-sm);
}
```

---

## Tokens de Espaciado

```css
:root {
  /* Escala de 4pt — la más común */
  --spacing-px:  1px;
  --spacing-0:   0;
  --spacing-0-5: 0.125rem;  /* 2px */
  --spacing-1:   0.25rem;   /* 4px */
  --spacing-1-5: 0.375rem;  /* 6px */
  --spacing-2:   0.5rem;    /* 8px */
  --spacing-2-5: 0.625rem;  /* 10px */
  --spacing-3:   0.75rem;   /* 12px */
  --spacing-4:   1rem;      /* 16px */
  --spacing-5:   1.25rem;   /* 20px */
  --spacing-6:   1.5rem;    /* 24px */
  --spacing-8:   2rem;      /* 32px */
  --spacing-10:  2.5rem;    /* 40px */
  --spacing-12:  3rem;      /* 48px */
  --spacing-16:  4rem;      /* 64px */
  --spacing-20:  5rem;      /* 80px */
  --spacing-24:  6rem;      /* 96px */

  /* Border radius */
  --radius-none: 0;
  --radius-sm:   0.25rem;   /* 4px */
  --radius-md:   0.5rem;    /* 8px */
  --radius-lg:   0.75rem;   /* 12px */
  --radius-xl:   1rem;      /* 16px */
  --radius-2xl:  1.5rem;    /* 24px */
  --radius-full: 9999px;    /* pill */

  /* Sombras */
  --shadow-xs:  0 1px 2px 0 rgba(0,0,0,0.05);
  --shadow-sm:  0 1px 3px 0 rgba(0,0,0,0.1), 0 1px 2px -1px rgba(0,0,0,0.1);
  --shadow-md:  0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -2px rgba(0,0,0,0.1);
  --shadow-lg:  0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -4px rgba(0,0,0,0.1);
  --shadow-xl:  0 20px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.1);

  /* Motion / transiciones */
  --duration-fast:    100ms;
  --duration-normal:  200ms;
  --duration-slow:    300ms;
  --duration-slower:  500ms;
  --ease-default:     cubic-bezier(0.4, 0, 0.2, 1);
  --ease-in:          cubic-bezier(0.4, 0, 1, 1);
  --ease-out:         cubic-bezier(0, 0, 0.2, 1);
  --ease-bounce:      cubic-bezier(0.175, 0.885, 0.32, 1.275);

  /* Z-index */
  --z-base:     0;
  --z-raised:   10;
  --z-dropdown: 100;
  --z-sticky:   200;
  --z-overlay:  300;
  --z-modal:    400;
  --z-toast:    500;
  --z-tooltip:  600;
}
```

---

## Tokens en Diferentes Plataformas

```javascript
// Style Dictionary — convierte tokens JSON a múltiples plataformas
// tokens/color.json
{
  "color": {
    "primary": { "value": "#2563EB", "type": "color" },
    "text": {
      "primary":   { "value": "#111827", "type": "color" },
      "secondary": { "value": "#4B5563", "type": "color" }
    }
  }
}

// Genera automáticamente:
// CSS: --color-primary: #2563EB;
// SCSS: $color-primary: #2563EB;
// iOS: UIColor.primary = UIColor(hex: "2563EB")
// Android: <color name="colorPrimary">#2563EB</color>
// React Native: primary: '#2563EB'
// Figma: a través del plugin Tokens Studio
```

```dart
// Flutter — tokens como constantes
class AppTokens {
  // Colors
  static const colorPrimary     = Color(0xFF2563EB);
  static const colorPrimaryHover= Color(0xFF1D4ED8);
  static const colorTextPrimary = Color(0xFF111827);
  static const colorBgDefault   = Color(0xFFFFFFFF);

  // Spacing
  static const spacing1 = 4.0;
  static const spacing2 = 8.0;
  static const spacing4 = 16.0;
  static const spacing8 = 32.0;

  // Typography
  static const textBase = 16.0;
  static const textSm   = 14.0;
  static const textLg   = 18.0;

  // Border radius
  static const radiusSm = 4.0;
  static const radiusMd = 8.0;
  static const radiusLg = 12.0;
}
```

---

## Plantilla portable — escala de tokens (ASCII)

```
Sin imagen, generar esta representación ASCII en markdown
(no depender de herramientas de visualización externas al editor):

Paleta de color con roles semánticos:
  ▓▓▓ Primary (Blue 600)     #2563EB  texto sobre blanco: ✅ WCAG AA
  ▓▓▓ Primary Hover (Blue 700) #1D4ED8
  ░░░ Primary Subtle (Blue 50) #EFF6FF
  ─────────────────────────────────────
  ▓▓▓ Success (Green 600)    #16A34A
  ▓▓▓ Error (Red 600)        #DC2626
  ▓▓▓ Warning (Yellow 600)   #CA8A04
  ─────────────────────────────────────
  ▓▓▓ Text Primary (Gray 900) #111827
  ▒▒▒ Text Secondary (Gray 600) #4B5563
  ░░░ Text Tertiary (Gray 400) #9CA3AF

Escala de espaciado:
  ▌ 4px (spacing-1)
  ▌▌ 8px (spacing-2)
  ▌▌▌▌ 16px (spacing-4)
  ▌▌▌▌▌▌▌▌ 32px (spacing-8)
  ▌▌▌▌▌▌▌▌▌▌▌▌▌▌▌▌ 64px (spacing-16)

Uso: "genera la escala de tokens de color/espaciado/tipografía"
```
