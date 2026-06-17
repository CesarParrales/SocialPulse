# Definición de Scope y Criterios de Aceptación

## Por Qué el Scope es la Parte Más Importante

```
El precio de un proyecto puede estar mal calculado en un 20%
y aún ser un proyecto rentable con buena gestión.

El scope mal definido puede destruir la rentabilidad completamente,
porque no tiene límite — puede crecer indefinidamente.

"El scope es el mapa. Sin mapa, cualquier camino lleva al destino
que el cliente imagine, no al que acordaron."

Los dos tipos de problemas de scope:

SCOPE AMBIGUO (el más común):
  "Un módulo de reportes"
  → ¿Qué reportes? ¿Exportables? ¿Con gráficas? ¿En tiempo real?
  → El dev asume lo mínimo. El cliente imagina lo máximo.

SCOPE AUSENTE (el más peligroso):
  "Todo lo necesario para que funcione el sistema"
  → El estudio nunca puede decir "eso no estaba incluido"
  → Cada nueva petición es "parte de lo que acordamos"
```

---

## El Truco del Criterio de Aceptación

```
La diferencia entre "módulo de usuarios" y un scope bien definido:

❌ SCOPE VAGO:
  "Sistema de gestión de usuarios con login, perfiles y permisos."

✅ SCOPE CON CRITERIOS:
  MÓDULO: Gestión de Usuarios

  Incluye:
  ✓ Login con email + contraseña
  ✓ Recuperación de contraseña por email
  ✓ 3 roles: Admin, Supervisor, Operador (permisos predefinidos)
  ✓ Admin puede crear/editar/desactivar usuarios
  ✓ Perfil de usuario: nombre, email, foto, contraseña
  ✓ Sesiones expiran a las 8 horas de inactividad

  No incluye:
  ✗ Login con Google/redes sociales
  ✗ 2FA / doble factor de autenticación
  ✗ Roles personalizados (solo los 3 roles predefinidos)
  ✗ Auditoría de accesos / login history

  Criterio de aceptación:
  → Un Admin puede crear un usuario Operador y ese usuario
    puede iniciar sesión y acceder solo a las secciones de su rol.
  → Un usuario que olvidó su contraseña puede recuperarla por email
    y establecer una nueva en menos de 5 minutos.
  → Aprobado cuando ambos criterios se cumplen en staging.

Por qué funciona:
→ "No incluye 2FA" previene la solicitud de 2FA sin CR
→ El criterio de aceptación define exactamente cuándo está terminado
→ El cliente puede leerlo y decir "no, necesito también X" ANTES de empezar
   (mucho más barato que durante el desarrollo)
```

---

## Cómo Redactar Criterios de Aceptación

```
Formato: el usuario puede [acción] y [resultado observable].

CRITERIOS BIEN ESCRITOS:
✅ "Un operador puede crear un pedido completo (con todos los campos
    requeridos) en menos de 2 minutos sin asistencia."

✅ "Al cambiar el estado de un pedido a 'Aprobado', el cliente recibe
    un email de confirmación en menos de 5 minutos."

✅ "La tabla de pedidos muestra los 20 más recientes por defecto y permite
    filtrar por estado, fecha y cliente de forma simultánea."

CRITERIOS MAL ESCRITOS:
❌ "El sistema debe ser rápido y fácil de usar."
   (¿Qué es rápido? ¿Qué es fácil?)

❌ "Los reportes deben funcionar correctamente."
   (¿Qué reportes? ¿Qué es "correcto"?)

❌ "El módulo de pagos debe estar completo."
   (¿Qué incluye "completo"?)

CRITERIOS PARA PERFORMANCE (cuando aplica):
✅ "La pantalla de listado de pedidos carga en menos de 2 segundos
    con 10,000 registros en la base de datos."

✅ "El sistema soporta 50 usuarios concurrentes sin degradación
    de performance superior al 20%."
```

---

## El Scope Matrix — Herramienta Práctica

