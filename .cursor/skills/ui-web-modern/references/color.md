# Color y Paletas Modernas

## Cómo Construir una Paleta Desde Cero

```
Una paleta de producto bien construida tiene:

1. COLOR BASE / BRAND (1 color):
   → El color principal de la marca
   → Genera 9-11 variaciones de tono (50-950)

2. NEUTRAL / GRAY (escala completa):
   → Los grises son el 80% de la interfaz
   → La mayoría de UI usa grises con un hint de color para calidez

3. SEMANTIC COLORS (2-4 colores):
   → Success:  verde (confirmación, completado)
   → Warning:  amarillo/naranja (atención, precaución)
   → Error:    rojo (fallo, problema)
   → Info:     azul claro (información neutral)

4. ACCENT (1-2 colores, opcional):
   → Para destacar elementos sin usar el color primario
   → Debe contrastar con el primario sin chocar

PROCESO DE CONSTRUCCIÓN:
1. Elegir el color de brand (H en HSL)
2. Generar la escala con un generador (Radix, Tailwind, oklch)
3. Verificar contraste en los niveles de uso (texto, fondos, bordes)
4. Ajustar para dark mode
5. Añadir los colores semánticos
```

---

## Escalas de Color — Cómo Nombrarlas y Usarlas

```
NAMING POR ESCALA (50-950):

50:   Fondo muy sutil, hover states en elementos de muy bajo contraste
100:  Fondos sutiles, highlighted backgrounds
200:  Bordes ligeros, divisores sutiles
300:  Bordes normales, íconos decorativos
400:  Texto deshabilitado, placeholders
500:  Íconos de acento, borders de focus ring
600:  Color primario para texto sobre blanco (verifica contraste AA)
700:  Hover states del color primario
800:  Active states, texto muy destacado
900:  Headings cuando el color es el primario
950:  Máximo contraste, solo en elementos muy específicos

USO POR NIVEL EN UNA INTERFAZ TÍPICA:
  Texto principal:    900 o 800
  Texto secundario:   600 o 700
  Texto deshabilitado: 400 o 300
  Borders:            200 o 300
  Fondos de hover:    50 o 100
  Color de acción:    600 (primario sobre blanco) o 300 (invertido sobre oscuro)
```

---

## Paletas Modernas por Estilo de Producto

```
SAAS / PRODUCTIVITY (azul o índigo como primario):
  Primary: Indigo (#4F46E5) o Blue (#2563EB)
  Neutral: Slate grays (con hint de azul: #F8FAFC, #F1F5F9, #E2E8F0)
  Accent: Violet o Purple para highlights secundarios
  Feeling: confiable, profesional, enfocado

  Ejemplo: Notion, Linear, Vercel

FINTECH / BANKING (verde oscuro o azul navy):
  Primary: Emerald (#059669) o Navy (#1E3A5F)
  Neutral: Cool grays puros
  Accent: Gold o Amber para valores positivos
  Feeling: seguridad, confianza, precisión

  Ejemplo: Stripe, Brex, Mercury

CREATIVE / DESIGN TOOLS (colores más saturados):
  Primary: Violet (#7C3AED) o Hot Pink (#DB2777)
  Neutral: Zinc o Stone (grays cálidos)
  Accent: Cyan o Teal para contraste
  Feeling: creativo, energético, expresivo

  Ejemplo: Figma, Adobe

HEALTHTECH / WELLNESS (verde o teal):
  Primary: Teal (#0D9488) o Emerald suave
  Neutral: Warm grays con hint verde
  Accent: Soft blue o lavender
  Feeling: calma, salud, bienestar

ENTERPRISE / B2B (azul + gris neutro):
  Primary: Blue corporativo (#1D4ED8)
  Neutral: Cool grays puros (sin hint de color)
  Accent: Mínimo — solo orange o amber para alertas
  Feeling: serio, institucional, confiable

  Ejemplo: Salesforce, SAP, ServiceNow
```

---

## Colores Neutros — La Parte Más Ignorada

```
Los colores neutros son el 70-80% de cualquier interfaz.
Elegirlos bien es más importante que elegir el color de brand.

TIPOS DE NEUTROS:

Cool grays (Slate, Zinc):
  → Hint de azul-gris
  → Ideales para interfaces tecnológicas, SaaS, dashboards
  → Se combinan bien con primarios azul/violeta/verde

Warm grays (Stone, Neutral):
  → Hint de beige/arena
  → Ideales para editorial, e-commerce, productos artesanales
  → Se combinan bien con primarios naranja/verde/café

True grays (Gray):
  → Completamente neutros
  → Versátiles pero a veces "planos"
  → Funcionan con cualquier color primario

Colored neutrals:
  → Grises con un hint del color primario
  → Ejemplo: si el primario es violeta, los grises tienen un hint de violeta
  → Crea coherencia cromática en toda la interfaz
  → Tailwind no los incluye — generar con oklch o Radix Colors

CÓMO GENERAR:
  oklch(L% C H)  → misma hue (H) que el primario, croma (C) muy bajo
  Ejemplo con primario Blue 600 (oklch 51.8% 0.279 264):
  Neutral-100: oklch(96% 0.01 264)  → casi blanco con hint de azul
  Neutral-200: oklch(92% 0.02 264)
  Neutral-800: oklch(25% 0.03 264)  → casi negro con hint de azul
```

