# Risk Patterns — Catálogo de Riesgos en Proyectos Software

Riesgos documentados con probabilidad base, impacto, señales de alerta y mitigación.
Probabilidad base asume equipo competente y proceso normal. Ajustar según contexto.

---

## RT — Riesgos Técnicos

### RT-01: Deuda Técnica Heredada
**Probabilidad base:** Alta (70%) si hay sistema existente
**Impacto:** Alto — multiplica tiempo estimado x1.3 a x2.5
**Señales:** Código sin documentar, sin tests, tecnología end-of-life, arquitectura monolítica sin separación
**Mitigación:**
- Auditoría técnica del sistema existente antes de estimar
- Presupuesto explícito para refactoring (nunca "lo arreglamos mientras")
- Estrategia strangler fig para migraciones grandes

### RT-02: Integraciones con APIs de Terceros
**Probabilidad base:** Media (45%)
**Impacto:** Alto si es integración crítica, Medio si es periférica
**Señales:** API no documentada, soporte del proveedor lento, historial de downtime, pricing variable por uso
**Mitigación:**
- Proof of concept de integración en fase 0, antes de comprometer arquitectura
- Abstraction layer entre sistema y API externa (facilita sustitución)
- Plan B documentado si la API deja de funcionar o cambia precios

### RT-03: Escalabilidad No Planificada
**Probabilidad base:** Media (40%) en proyectos con crecimiento esperado
**Impacto:** Alto — refactoring de arquitectura post-lanzamiento es extremadamente caro
**Señales:** Sin análisis de carga, sin pruebas de performance, base de datos sin índices planificados
**Mitigación:**
- Load testing en staging antes de lanzar
- Arquitectura que permita escalar horizontalmente desde el inicio
- Monitoreo de performance desde semana 1 en producción

### RT-04: Dependencias de Paquetes
**Probabilidad base:** Media (35%)
**Impacto:** Medio — puede bloquear actualizaciones de seguridad o forzar refactoring
**Señales:** Dependencias con versiones fijas muy viejas, paquetes abandonados, conflictos de licencias
**Mitigación:**
- Auditoría de dependencias al inicio
- Política de actualización de dependencias (mensual/trimestral)
- Preferir librerías con comunidad activa y mantenedor corporativo

### RT-05: Seguridad — Vulnerabilidades No Detectadas
**Probabilidad base:** Alta (65%) sin proceso de seguridad explícito
**Impacto:** Crítico — breach de datos puede ser terminal para el negocio
**Señales:** Sin revisiones de seguridad planificadas, credenciales en código, sin HTTPS, sin validación de inputs
**Mitigación:**
- OWASP Top 10 como checklist obligatorio en cada módulo
- SAST (análisis estático) en CI/CD pipeline
- Penetration testing antes de lanzar si hay datos sensibles
- Secrets management (nunca credenciales en repositorio)

### RT-06: Performance de Base de Datos
**Probabilidad base:** Media (40%) en proyectos con volumen de datos no analizado
**Impacto:** Alto — queries lentas en producción son visibles para usuarios y difíciles de esconder
**Señales:** Sin modelado de datos previo, sin análisis de consultas frecuentes, sin estrategia de índices
**Mitigación:**
- Query analysis en staging con datos representativos
- Índices planificados desde el modelo de datos inicial
- Estrategia de caché para queries frecuentes

---

## RR — Riesgos de Requerimientos

### RR-01: Ambigüedad en Requerimientos
**Probabilidad base:** Alta (75%) sin proceso formal de levantamiento
**Impacto:** Alto — implementar lo que no se pidió es retrabajo puro
**Señales:** Requerimientos en lenguaje natural sin criterios de aceptación, sin wireframes, sin ejemplos
**Mitigación:**
- Criterios de aceptación verificables para cada historia
- Prototipos navegables aprobados antes de desarrollo
- Técnica "show don't tell": demos frecuentes, no reportes de avance

