# Frameworks de Admin: Filament, shadcn/ui, CoreUI

> **Datos con caducidad — revisar.** Las versiones, APIs y popularidad relativa
> de los frameworks de este archivo cambian rápido. Antes de recomendar o
> escribir código, verificar versión actual y docs oficiales de Filament,
> shadcn/ui, TanStack Table, CoreUI y PrimeVue.

## Cómo Elegir el Framework Correcto

```
La elección depende del stack técnico del proyecto:

Stack PHP/Laravel    → Filament (la opción obvia)
Stack React/Next.js  → shadcn/ui + TanStack Table
Stack cualquiera     → CoreUI (tiene versiones para múltiples frameworks)
Stack Vue.js         → Naive UI o PrimeVue
Stack Angular        → Angular Material o PrimeNG
Stack Flutter        → No hay "admin framework" → construir con tokens propios

Factores adicionales:
  Velocidad de desarrollo:     Filament > CoreUI > shadcn (más opinado = más rápido)
  Flexibilidad:                shadcn > CoreUI > Filament (menos opinado = más flexible)
  Look and feel personalizado: shadcn > CoreUI > Filament
  Curva de aprendizaje:        shadcn < Filament < CoreUI (paradoja de la flexibilidad)
```

---

## Filament — El Admin de Laravel

```
Filament es el framework de admin más completo para Laravel.
Construido sobre Livewire, Alpine.js y Tailwind CSS.
Genera interfaces de admin completas con muy poco código.

QUÉ INCLUYE OUT OF THE BOX:
  → Recursos CRUD completos (tablas + formularios + vistas detalle)
  → Navegación con sidebar automática
  → Filtros, búsqueda, ordenamiento en tablas
  → Relaciones (BelongsTo, HasMany, BelongsToMany)
  → Widgets de dashboard (charts, KPIs, tablas)
  → Autenticación
  → Multi-tenancy
  → Exportación de tablas

ESTRUCTURA DE UN RESOURCE:
  app/Filament/Resources/
  └── UserResource/
      ├── UserResource.php    → Configuración principal del resource
      └── Pages/
          ├── ListUsers.php   → Tabla de listado
          ├── CreateUser.php  → Formulario de creación
          └── EditUser.php    → Formulario de edición

DEFINIR UNA TABLA:
  public static function table(Table $table): Table
  {
      return $table
          ->columns([
              ImageColumn::make('avatar')->circular(),
              TextColumn::make('name')->searchable()->sortable(),
              TextColumn::make('email')->searchable(),
              BadgeColumn::make('status')
                  ->colors(['success' => 'active', 'danger' => 'banned']),
              TextColumn::make('created_at')->dateTime()->sortable(),
          ])
          ->filters([
              SelectFilter::make('status')
                  ->options(['active' => 'Active', 'banned' => 'Banned']),
              TrashedFilter::make(),
          ])
          ->actions([
              ViewAction::make(),
              EditAction::make(),
              DeleteAction::make(),
          ])
          ->bulkActions([
              DeleteBulkAction::make(),
              ExportBulkAction::make(),
          ]);
  }

DEFINIR UN FORMULARIO:
  public static function form(Form $form): Form
  {
      return $form
          ->schema([
              Section::make('Información Personal')
                  ->columns(2)
                  ->schema([
                      TextInput::make('name')->required()->maxLength(255),
                      TextInput::make('email')->email()->required()->unique(),
                      Select::make('role')
                          ->relationship('roles', 'name')
                          ->multiple()
                          ->preload(),
                  ]),
              Section::make('Configuración')
                  ->schema([
                      Toggle::make('is_active')->default(true),
                      FileUpload::make('avatar')->image()->avatar(),
                  ]),
          ]);
  }

WIDGETS DE DASHBOARD:
  class StatsOverview extends BaseWidget
  {
      protected function getStats(): array
      {
          return [
              Stat::make('Total Users', User::count())
                  ->description('12% increase')
                  ->descriptionIcon('heroicon-m-arrow-trending-up')
                  ->color('success'),
              Stat::make('Active Subscriptions', Subscription::active()->count()),
              Stat::make('MRR', '$' . number_format($mrr, 2)),
          ];
      }
  }

PERSONALIZACIÓN DE ESTILOS:
  Filament usa Tailwind CSS — personalizar en tailwind.config.js
  El color primario se configura en filament.php:
  'primary' => Color::Indigo,  // o cualquier color de Tailwind
```

---

## shadcn/ui — El Admin de React/Next.js

