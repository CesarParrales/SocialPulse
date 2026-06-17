# Documentación y Storybook

## Por Qué la Documentación es Parte del Sistema

```
Un design system sin documentación:
→ El desarrollador adivina cómo usar el componente → lo usa mal
→ El diseñador no sabe qué ya existe → reinventa la rueda
→ El onboarding de nuevos miembros tarda semanas
→ Los "¿cómo se usa X?" se responden 10 veces por el mismo owner

Un design system bien documentado:
→ Es autosuficiente — el equipo puede avanzar sin preguntar
→ Reduce el tiempo de desarrollo (el componente ya existe y está explicado)
→ Reduce bugs de UX (el componente ya tiene las reglas de uso)
→ Escala con el equipo sin que el owner sea el cuello de botella
```

---

## Storybook — El Estándar de la Industria

```
Storybook es un entorno de desarrollo para componentes UI.
Funciona como catálogo interactivo del design system.

Qué hace:
→ Renderiza componentes en aislamiento (sin la app completa)
→ Permite explorar todas las variantes y estados
→ Sirve como documentación viva (siempre actualizada)
→ Integra tests de accesibilidad (addon a11y)
→ Integra visual regression testing (Chromatic)
→ Genera documentación de props automáticamente (autodocs)

Setup básico:
npx storybook@latest init
# Detecta el framework (React, Vue, Angular, etc.) automáticamente

Estructura de archivos:
  src/
  └── components/
      └── Button/
          ├── Button.tsx          → el componente
          ├── Button.stories.tsx  → las stories de Storybook
          ├── Button.test.tsx     → los tests
          └── Button.module.css  → los estilos
```

---

## Escribir Stories de Calidad

```typescript
// Button.stories.tsx
import type { Meta, StoryObj } from '@storybook/react';
import { Button } from './Button';

// Metadata del componente
const meta: Meta<typeof Button> = {
  title: 'Primitives/Button',
  component: Button,

  // Documentación automática
  tags: ['autodocs'],

  // Parámetros por defecto
  parameters: {
    layout: 'centered',
    docs: {
      description: {
        component: `
El componente Button es la acción principal de la interfaz.
Usa la variante \`primary\` para la acción más importante de cada sección.
Usa \`secondary\` o \`ghost\` para acciones complementarias.

## Cuándo usar
- Para disparar acciones (guardar, enviar, confirmar)
- Para navegar cuando la acción es el elemento más importante
- NO usar para navegación secundaria (usar Link)
        `,
      },
    },
  },

  // Controles de Storybook para el playground
  argTypes: {
    variant: {
      description: 'El estilo visual del botón',
      control: 'select',
      options: ['primary', 'secondary', 'ghost', 'danger'],
      table: {
        defaultValue: { summary: 'primary' },
      },
    },
    size: {
      description: 'El tamaño del botón',
      control: 'radio',
      options: ['sm', 'md', 'lg'],
      table: {
        defaultValue: { summary: 'md' },
      },
    },
    isLoading: {
      description: 'Muestra spinner y deshabilita el botón',
      control: 'boolean',
    },
    disabled: {
      description: 'Deshabilita el botón',
      control: 'boolean',
    },
  },
};

export default meta;
type Story = StoryObj<typeof Button>;

// Story principal — el estado más común
export const Default: Story = {
  args: {
    children: 'Button',
    variant: 'primary',
    size: 'md',
  },
};

// Todas las variantes en una vista
export const AllVariants: Story = {
  render: () => (
    <div style={{ display: 'flex', gap: '8px' }}>
      <Button variant="primary">Primary</Button>
      <Button variant="secondary">Secondary</Button>
      <Button variant="ghost">Ghost</Button>
      <Button variant="danger">Danger</Button>
    </div>
  ),
};

// Todos los tamaños
export const Sizes: Story = {
  render: () => (
    <div style={{ display: 'flex', gap: '8px', alignItems: 'center' }}>
      <Button size="sm">Small</Button>
      <Button size="md">Medium</Button>
      <Button size="lg">Large</Button>
    </div>
  ),
};

// Estados
export const Loading: Story = {
  args: { children: 'Saving...', isLoading: true },
};

export const Disabled: Story = {
  args: { children: 'Disabled', disabled: true },
};

