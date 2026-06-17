# README del Proyecto — La Documentación que Realmente Importa

## El README es el Manual de Vuelo del Proyecto

```
Un buen README responde todas las preguntas del día 1 sin ayuda de nadie.
Un mal README hace que el dev nuevo interrumpa al equipo cada 20 minutos.

Test del buen README:
¿Puede alguien que no sabe nada del proyecto levantar el ambiente
y entender qué hace el sistema en menos de 2 horas?

Si la respuesta es "no" → el README está incompleto.
```

---

## Estructura del README de Proyecto

### 1. Descripción del Proyecto (5 oraciones máximo)

```
Responde:
→ ¿Qué hace este sistema?
→ ¿Para quién es?
→ ¿Cuál es el problema que resuelve?
→ ¿Dónde está en producción?

Ejemplo:
"Sistema de gestión de pedidos para Acme Corp. Permite a los operadores
del almacén crear, aprobar y rastrear pedidos en tiempo real.
Reemplaza el proceso manual en Excel que tardaba 4 horas diarias.
Producción: [URL]. Staging: [URL]."

Lo que NO poner:
→ La historia del proyecto
→ Por qué se tomaron decisiones técnicas (eso va en ADRs)
→ Información que cambia frecuentemente (eso va en el wiki o notion)
```

### 2. Stack Tecnológico

```
Solo lo esencial. Sin párrafos de justificación.

Backend:   Laravel / PHP — versiones exactas de composer.json
Frontend:  Next.js / React / TypeScript — versiones exactas de package.json
Database:  PostgreSQL (versión del entorno o docker-compose)
Cache:     Redis
Queue:     Laravel Horizon (Redis)
Deploy:    Kamal / DigitalOcean (ver infra/)
CI/CD:     GitHub Actions

Versiones exactas — no rangos genéricos de skills.
El dev nuevo necesita instalar exactamente lo del repo, no defaults desactualizados.
```

### 3. Setup Local — El Más Importante

```
Este es el paso donde más proyectos fallan.
La sección de setup debe poder ejecutarse de forma lineal,
de arriba a abajo, sin decisiones.

Estructura obligatoria:

PREREQUISITOS (solo los que el dev necesita instalar):
  - PHP 8.3 (via Homebrew: `brew install php@8.3`)
  - Node.js 20 LTS (via nvm: `nvm install 20`)
  - PostgreSQL 16 (via Homebrew: `brew install postgresql@16`)
  - Redis 7 (via Homebrew: `brew install redis`)

  [Si el equipo usa Docker, dar la opción Docker primero]

PASOS DE INSTALACIÓN:
  1. Clonar el repositorio
     git clone [URL] && cd [nombre]

  2. Copiar variables de entorno
     cp .env.example .env
     # Editar .env con los valores del password manager (ver sección de accesos)

  3. Instalar dependencias
     composer install
     npm install

  4. Configurar la base de datos
     php artisan key:generate
     php artisan migrate --seed

  5. Levantar los servicios
     php artisan serve       # terminal 1
     npm run dev             # terminal 2
     php artisan queue:work  # terminal 3 (si hay jobs)

  6. Verificar que funciona
     Abrir http://localhost:8000
     Login con: admin@example.com / password

PROBLEMAS COMUNES (la sección más valiosa):
  "Si el comando X falla con el error Y":
    → Descripción del problema
    → Causa
    → Solución exacta

  Estos problemas se documentan cuando ocurren.
  Cada vez que alguien hace el setup y se traba → documentar la solución.
```

### 4. Arquitectura del Sistema

```
Un diagrama vale más que 500 palabras.
No tiene que ser bonito — tiene que ser claro.

Incluir:
→ Los componentes principales y cómo se comunican
→ Los servicios externos (Stripe, SendGrid, etc.) y para qué se usan
→ El flujo de datos para el caso de uso más importante

Formato mínimo aceptable (texto, no imagen):

  FRONTEND (Next.js)
    ↕ HTTPS
  BACKEND (Laravel API)
    ├── PostgreSQL (datos principales)
    ├── Redis (caché + colas)
    └── Servicios externos:
        ├── Stripe (pagos)
        ├── SendGrid (emails transaccionales)
        └── AWS S3 (storage de archivos)

Si el proyecto tiene módulos complejos:
→ Un diagrama por módulo (no uno gigante de todo)
→ Enlazar desde el diagrama general
```

### 5. Estructura de Directorios

