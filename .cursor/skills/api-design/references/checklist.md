# Checklist de diseño de API

Antes de publicar un endpoint:

- [ ] URL en plural, sustantivo, sin verbos
- [ ] HTTP method semánticamente correcto
- [ ] Status code apropiado para cada caso (éxito y errores)
- [ ] Estructura de respuesta consistente con el resto de la API
- [ ] Errores de validación con campo específico (no mensaje genérico)
- [ ] Paginación en todos los endpoints de listado
- [ ] Filtros y ordenamiento documentados
- [ ] Autenticación requerida donde corresponde
- [ ] Rate limiting configurado
- [ ] Versionado en URL (/v1/)
- [ ] Documentación OpenAPI actualizada
- [ ] Tests de integración cubriendo happy path + errores principales
- [ ] No exponer IDs internos en API pública (usar UUID o ULID)
- [ ] CORS configurado correctamente
- [ ] Campos sensibles excluidos de respuestas (passwords, tokens internos)