### RR-02: Scope Creep Progresivo
**Probabilidad base:** Alta (80%) sin control formal de cambios
**Impacto:** Alto — proyectos pueden crecer 40-100% en scope sin actualizar estimaciones
**Señales:** Ver patrones SC-01 a SC-04 en client-behavior.md
**Mitigación:**
- Proceso de change request con análisis de impacto obligatorio
- Backlog público y priorizado
- Regla: cada adición desplaza algo o tiene costo adicional

### RR-03: Requerimientos Contradictorios
**Probabilidad base:** Media (45%) en empresas con múltiples stakeholders
**Impacto:** Medio — paraliza decisiones y fuerza retrabajo
**Señales:** Múltiples decisores con prioridades distintas, requerimientos que se anulan entre sí
**Mitigación:**
- Sesión de priorización con todos los stakeholders antes de desarrollo
- DRI (decisor único) para conflictos sin resolver
- Documentar el conflicto y la resolución elegida

### RR-04: Requerimientos que Cambian la Arquitectura
**Probabilidad base:** Baja (20%) si el discovery fue completo; Alta (60%) si fue apresurado
**Impacto:** Crítico — cambios arquitectónicos post-desarrollo son los más costosos
**Señales:** Discovery incompleto, cliente que "descubre" necesidades durante desarrollo
**Mitigación:**
- Discovery exhaustivo antes de arquitectura
- Documentar suposiciones arquitectónicas y validarlas con cliente
- Arquitectura modular que permita cambios en componentes sin afectar todo el sistema

---

## RE — Riesgos de Equipo

### RE-01: Rotación de Personal Clave
**Probabilidad base:** Media (30%) en proyectos de más de 6 meses
**Impacto:** Alto — pérdida de conocimiento implícito, curva de onboarding de nuevo miembro
**Señales:** Equipo con un solo "experto" en componentes críticos, documentación inexistente
**Mitigación:**
- Knowledge sharing: sesiones técnicas regulares, pair programming
- Documentación de arquitectura actualizada (no opcional)
- Bus factor ≥ 2 para componentes críticos

### RE-02: Curva de Aprendizaje en Stack
**Probabilidad base:** Alta (65%) con tecnología nueva para el equipo
**Impacto:** Medio — multiplica tiempo estimado x1.2 a x1.5 en primeras semanas
**Señales:** Equipo nunca usó el framework/lenguaje en producción, solo en tutoriales
**Mitigación:**
- Spike técnico (prototipo de prueba) en fase 0 antes de comprometer
- Factor de ajuste x1.3 en estimaciones para módulos con tecnología nueva
- Capacitación dirigida antes de iniciar, no durante desarrollo

### RE-03: Estimaciones Optimistas
**Probabilidad base:** Alta (70%) sin metodología formal de estimación
**Impacto:** Alto — presión de tiempo constante, calidad comprometida, burnout
**Señales:** Estimaciones puntuales sin rangos, sin buffer, presión a dar "fechas concretas"
**Mitigación:**
- Estimación PERT obligatoria (ver SKILL.md Fase 4)
- Buffer técnico del 15-20% no negociable
- Historial de velocidad real vs. estimada para calibrar futuras estimaciones

### RE-04: Deuda de Comunicación
**Probabilidad base:** Media (40%) en equipos distribuidos o nuevos
**Impacto:** Medio — trabajo duplicado, decisiones inconsistentes, bugs de integración
**Señales:** Sin reuniones de sync, sin documentación de decisiones, silos entre frontend/backend
**Mitigación:**
- Daily standup (15 min max) con bloqueos explícitos
- Decisiones técnicas documentadas en ADRs
- Code review cruzado entre frontend y backend en integraciones

---

## RN — Riesgos de Negocio

### RN-01: Cambio de Prioridades del Cliente
**Probabilidad base:** Media (40%) en empresas con alta volatilidad estratégica
**Impacto:** Alto — puede pausar o cancelar proyecto mid-execution
**Señales:** Cliente en proceso de fundraising, M&A, reestructuración, cambio de liderazgo
**Mitigación:**
- Entregas incrementales con valor standalone
- Contratos por fase, no por proyecto completo
- MVP en primera fase protege inversión si hay pivot

