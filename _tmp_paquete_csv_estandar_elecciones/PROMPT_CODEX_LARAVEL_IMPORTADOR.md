# Prompt para Codex: importador CSV electoral en Laravel

Analiza primero estos archivos:

- catalogo_candidaturas_erm2026.csv
- plantilla_candidaturas_estandar.csv
- plantilla_resultados_encuesta.csv
- diccionario_datos_electorales.csv
- schema_mysql_encuestas_electorales.sql

Implementa en el proyecto Laravel lo siguiente:

## 1. Base de datos

Crea migraciones equivalentes al esquema SQL para:

- election_scopes
- political_organizations
- candidates
- candidacies
- polls
- poll_results

Usa claves únicas por UID y foreign keys internas por ID.

## 2. Modelos Eloquent

Crea modelos y relaciones:

- ElectionScope hasMany Candidacy
- PoliticalOrganization hasMany Candidacy
- Candidate hasMany Candidacy
- Candidacy belongsTo las tres entidades anteriores
- Poll belongsTo ElectionScope
- Poll hasMany PollResult
- PollResult belongsTo Poll y Candidacy

## 3. Importador de candidaturas

Crea un comando:

php artisan elections:import-candidacies {path}

Requisitos:

- Leer CSV UTF-8 con o sin BOM usando SplFileObject o fgetcsv.
- No agregar una dependencia externa salvo que el proyecto ya la use.
- Convertir celdas vacías a NULL.
- Validar election_level: REGIONAL, PROVINCIAL o DISTRITAL.
- Validar geografía:
  - REGIONAL: province_name y district_name deben ser NULL.
  - PROVINCIAL: province_name obligatorio y district_name NULL.
  - DISTRITAL: province_name y district_name obligatorios.
- Hacer upsert por:
  - scope_uid
  - organization_uid
  - candidate_uid
  - candidacy_uid
- Resolver IDs internos después de cada upsert.
- Procesar en chunks de 500 filas.
- Usar transacción por chunk.
- Continuar ante errores de una fila y generar un reporte CSV de errores.
- No tratar legacy_party_logo_url como imagen principal.
- party_logo_url es el logo correcto.
- candidate_photo_url es la foto correcta.
- No inventar fotos cuando estén vacías.

## 4. Importador de resultados

Crea:

php artisan elections:import-results {path}

- Upsert de Poll por poll_code.
- Upsert de PollResult por result_uid.
- Validar que scope_uid y candidacy_uid existan.
- Guardar percentage como valor de 0 a 100.
- No mezclar resultados con la tabla candidates.

## 5. Panel administrativo

Crear una pantalla protegida para:

- subir CSV de candidaturas;
- subir CSV de resultados;
- ver filas válidas, actualizadas, omitidas y con error;
- descargar reporte de errores;
- filtrar por nivel, región, provincia, distrito y estado;
- editar campos faltantes, especialmente ubigeos, fotos y siglas.

## 6. Almacenamiento de imágenes

Crear un servicio opcional para descargar imágenes remotas.

- Guardar partidos en `elections/parties/{organization_uid}.jpg`.
- Guardar candidatos en `elections/candidates/{candidate_uid}.jpg`.
- No sobrescribir archivos existentes salvo opción `--force`.
- Si una descarga falla, conservar la URL remota.
- La aplicación debe usar primero la ruta local y después la URL remota.

## 7. Seguridad

- La carga administrativa debe requerir autenticación y autorización.
- Validar extensión, MIME y tamaño.
- No exponer rutas arbitrarias del servidor al comando o controlador.
- Escapar valores al mostrar nombres y URLs.
- No crear una ruta pública temporal de importación.

## 8. Pruebas

Agregar pruebas para:

- CSV regional válido;
- CSV provincial válido;
- CSV distrital válido;
- campos opcionales vacíos;
- fila duplicada;
- geografía inválida;
- foto de candidato ausente;
- error parcial sin rollback de todo el archivo.

Entrega los archivos modificados, comandos de ejecución y una breve explicación de las decisiones.