```
Antes de escribir la propuesta, llenar esta matriz:

FUNCIONALIDAD          | INCLUIDO | EXCLUIDO | FASE 2 | NOTAS
─────────────────────────────────────────────────────────────────
Login básico           |    ✓     |          |        |
Login Google/SSO       |          |    ✓     |        | CR si se necesita
2FA                    |          |          |   ✓    | Post-lanzamiento
Roles predefinidos (3) |    ✓     |          |        | Admin/Sup/Op
Roles personalizados   |          |    ✓     |        |
Dashboard de KPIs      |    ✓     |          |        | 4 métricas definidas
Dashboard en tiempo real|         |          |   ✓    | WebSockets no incluidos
Export CSV             |    ✓     |          |        | Todos los listados
Export Excel           |          |    ✓     |        | CR disponible
App móvil              |          |    ✓     |   ✓    | Propuesta separada
API pública            |          |    ✓     |        | No en scope
Migración de datos     |          |    ✓     |        | Cotizar por separado
Capacitación equipo    |    ✓     |          |        | Hasta 5 usuarios/4h

Esta matriz se convierte en:
→ Las secciones de "incluye / no incluye" de la propuesta
→ La base para las conversaciones de scope con el cliente
→ La defensa cuando el cliente pide algo "que debería estar incluido"
```

---

## El Discovery como Proyecto Separado

```
Para proyectos grandes o complejos, el scope completo no se puede
definir sin antes hacer un discovery.

¿Cuándo hacer discovery pagado?
→ Cuando el proyecto tiene partes que el cliente no sabe cómo especificar
→ Cuando hay sistemas legacy que integrar y no se conoce su estado
→ Cuando el cliente tiene "una idea general" pero no casos de uso detallados
→ Cuando el proyecto > $30,000 o > 4 meses (el riesgo justifica el discovery)

PROPUESTA DE DISCOVERY (formato):

Alcance del Discovery (2-4 semanas):
  → Entrevistas con stakeholders (quiénes usan el sistema y cómo)
  → Mapeo de procesos actuales (flujos de trabajo existentes)
  → Inventario de sistemas existentes (integraciones necesarias)
  → Definición de requerimientos funcionales y no funcionales
  → Diseño de arquitectura técnica preliminar
  → Prototipos de pantallas clave (wireframes, no diseño final)
  → Estimación detallada del proyecto completo

Entregables del Discovery:
  1. Documento de requerimientos funcionales
  2. Mapa de la arquitectura propuesta
  3. Wireframes de las pantallas principales
  4. Estimación detallada por módulo
  5. Propuesta del proyecto completo (post-discovery)

Inversión del Discovery: $X,XXX
  → Si el cliente contrata el proyecto completo:
    el costo del discovery se descuenta del total.
  → Si decide no continuar: el discovery queda pagado.

Por qué funciona:
→ El estudio cobra por su tiempo de análisis
→ El cliente obtiene un documento valioso aunque no continúe
→ La propuesta del proyecto completo post-discovery es mucho más precisa
→ Elimina el riesgo de una estimación a ciegas
```

---

## Rondas de Revisión — Incluir Siempre

```
Una de las causas más frecuentes de scope creep disfrazado:
el cliente pide "una pequeña corrección más" al diseño o al código
y eso se convierte en un proceso interminable sin fin.

Definir en la propuesta cuántas rondas de revisión están incluidas:

DISEÑO:
  "Se incluyen hasta 2 rondas de revisión por pantalla.
   Una ronda = un conjunto de comentarios enviados por el cliente.
   Revisiones adicionales se cotizan a $XX/hora."

DESARROLLO:
  "El UAT (User Acceptance Testing) incluye 2 semanas para reportar bugs.
   Un bug = comportamiento que difiere del criterio de aceptación acordado.
   Solicitudes de nuevas funcionalidades o cambios de diseño durante el UAT
   son Change Requests y se cotizan por separado."

ENTREGABLES ESCRITOS (reportes, documentación):
  "Se incluyen hasta 2 rondas de revisión por documento.
   Los cambios de dirección de contenido después de la primera revisión
   pueden requerir cotización adicional."

Sin este límite → el cliente puede revisar indefinidamente.
Con este límite → hay un proceso claro y ambas partes lo conocen.
```
