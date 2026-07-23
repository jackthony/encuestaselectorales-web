# CSV estándar para encuestas electorales

## Archivos principales

- `catalogo_candidaturas_erm2026.csv`: catálogo ya poblado con 30 candidaturas.
- `plantilla_candidaturas_estandar.csv`: misma cabecera, sin registros, para importar futuros distritos/provincias/regiones.
- `plantilla_resultados_encuesta.csv`: resultados variables de encuestas, simulaciones o conteos oficiales.
- `diccionario_datos_electorales.csv`: definición y validación de cada columna.
- `schema_mysql_encuestas_electorales.sql`: modelo normalizado.
- `catalogo_candidaturas_erm2026.json`: espejo JSON.
- `PROMPT_CODEX_LARAVEL_IMPORTADOR.md`: instrucciones para que Codex implemente el importador.

## Mapeo de las imágenes

- `party_logo_url` is the party logo column used by the public UI.
- `candidate_photo_url` is the candidate photo column used by the public UI.
- `legacy_party_logo_url` is archival/reference only; do not render it in the public UI.

## Regla territorial

- `REGIONAL`: región obligatoria; provincia y distrito vacíos.
- `PROVINCIAL`: región y provincia obligatorias; distrito vacío.
- `DISTRITAL`: región, provincia y distrito obligatorios.
- Los códigos UBIGEO pueden llegar vacíos y completarse después.
- Los campos vacíos deben convertirse a `NULL` al importar.

## Claves de upsert

Usa siempre estas claves para evitar duplicados:

- `scope_uid`
- `organization_uid`
- `candidate_uid`
- `candidacy_uid`

No uses el número de fila ni el ID autoincremental de MySQL como identificador entre archivos.

## Flujo sugerido en Laravel

1. Subir el CSV a `storage/app/imports` mediante FTP o el administrador de archivos.
2. Ejecutar un comando Artisan de importación si existe acceso a terminal/SSH.
3. Cuando no haya terminal, ejecutar la importación desde un panel administrativo protegido.
4. Procesar en lotes de 250 a 500 filas.
5. Usar una transacción por lote.
6. Hacer `upsert`, no inserciones ciegas.
7. Registrar errores por fila sin detener todo el archivo.
8. Mantener catálogo y resultados en tablas distintas.

## Imágenes

En una primera versión se pueden mostrar las URLs remotas.

Luego conviene descargar las imágenes y llenar:

- `party_logo_local_path`
- `candidate_photo_local_path`

El frontend debe usar:

```php
$photo = $candidate->candidate_photo_local_path
    ?: $candidate->candidate_photo_url;

$logo = $organization->party_logo_local_path
    ?: $organization->party_logo_url;
```

## Estado actual

- Regional Callao: 8 registros, todos con foto de candidato.
- Provincial Lima: 22 registros, sin foto de candidato en el archivo recibido.
- Distrital: la plantilla está preparada; todavía no se incorporó un archivo distrital.