```
shadcn/ui no es una librería de componentes — es una colección de
componentes que se copian al proyecto y se modifican libremente.
Construido sobre Radix UI (accesibilidad) y Tailwind CSS.

POR QUÉ ES DIFERENTE A OTRAS LIBRERÍAS:
  → Los componentes son TUYOS — están en tu codebase
  → Puedes modificar cualquier cosa sin luchar contra la librería
  → No hay un paquete de npm que actualizar que rompa estilos
  → La accesibilidad está incluida (via Radix UI primitives)

PARA ADMIN/DASHBOARD: shadcn/ui + TanStack Table es el stack dominante en React
(dato con caducidad — revisar vigencia)

INSTALAR UN COMPONENTE:
  npx shadcn@latest add button table dialog select
  → El componente se copia a src/components/ui/

TABLA CON TANSTACK TABLE + SHADCN:
  // Definir las columnas
  const columns: ColumnDef<User>[] = [
    {
      id: 'select',
      header: ({ table }) => (
        <Checkbox
          checked={table.getIsAllPageRowsSelected()}
          onCheckedChange={(v) => table.toggleAllPageRowsSelected(!!v)}
          aria-label="Select all"
        />
      ),
      cell: ({ row }) => (
        <Checkbox
          checked={row.getIsSelected()}
          onCheckedChange={(v) => row.toggleSelected(!!v)}
        />
      ),
    },
    {
      accessorKey: 'name',
      header: ({ column }) => (
        <Button
          variant="ghost"
          onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}
        >
          Name <ArrowUpDown className="ml-2 h-4 w-4" />
        </Button>
      ),
    },
    {
      accessorKey: 'status',
      header: 'Status',
      cell: ({ row }) => (
        <Badge variant={row.original.status === 'active' ? 'default' : 'secondary'}>
          {row.original.status}
        </Badge>
      ),
    },
    {
      id: 'actions',
      cell: ({ row }) => <UserActionsMenu user={row.original} />,
    },
  ];

COMPONENTES DE ADMIN MÁS USADOS DE SHADCN:
  Table                → tablas básicas
  DataTable (custom)   → tabla completa con sorting/filtering/pagination
  Dialog               → modales de confirmación y formularios
  Sheet                → panel lateral para formularios
  Select, Combobox     → selects con búsqueda
  Command              → paleta de comandos / búsqueda global (Cmd+K)
  Calendar, DatePicker → selección de fechas
  Form + React Hook Form → formularios con validación

DASHBOARD CON TREMOR (encima de shadcn):
  import { Card, Metric, Text, AreaChart, DonutChart } from '@tremor/react';

  <Card>
    <Text>Sales</Text>
    <Metric>$48,295</Metric>
    <AreaChart data={salesData} index="date" categories={['Sales']} />
  </Card>
```

---

## CoreUI — El Admin Multi-Framework

```
CoreUI ofrece un template de admin completo para múltiples frameworks.
Es más "batería incluida" que shadcn — viene con el layout y componentes listos.

VERSIONES DISPONIBLES:
  CoreUI for Bootstrap   → HTML/JS puro o con framework
  CoreUI for React       → Components for React
  CoreUI for Vue         → Components for Vue.js
  CoreUI for Angular     → Components for Angular
  CoreUI for Laravel     → Blade templates + Alpine.js

CUÁNDO USAR COREUI:
  → Equipo con poca experiencia en diseño que necesita un resultado visual correcto
  → Prototipado rápido de admin sin mucho customización
  → Proyectos donde el look-and-feel estándar de bootstrap es aceptable
  → Proyecto legacy ya en Bootstrap que necesita un admin

ESTRUCTURA DEL TEMPLATE:
  src/
  ├── layout/
  │   ├── DefaultLayout.js    → Sidebar + topbar + content
  │   ├── Sidebar.js
  │   └── Header.js
  ├── views/
  │   ├── dashboard/
  │   ├── base/               → Componentes base (tables, forms, etc.)
  │   └── pages/
  └── _nav.js                 → Configuración de la navegación

CUSTOMIZACIÓN EN COREUI:
  → Sobrescribir variables SCSS: $primary, $sidebar-width, $header-height
  → Usar el sistema de CSS variables para customización dinámica
  → Crear componentes custom que sigan el mismo sistema de clases
```

---

## Comparativa Final para Decidir

```
DECIDE FILAMENT SI:
  ✅ Tu stack es Laravel/PHP
  ✅ Necesitas un admin funcional rápidamente
  ✅ El 80% del admin son CRUDs de recursos
  ✅ Quieres autenticación, roles, permisos out of the box
  ⚠️  Cuidado: muy opinionado, customización profunda puede ser difícil

DECIDE SHADCN/UI + TANSTACK SI:
  ✅ Tu stack es React/Next.js
  ✅ Necesitas alta customización visual
  ✅ El admin tiene flujos complejos que no encajan en CRUD estándar
  ✅ El equipo ya conoce React
  ⚠️  Cuidado: hay que construir más desde cero (tabla, filtros, etc.)

DECIDE COREUI SI:
  ✅ Necesitas soporte para múltiples frameworks
  ✅ El equipo viene de Bootstrap
  ✅ El admin tiene requisitos estándar
  ✅ Quieres un template ya estructurado
  ⚠️  Cuidado: el look-and-feel es reconocible como "CoreUI" — difícil customizar completamente

CONSTRUIR DESDE CERO (con design system propio) SI:
  ✅ El admin tiene requisitos muy específicos que ningún framework cubre
  ✅ El diseño debe ser exactamente igual al producto principal
  ✅ Hay tiempo y recursos para construir los componentes
  ⚠️  Cuidado: es la opción más costosa en tiempo inicial
```