// Con iconos
export const WithIcons: Story = {
  render: () => (
    <div style={{ display: 'flex', gap: '8px' }}>
      <Button leftIcon={<PlusIcon />}>Create</Button>
      <Button rightIcon={<ArrowRightIcon />}>Continue</Button>
      <Button leftIcon={<TrashIcon />} variant="danger">Delete</Button>
    </div>
  ),
};

// Interacción documentada
export const Interactive: Story = {
  args: { children: 'Click me' },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement);
    await userEvent.click(canvas.getByRole('button'));
  },
};
```

---

## Estructura de Documentación de un Componente

```markdown
# Button

Un botón inicia una acción. Es el elemento interactivo más fundamental del sistema.

## Cuándo usar

✅ Para acciones: guardar, enviar, confirmar, cancelar
✅ Para navegación cuando el destino es el resultado de una acción
✅ Cuando hay una jerarquía clara de acciones en la pantalla

❌ No usar para navegación entre páginas (usar `<Link>`)
❌ No usar `danger` para acciones que no son destructivas
❌ Evitar más de 1 botón `primary` por sección

## Jerarquía de variantes

| Variante  | Cuándo usar |
|-----------|-------------|
| primary   | La acción principal de la pantalla (1 por sección) |
| secondary | Acciones alternativas o complementarias |
| ghost     | Acciones terciarias, en contextos con mucho contenido |
| danger    | Acciones destructivas o irreversibles |

## Accesibilidad

- Siempre incluir texto descriptivo (no solo icono)
- Si hay solo icono, agregar `aria-label`
- El estado `loading` desactiva el botón automáticamente
- El foco es visible en todos los estados

## Props

| Prop | Tipo | Default | Descripción |
|------|------|---------|-------------|
| variant | 'primary' \| 'secondary' \| 'ghost' \| 'danger' | 'primary' | Estilo visual |
| size | 'sm' \| 'md' \| 'lg' | 'md' | Tamaño del botón |
| isLoading | boolean | false | Muestra spinner, deshabilita |
| disabled | boolean | false | Deshabilita el botón |
| leftIcon | ReactNode | — | Icono antes del label |
| rightIcon | ReactNode | — | Icono después del label |
| fullWidth | boolean | false | Ocupa todo el ancho disponible |

## Código de ejemplo

\`\`\`tsx
// Botón primario básico
<Button variant="primary" onClick={handleSave}>
  Save Changes
</Button>

// Con estado de carga
<Button variant="primary" isLoading={isSaving}>
  {isSaving ? 'Saving...' : 'Save Changes'}
</Button>

// Acción destructiva con confirmación
<Button variant="danger" onClick={openConfirmModal}>
  Delete Account
</Button>
\`\`\`
```

---

## Addons Esenciales de Storybook

```bash
# Accesibilidad — valida cada story contra WCAG
npx storybook add @storybook/addon-a11y

# Controles interactivos (ya incluido por defecto)
# @storybook/addon-controls

# Backgrounds — probar en diferentes fondos
# @storybook/addon-backgrounds

# Viewport — probar en diferentes tamaños
# @storybook/addon-viewport

# Interactions — tests interactivos
npx storybook add @storybook/addon-interactions

# Figma — vincular al diseño en Figma
npx storybook add @storybook/addon-designs

# Visual regression testing (Chromatic — servicio de pago)
# Detecta cambios visuales no intencionales entre deploys
```

---

## Documenting Design Tokens en Storybook

```typescript
// stories/tokens/Colors.stories.tsx
export const ColorPalette: Story = {
  render: () => (
    <div>
      <h2>Semantic Colors</h2>
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4, 1fr)', gap: '16px' }}>
        {[
          { name: 'primary', var: '--color-primary' },
          { name: 'success', var: '--color-success' },
          { name: 'error', var: '--color-error' },
          { name: 'warning', var: '--color-warning' },
        ].map(({ name, var: cssVar }) => (
          <div key={name}>
            <div
              style={{
                width: '100%',
                height: '80px',
                backgroundColor: `var(${cssVar})`,
                borderRadius: '8px',
              }}
            />
            <p style={{ margin: '4px 0 0', fontSize: '13px', fontWeight: 500 }}>{name}</p>
            <p style={{ margin: 0, fontSize: '12px', color: '#666' }}>{cssVar}</p>
          </div>
        ))}
      </div>
    </div>
  ),
};
```
