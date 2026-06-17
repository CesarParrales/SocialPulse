# Principios UX gratuitos — checklist de validación

```
Fuente: uxuiprinciples.com/es/principles (acceso free al jun 2026)
Complementa: nielsen-heuristics.md, accessibility.md
Caducidad: revisar cada 90 días — el catálogo y tiers pueden cambiar
```

> Usar como **criterios verificables**, no como opinión estética. En auditorías,
> marcar ✓/✗/N/A por flujo evaluado. Mínimo **3 principios** aplicables al scope.

---

## Checklist por principio (tier gratuito)

| ID | Principio | Pregunta de auditoría | Severidad si falla |
|----|-----------|----------------------|-------------------|
| UXF-01 | Carga cognitiva | ¿Hay ≤7±2 grupos visuales activos por vista sin jerarquía clara? | Alta si form/nav abruma |
| UXF-02 | Consistencia | ¿Mismos patrones para misma acción en todo el scope? | Media–Alta |
| UXF-03 | Ley de Jakob | ¿Patrones reconocibles vs convención del dominio? | Media |
| UXF-04 | Ley de Hick | ¿Cada decisión tiene pocas opciones paralelas visibles? | Media |
| UXF-05 | Divulgación progresiva | ¿Lo avanzado está oculto hasta que se necesita? | Media |
| UXF-06 | Ley de Fitts | ¿Targets táctiles ≥44px y CTAs principales accesibles? | Alta en mobile |
| UXF-07 | Reconocimiento vs recuerdo | ¿Opciones visibles vs memorizar pasos? | Media |
| UXF-08 | Transparencia IA | ¿Features IA muestran límites y no simulan certeza? | Alta si hay IA |
| UXF-09 | Bienestar inclusivo | ¿Evita dark patterns y reduce estrés innecesario? | Media–Alta |

---

## Mapeo a ejes de ui-audit

```
Usabilidad (Nielsen)  ← UXF-02, UXF-03, UXF-04, UXF-05, UXF-07
Accesibilidad (WCAG)  ← UXF-01, UXF-06 (+ accessibility.md)
Consistencia visual   ← UXF-02 (+ visual-consistency.md)
IA / producto         ← UXF-08, UXF-09
```

---

## Plantilla en reporte (audit-report.md)

```markdown
## Validación principios UX (tier gratuito)

| ID | ✓/✗/N/A | Evidencia (pantalla + observación) |
|----|---------|-----------------------------------|
| UXF-01 | | |
| UXF-02 | | |
| ... | | |

Principios evaluados: N/9 · Fallos Alta: N
```

---

## Prompts IA (inspiración para remediación)

Copiar desde uxuiprinciples.com el prompt del principio fallido; adaptar al
producto. No pegar prompts genéricos sin contexto del inventario de pantallas.

Recursos ampliados → `ui-web-modern/references/learning-sources.md`