```
Solo lo no obvio. No listar lo estándar del framework.

app/
├── Http/
│   ├── Controllers/       # Controladores thin — solo reciben y responden
│   └── Requests/          # Validación de requests
├── Models/                # Eloquent models con sus relaciones
├── Services/              # Lógica de negocio (una clase por dominio)
│   ├── OrderService.php   # Todo lo relacionado con órdenes
│   └── PaymentService.php # Integración con Stripe
├── Jobs/                  # Jobs de cola — procesamiento async
└── Events/                # Eventos y listeners

database/
├── migrations/            # Cronológicas — NUNCA modificar las ya corridas
└── seeders/               # Datos de prueba realistas

tests/
├── Feature/               # Tests de integración (endpoints HTTP)
└── Unit/                  # Tests unitarios (servicios, lógica de negocio)

Documentar las decisiones de estructura que no son obvias:
→ "Los Services encapsulan toda la lógica de negocio.
   Los Controllers son thin — no tienen lógica, solo llaman al Service."
→ "Los Jobs se crean para cualquier operación que tome > 2 segundos."
```

### 6. Convenciones del Proyecto

```
Lo que el equipo decidió y que el dev nuevo no puede inferir del código.

Commits:
  Conventional commits: feat(module): description
  Ver git-workflow skill para el formato completo.

Branches:
  main → producción
  develop → staging (si existe)
  feat/nombre, fix/nombre, hotfix/nombre

Code style:
  PHP: Laravel Pint (correr: ./vendor/bin/pint antes de commit)
  JS/TS: ESLint + Prettier (correr: npm run lint antes de commit)

Testing:
  Nivel mínimo: tests de integración para todos los endpoints nuevos
  Correr antes de PR: php artisan test / npm test

Nomenclatura en el código:
  Variables y métodos: camelCase (PHP y JS)
  Clases: PascalCase
  Tablas de BD: snake_case plural (orders, order_items)
  Constantes: UPPER_SNAKE_CASE
```

### 7. Flujos de Trabajo Clave

```
Los 3-5 flujos más importantes del sistema explicados en texto.
No es un manual de usuario — es contexto para el dev.

Ejemplo:
"FLUJO DE CREACIÓN DE PEDIDO:
1. El operador crea el pedido en el frontend (POST /api/orders)
2. OrderController@store valida con CreateOrderRequest
3. Llama a OrderService@create que:
   a. Crea el registro en DB
   b. Actualiza el stock (con transacción DB)
   c. Dispara el evento OrderCreated
4. OrderCreated dispara OrderCreatedListener que:
   a. Envía email de confirmación (Job: SendOrderConfirmation)
   b. Notifica al supervisor (Job: NotifySupervisor)
5. Los Jobs se procesan por Horizon en background"

Esto ahorra 2-3 horas al dev nuevo que intenta entender el flujo leyendo el código.
```

### 8. Accesos y Servicios Externos

```
NO poner credenciales en el README. Poner dónde encontrarlas.

Password Manager:
  Todas las credenciales del proyecto están en 1Password / Bitwarden
  bajo el vault "[nombre del proyecto]".
  Solicitar acceso al vault a [nombre del responsable].

Credenciales que necesitas:
  ├── .env local completo (incluye DB, Redis, servicios externos)
  ├── Acceso a staging (URL + credenciales de admin)
  └── [Solo si aplica] Tokens de AWS/GCP para servicios cloud

Servicios del proyecto:
  ├── GitHub: [URL del repo] — solicitar acceso a [responsable]
  ├── Sentry: [URL] — para monitoreo de errores
  ├── Linear/Jira: [URL] — para gestión de tareas
  └── Slack: canal #[nombre-proyecto]

Accesos que toman tiempo (solicitar el día 1):
  → Acceso a la BD de staging: requiere aprobación del CTO
  → AWS console: requiere MFA configurado primero
```

### 9. Cómo Desplegar

```
Sin ambigüedad. El dev nuevo puede necesitar hacer un deploy de emergencia.

Staging:
  git push origin develop
  # El CI/CD hace el deploy automáticamente en ~3 minutos
  # Verificar en GitHub Actions que pasó

Producción:
  Solo el lead puede hacer deploys a producción.
  Si es urgente en fin de semana: [número de teléfono del lead]

Rollback:
  Si algo falla después del deploy:
  1. kamal rollback (o el comando equivalente)
  2. Avisar en #[canal-incidentes]
  3. Abrir issue con lo que pasó
```

---

## Mantener el README Actualizado

```
El README desactualizado es peor que no tener README.
El dev nuevo sigue instrucciones que ya no aplican → pierde tiempo y confianza.

Regla del estudio:
→ Si cambias algo que afecta el setup o la arquitectura → actualiza el README en el mismo PR
→ Si alguien hace el onboarding y encuentra algo incorrecto → issue o PR inmediato

El README es un entregable del proyecto, no documentación opcional.
Si hay presión de tiempo para documentar → documentar el README antes que los tests.
```