---

## Dark Mode — Más que Invertir Colores

```
Los errores más comunes en dark mode:

❌ Simplemente invertir los colores del light mode
   → Los colores saturados se vuelven agresivos en dark
   → Los fondos negros puros crean halos visuales alrededor del texto

❌ Usar el mismo color primario en ambos modos
   → Un azul 600 sobre blanco: contraste 5.9:1 ✅
   → El mismo azul 600 sobre gris 900: contraste 1.8:1 ❌

✅ Ajustar la saturación y el tono para dark mode
   → Los colores deben ser más suaves y menos saturados
   → El color primario en dark: usar un nivel más claro (400 vs 600 en light)

ESTRATEGIA DE DARK MODE:

Fondos (el stack de grises en dark mode):
  bg-default:  #0A0A0B  (no #000000 — más suave)
  bg-subtle:   #111113
  bg-muted:    #1C1C1F
  bg-raised:   #252528  (para cards sobre el fondo)
  bg-overlay:  #2F2F32  (para modals y popovers)

Texto en dark mode:
  text-primary:   #EDEDEF  (no #FFFFFF)
  text-secondary: #A0A0AB
  text-tertiary:  #6B6B76
  text-disabled:  #4A4A55

Borders en dark mode:
  border-subtle: #27272A
  border-default: #3F3F46
  border-strong:  #52525B

Colores de acción en dark mode (más claros que en light):
  primary:       use color-400 or color-300 (not color-600)
  primary-hover: use color-300 (lighter)
  success:       green-400 (not green-600)
  error:         red-400 (not red-600)
```

---

## Gradientes Modernos

```
Principio atemporal: el gradiente funciona cuando es sutil y armónico.
(Contexto de moda y vigencia → trends-watch.md)

GRADIENTES SUTILES (los que funcionan):

Mesh gradients:
  → Múltiples colores que se mezclan orgánicamente
  → Efecto de "luz sobre superficies"
  → No rectangulares ni lineales
  background: radial-gradient(at 40% 20%, #6366f1 0px, transparent 50%),
              radial-gradient(at 80% 0%, #8b5cf6 0px, transparent 50%),
              radial-gradient(at 0% 50%, #3b82f6 0px, transparent 50%);

Color-to-color sutiles:
  → Del mismo color, diferente luminosidad
  → O colores análogos en la rueda de color
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);

Noise texture sobre gradiente:
  → El grano añade profundidad y evita el "banding"
  → Se implementa con SVG filter o PNG overlay

Glassmorphism bien hecho:
  background: rgba(255, 255, 255, 0.08);
  backdrop-filter: blur(12px);
  border: 1px solid rgba(255, 255, 255, 0.12);
  → Sutil, no exagerado
  → Solo cuando hay contenido de alto contraste detrás

GRADIENTES QUE SE VEN ANTICUADOS:
  → Degradado de un color saturado a blanco
  → Gradientes de múltiples colores muy saturados sin relación armónica
  → Gradientes en texto (aceptable en headlines muy grandes, raramente)
```

---

## Accesibilidad del Color

```
Más allá del contraste:

COLOR BLINDNESS (8% de los hombres, 0.5% de las mujeres):
  → Nunca usar SOLO el color para comunicar estado
  → Error: no solo rojo — usar ícono + texto + rojo
  → Success: no solo verde — usar ícono + texto + verde
  → Si el diseño solo funciona en color = accesibilidad rota

Protanopia (sin rojo):
  → Rojo y verde se confunden
  → Usar íconos distintos para success (checkmark) vs error (X)

Deuteranopia (sin verde):
  → Verde y rojo se confunden
  → Misma solución que protanopia

Acromatopsia (sin color):
  → El diseño debe funcionar en escala de grises
  → Verificar con Chrome DevTools → Rendering → Emulate vision deficiency

Herramientas:
  → Color Oracle: simula daltonismo en toda la pantalla
  → Chrome DevTools: Rendering → Emulate vision deficiency
  → Stark (Figma plugin): checks de contraste y simulación de daltonismo
```

---

## Plantilla portable — paletas sin imagen

```
Sin imagen, generar esta representación en markdown (tabla o ASCII;
no depender de herramientas de visualización externas al editor):

Paleta completa con escala:
  ■ 50  #EFF6FF  ░ texto sobre: #111827 ✅ ratio 18.4:1
  ■ 100 #DBEAFE  ░ texto sobre: #111827 ✅ ratio 16.1:1
  ...
  ■ 600 #2563EB  ■ texto sobre: #FFFFFF ✅ ratio 5.9:1 (AA)
  ■ 700 #1D4ED8  ■ texto sobre: #FFFFFF ✅ ratio 7.8:1 (AAA)

Paleta light/dark comparativa:
  [Light]                  [Dark]
  bg: #FFFFFF              bg: #0A0A0B
  text: #111827            text: #EDEDEF
  primary: #2563EB(600)    primary: #93C5FD(300)

Paletas por industria/estilo:
  → "SaaS profesional": Indigo/Slate
  → "Fintech": Emerald/Gray
  → "Creative": Violet/Stone

Uso:
"genera una paleta de color para [industria/estilo]"
"genera la versión dark mode de esta paleta: [descripción]"
"muéstrame gradientes modernos para [contexto]"
```
