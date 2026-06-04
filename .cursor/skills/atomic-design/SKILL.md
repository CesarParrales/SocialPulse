---
name: atomic-design
description: Guides UI component structure using Atomic Design methodology (atoms, molecules, organisms, templates, pages). Use during web design and development, frontend work, admin panels, and apps. Applies to React, Vue, Laravel Blade, and any component-based stack.
---

# Atomic Design

Aplica la metodología Atomic Design para estructurar componentes de interfaz en proyectos web, frontends, admin panels y apps. Funciona con React, Vue, Laravel Blade y cualquier stack basado en componentes.

## Jerarquía

| Nivel | Descripción | Ejemplo |
|-------|-------------|---------|
| **Atoms** | Elementos UI indivisibles, sin lógica de negocio | Button, Input, Label, Icon, Badge |
| **Molecules** | Agrupaciones de atoms con una función clara | SearchBar, FormField, CardHeader |
| **Organisms** | Secciones complejas de múltiples molecules | Header, Sidebar, DataTable, Form |
| **Templates** | Layouts que combinan organisms (estructura, no contenido real) | PageLayout, DashboardLayout, AuthLayout |
| **Pages** | Instancias concretas de templates con datos reales | LoginPage, UsersListPage, DashboardPage |

## Reglas de composición

- **Atoms**: No importan otros componentes del design system. Solo HTML nativo o primitivos.
- **Molecules**: Importan solo atoms (y opcionalmente otras molecules pequeñas).
- **Organisms**: Importan molecules y atoms. Pueden contener lógica de estado.
- **Templates**: Importan organisms. Definen slots/children para contenido variable.
- **Pages**: Importan templates y organismos. Contienen datos, llamadas API y routing.

## Estructura de carpetas sugerida

```
components/
├── atoms/
│   ├── Button/
│   ├── Input/
│   └── Icon/
├── molecules/
│   ├── SearchBar/
│   └── FormField/
├── organisms/
│   ├── Header/
│   ├── Sidebar/
│   └── DataTable/
├── templates/
│   ├── PageLayout/
│   └── DashboardLayout/
└── pages/
    ├── LoginPage/
    └── UsersListPage/
```

En Laravel Blade puede ser `resources/views/components/` con subcarpetas equivalentes.

## Cuándo usar cada nivel

**¿Es un elemento HTML primitivo o un componente UI reutilizable mínimo?** → Atom

**¿Combina 2–3 atoms con una función específica?** → Molecule

**¿Es una sección completa de la UI (header, sidebar, tabla, formulario largo)?** → Organism

**¿Define la estructura de una pantalla sin contenido concreto?** → Template

**¿Es una pantalla específica con datos y comportamiento real?** → Page

## Checklist al crear componentes

- [ ] ¿El componente pertenece al nivel más bajo posible?
- [ ] ¿Las dependencias respetan la jerarquía (atoms ← molecules ← organisms ← templates)?
- [ ] ¿Los atoms son verdaderamente indivisibles?
- [ ] ¿Las molecules tienen una única responsabilidad?
- [ ] ¿Los organisms encapsulan secciones completas y reutilizables?

## Ejemplo rápido (React/Vue)

```
Atom:     <Button variant="primary">Guardar</Button>
Molecule: <FormField label="Email" type="email" required />
Organism: <Header logo={<Logo />} nav={<MainNav />} user={<UserMenu />} />
Template: <PageLayout header={<Header />} sidebar={<Sidebar />}>{children}</PageLayout>
Page:     <DashboardPage /> // Usa DashboardLayout + datos reales
```

## Anti-patrones

- **Atom con lógica de negocio**: Mover a molecule u organism.
- **Organism que importa otro organism completo**: Revisar si uno debería ser molecule o si hay acoplamiento excesivo.
- **Page con layout hardcodeado**: Extraer template.
- **Molecule gigante**: Dividir en molecules más pequeñas o promover a organism.
