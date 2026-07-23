# Opus Implementation Checklist

Checklist operativa para ejecutar el plan sin volver a mezclar legacy ni noticias.

## 0. Freeze de alcance

- [ ] Confirmar que no habrá noticias en la home.
- [ ] Confirmar que no se toca la otra arquitectura de vistas.
- [ ] Confirmar que todo lo nuevo parte de mock data hasta integrar backend.

## 1. Miniatura social

- [ ] Construir la miniatura pixel por pixel con lógica pura de diseño.
- [ ] Usar solo mock data.
- [ ] No consumir controllers ni servicios de backend para la miniatura.
- [ ] Mostrar título, territorio, total, líder y top 5.
- [ ] Mantenerla lista para WhatsApp, X y LinkedIn.

## 2. Home principal

- [ ] Mostrar encuesta activa actual.
- [ ] Mostrar ranking visible.
- [ ] Mostrar votos acumulados.
- [ ] Mostrar CTA de compartir.
- [ ] Mostrar acceso al detalle territorial.
- [ ] Definir estado vacío limpio.

## 3. Página de territorio

- [ ] Mostrar breadcrumb simple.
- [ ] Mostrar estado de la ronda.
- [ ] Mostrar ranking y votos por opción.
- [ ] Mostrar total de votos.
- [ ] Mostrar bloque de compartir.

## 4. Header

- [ ] Mantener solo marca, inicio y acceso a encuesta activa.
- [ ] Dejar fuera estudios de campo.
- [ ] Dejar fuera noticias.
- [ ] Dejar fuera enlaces legacy.

## 5. Footer

- [ ] Mantener solo marca, enlaces legales, correcciones y contacto básico.
- [ ] Eliminar navegación duplicada.
- [ ] Eliminar rutas muertas.

## 6. Accesibilidad

- [ ] Un `main` y un `h1` por página.
- [ ] Focus visible.
- [ ] Contraste suficiente.
- [ ] Alt útil en imágenes.
- [ ] Navegación por teclado.
- [ ] Responsive estable.

## 7. Limpieza técnica

- [ ] Mantener controllers delgados.
- [ ] Mantener servicios de aplicación pequeños.
- [ ] Usar factories/presenters para cards y share text.
- [ ] Evitar lógica de negocio en vistas.

## 8. Corte de legacy

- [ ] Revisar rutas huérfanas.
- [ ] Revisar vistas huérfanas.
- [ ] Revisar controllers huérfanos.
- [ ] Eliminar solo lo que ya no participa del flujo actual.

## 9. Validación

- [ ] Correr `php artisan test`.
- [ ] Verificar home en local.
- [ ] Verificar página de territorio en local.
- [ ] Verificar que el share no use imagen aleatoria.