### RN-02: Modelo de Negocio No Validado
**Probabilidad base:** Alta (60%) en startups sin tracción previa
**Impacto:** Crítico — producto técnicamente correcto sin mercado que lo use
**Señales:** Sin usuarios piloto identificados, sin validación de disposición a pagar, sin métricas de éxito definidas
**Mitigación:**
- Recomendar validación de mercado antes o en paralelo al desarrollo
- MVP orientado a validar hipótesis de negocio, no a lanzar producto completo
- Métricas de éxito definidas antes de escribir código

### RN-03: Competidor Lanza Primero
**Probabilidad base:** Variable según mercado
**Impacto:** Medio a Alto — afecta urgencia y posicionamiento, no necesariamente viabilidad
**Señales:** Mercado activo con varios actores, timing del cliente claramente reactivo
**Mitigación:**
- Diferenciadores técnicos que no dependan de ser primero
- MVP que valide nicho específico donde hay ventaja real
- Velocidad de iteración post-lanzamiento como ventaja competitiva

---

## RX — Riesgos Externos

### RX-01: Dependencia de Proveedor Cloud
**Probabilidad base:** Baja (15%) — pero catastrófico si ocurre sin plan
**Impacto:** Crítico si no hay redundancia
**Señales:** Toda la arquitectura en un solo proveedor sin estrategia de salida
**Mitigación:**
- Abstraction layer sobre servicios cloud específicos
- Backup en proveedor alternativo para datos críticos
- RTO y RPO definidos y testeados

### RX-02: Cambios Regulatorios
**Probabilidad base:** Baja (10-30% según industria)
**Impacto:** Alto en fintech, salud, educación, datos personales
**Señales:** Proyecto en industria regulada sin asesoría legal, GDPR/PCI/HIPAA no considerados desde el inicio
**Mitigación:**
- Consulta legal antes de arquitectura en industrias reguladas
- Diseño de privacidad desde el inicio (Privacy by Design)
- Arquitectura que permita adaptar a cambios regulatorios sin reescribir

### RX-03: Obsolescencia de Dependencias
**Probabilidad base:** Media (35%) en proyectos de más de 2 años
**Impacto:** Medio — fuerza actualizaciones no planificadas o vulnerabilidades sin parche
**Señales:** Stack con versiones end-of-life, sin política de actualización
**Mitigación:**
- Dependabot o equivalente en CI/CD
- Revisión trimestral de versiones críticas
- Plan de migración para versiones LTS antes de que lleguen a EOL

---

## Matriz de Priorización de Riesgos

Calcular Exposición = Probabilidad × Impacto

| Exposición | Acción requerida |
|-----------|------------------|
| Alta × Alto = Crítico | Plan de mitigación obligatorio antes de iniciar |
| Alta × Medio = Alto | Plan de mitigación en fase 1 |
| Media × Alto = Alto | Monitoreo activo + plan de contingencia |
| Media × Medio = Medio | Monitoreo periódico |
| Baja × cualquiera = Bajo | Documentar y revisar trimestralmente |

---

## Checklist de Riesgos por Tipo de Proyecto

### Startup / Producto Nuevo
- [ ] RN-02 Modelo de negocio no validado
- [ ] RR-01 Ambigüedad en requerimientos
- [ ] PR-02 Presupuesto irreal (client-behavior.md)
- [ ] TL-01 Fecha mágica de lanzamiento (client-behavior.md)

### Sistema Empresarial / Enterprise
- [ ] RT-01 Deuda técnica heredada
- [ ] RT-02 Integraciones con sistemas legado
- [ ] RX-02 Cambios regulatorios
- [ ] TD-01 Cliente comité (client-behavior.md)

### App Mobile
- [ ] RT-02 APIs de terceros (push, pagos, mapas)
- [ ] RE-02 Curva de aprendizaje en plataformas nativas
- [ ] RR-04 Requerimientos que cambian arquitectura
- [ ] VP-02 Rechazo del MVP (client-behavior.md)

### E-commerce / Pagos
- [ ] RT-05 Seguridad — crítico con datos de pago
- [ ] RT-02 Integración con gateway de pagos
- [ ] RX-02 PCI DSS compliance
- [ ] RT-06 Performance de BD bajo carga de ventas
