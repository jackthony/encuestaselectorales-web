<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

final class StaticPublicPortalController extends Controller
{
    public function show(Request $request): View
    {
        $page = (string) ($request->route('page') ?? '');
        $config = match ($page) {
            'metodologia.php' => $this->metodologiaPageConfig(),
            'quienes-somos.php' => $this->quienesSomosPageConfig(),
            'fuentes-correcciones.html' => $this->fuentesCorreccionesPageConfig(),
            'politica-editorial.html' => $this->politicaEditorialPageConfig(),
            'politica-privacidad.html' => $this->politicaPrivacidadPageConfig(),
            default => null,
        };

        abort_if($config === null, 404);

        return view('pages.static-page', $config);
    }

    /**
     * @return array<string, mixed>
     */
    private function metodologiaPageConfig(): array
    {
        return [
            'pageTitle' => 'Metodología y Rigor | EncuestasElectorales.pe',
            'pageDescription' => 'Conoce cómo medimos, validamos y publicamos encuestas web reales con resguardo antiabuso.',
            'activeNav' => 'metodologia',
            'tickerText' => 'Sondeo ciudadano en vivo · Elecciones 2026',
            'tickerSecondary' => 'Validación server-side y cobertura territorial',
            'heroBadge' => 'Inteligencia electoral',
            'heroTitle' => 'Midiendo el pulso político en tiempo real',
            'heroLead' => 'Publicamos sondeos opt-in, territoriales y verificables. No inventamos datos: solo mostramos rondas reales, con niveles de distrito, provincia y región.',
            'sidebarTitle' => 'En esta página',
            'sidebarLinks' => [
                ['href' => '#como-medimos', 'label' => 'Cómo medimos'],
                ['href' => '#seguridad', 'label' => 'Seguridad'],
                ['href' => '#transparencia', 'label' => 'Transparencia'],
            ],
            'intro' => 'La metodología combina participación online, validación territorial y resguardo server-side para evitar duplicados sin sacrificar usabilidad.',
            'sections' => [
                [
                    'id' => 'como-medimos',
                    'title' => '1. Cómo medimos',
                    'paragraphs' => [
                        'Nuestra base es un sondeo online abierto, con participación voluntaria y publicación limitada a rondas realmente activas.',
                        'Cada publicación se etiqueta por nivel territorial para que un mismo nombre pueda distinguirse como distrito, provincia o región sin ambigüedad.',
                    ],
                    'bullets' => [
                        'Cobertura inicial con las localidades ya cargadas en la base real.',
                        'Corte público hasta el 5 de agosto de 2026 para las primeras rondas visibles.',
                        'Actualización continua conforme llegan nuevos lotes de data validada.',
                    ],
                ],
                [
                    'id' => 'seguridad',
                    'title' => '2. Seguridad y antiabuso',
                    'cards' => [
                        [
                            'icon' => 'fas fa-shield-halved',
                            'title' => 'Unicidad del voto',
                            'body' => 'Bloqueamos repetición por IP y token de dispositivo. Los identificadores no se exponen como secuencias predecibles.',
                        ],
                        [
                            'icon' => 'fas fa-location-crosshairs',
                            'title' => 'Validación geográfica',
                            'body' => 'GPS y accuracy funcionan como señales de validación. Si faltan, el voto no entra en la ruta pública.',
                        ],
                    ],
                ],
                [
                    'id' => 'transparencia',
                    'title' => '3. Transparencia y publicación',
                    'paragraphs' => [
                        'Cada encuesta publicada expone su nivel, su ámbito y su fuente real. No usamos ejemplos ficticios ni instituciones inventadas.',
                        'Cuando falta un dato, publicamos ausencia o bloqueo explícito. Nunca inventamos una encuesta para rellenar una columna.',
                    ],
                    'quote' => 'Publicar menos, pero publicar real, es la única forma de sostener confianza.',
                ],
            ],
            'cta' => [
                'title' => '¿Tienes dudas sobre la metodología?',
                'body' => 'Si detectas una inconsistencia en un distrito, provincia o región, la revisamos antes de abrir la ronda pública.',
                'label' => 'Contactar al equipo',
                'href' => 'mailto:contacto@encuestaselectorales.pe',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function quienesSomosPageConfig(): array
    {
        return [
            'pageTitle' => 'Quiénes Somos | EncuestasElectorales.pe',
            'pageDescription' => 'Equipo independiente de datos, infraestructura y publicación cívica para las elecciones de Perú.',
            'activeNav' => 'quienes-somos',
            'tickerText' => 'Inteligencia electoral y ciencia de datos',
            'tickerSecondary' => 'Plataforma cívica independiente',
            'heroBadge' => 'Plataforma cívica',
            'heroTitle' => 'Tecnología para la transparencia electoral',
            'heroLead' => 'Somos una iniciativa independiente enfocada en democratizar el acceso a la inteligencia electoral. Datos rigurosos, en tiempo real y sin agendas ocultas.',
            'sidebarTitle' => 'En esta página',
            'sidebarLinks' => [
                ['href' => '#que-hacemos', 'label' => 'Qué hacemos'],
                ['href' => '#compromisos', 'label' => 'Compromisos'],
                ['href' => '#contacto', 'label' => 'Contacto'],
            ],
            'intro' => 'La plataforma se construyó para publicar data real y facilitar el acceso público, no para inflar métricas ni vender humo.',
            'sections' => [
                [
                    'id' => 'que-hacemos',
                    'title' => '1. Qué hacemos',
                    'paragraphs' => [
                        'Diseñamos, publicamos y mantenemos el portal electoral con foco en territorios, candidatos y rondas públicas reales.',
                        'La estructura actual prioriza el sitio web de encuestas, no los estudios de campo como ruta crítica.',
                    ],
                ],
                [
                    'id' => 'compromisos',
                    'title' => '2. Compromisos',
                    'cards' => [
                        [
                            'icon' => 'fas fa-scale-balanced',
                            'title' => 'Independencia',
                            'body' => 'No cruzamos poll numbers, rankings o agregación con intereses editoriales o comerciales.',
                        ],
                        [
                            'icon' => 'fas fa-lock',
                            'title' => 'Seguridad',
                            'body' => 'Mantenemos credenciales, llaves y configuración fuera del web root y siempre con prepared statements.',
                        ],
                        [
                            'icon' => 'fas fa-eye',
                            'title' => 'Transparencia',
                            'body' => 'Cuando no hay data suficiente, mostramos ausencia; no inventamos un card para completar la pantalla.',
                        ],
                    ],
                ],
                [
                    'id' => 'contacto',
                    'title' => '3. Contacto',
                    'paragraphs' => [
                        'El equipo responde consultas de prensa y correcciones por los canales oficiales mientras se completa el panel de administración.',
                    ],
                ],
            ],
            'cta' => [
                'title' => 'Canales oficiales',
                'body' => 'Escríbenos si necesitas corregir una ficha, sumar media o coordinar una carga por lote.',
                'label' => 'Correo de prensa',
                'href' => 'mailto:prensa@encuestaselectorales.pe',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function fuentesCorreccionesPageConfig(): array
    {
        return [
            'pageTitle' => 'Fuentes y correcciones — Encuestas Electorales Perú',
            'pageDescription' => 'Cómo citamos encuestadoras, qué hacemos con fotos oficiales y cómo pedir una corrección.',
            'activeNav' => '',
            'tickerText' => 'Fuentes verificadas y correcciones públicas',
            'tickerSecondary' => 'Citar, enlazar y corregir',
            'heroBadge' => 'Fuentes y correcciones',
            'heroTitle' => 'Cómo citamos y cómo corregimos',
            'heroLead' => 'Agregamos datos que no producimos nosotros. Cuando algo está mal, lo corregimos en público y con trazabilidad.',
            'sidebarTitle' => 'Temas',
            'sidebarLinks' => [
                ['href' => '#terceros', 'label' => 'Encuestas de terceros'],
                ['href' => '#imagenes', 'label' => 'Fotos y media'],
                ['href' => '#correcciones', 'label' => 'Correcciones'],
            ],
            'intro' => 'La regla es simple: citar la fuente, enlazar la evidencia y nunca publicar una versión ficticia solo para llenar una página.',
            'sections' => [
                [
                    'id' => 'terceros',
                    'title' => '1. Encuestas de terceros',
                    'paragraphs' => [
                        'Cuando mostramos una cifra ajena, la vinculamos al reporte original y publicamos el nombre de la encuestadora junto con la fecha.',
                    ],
                    'bullets' => [
                        'Nunca reproducimos un PDF completo si basta con citar el dato y su enlace.',
                        'La fuente siempre queda visible para que cualquiera pueda verificarla.',
                    ],
                ],
                [
                    'id' => 'imagenes',
                    'title' => '2. Fotos y media',
                    'paragraphs' => [
                        'La foto del candidato y el logo del partido se toman del catálogo real. Si falta el archivo, usamos un fallback neutral, no una imagen inventada.',
                        'El mismo criterio aplica a miniaturas y previews para redes sociales.',
                    ],
                ],
                [
                    'id' => 'correcciones',
                    'title' => '3. Correcciones',
                    'bullets' => [
                        'Si un dato está mal, abrimos una corrección pública y trazable.',
                        'Si una ficha no está lista, preferimos dejarla en estado bloqueado.',
                        'El punto de contacto inicial para correcciones es el repositorio del proyecto.',
                    ],
                ],
            ],
            'cta' => [
                'title' => 'Canal de correcciones',
                'body' => 'Si algo no cuadra, repórtalo. Revisamos la fuente antes de cambiar el contenido visible.',
                'label' => 'Abrir issue público',
                'href' => 'https://github.com/jackthony/encuestaselectorales-web/issues/new',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function politicaEditorialPageConfig(): array
    {
        return [
            'pageTitle' => 'Independencia editorial — Encuestas Electorales Perú',
            'pageDescription' => 'Qué se vende y qué nunca se vende en Encuestas Electorales.',
            'activeNav' => '',
            'tickerText' => 'Independencia editorial',
            'tickerSecondary' => 'La línea entre visibilidad y números no se cruza',
            'heroBadge' => 'Política editorial',
            'heroTitle' => 'Independencia editorial',
            'heroLead' => 'Somos una plataforma, no un actor político. Si monetizamos visibilidad, nunca monetizamos resultados.',
            'sidebarTitle' => 'En esta página',
            'sidebarLinks' => [
                ['href' => '#firewall', 'label' => 'Qué se vende'],
                ['href' => '#nunca', 'label' => 'Qué nunca se vende'],
                ['href' => '#principio', 'label' => 'Principio rector'],
            ],
            'intro' => 'La confianza en el sitio depende de mantener separadas la visibilidad comercial y la lógica de publicación de datos.',
            'sections' => [
                [
                    'id' => 'firewall',
                    'title' => '1. Qué se vende',
                    'bullets' => [
                        'Ficha destacada o visibilidad especial de un candidato verificado.',
                        'Alertas cuando un distrito publica una nueva encuesta.',
                    ],
                ],
                [
                    'id' => 'nunca',
                    'title' => '2. Qué nunca se vende',
                    'bullets' => [
                        'El porcentaje de intención de voto.',
                        'El orden o ranking de candidatos.',
                        'La lógica de agregación entre encuestadoras.',
                        'La metodología o ficha técnica de una encuesta.',
                    ],
                ],
                [
                    'id' => 'principio',
                    'title' => '3. Principio rector',
                    'paragraphs' => [
                        'Si esa línea se cruza, el sitio deja de tener valor. Por eso el firewall editorial es absoluto.',
                    ],
                ],
            ],
            'cta' => [
                'title' => 'Ver metodología',
                'body' => 'La metodología explica cómo se publica la data sin mezclarla con intereses comerciales.',
                'label' => 'Ir a metodología',
                'href' => url('/metodologia.php'),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function politicaPrivacidadPageConfig(): array
    {
        return [
            'pageTitle' => 'Política de privacidad — Encuestas Electorales Perú',
            'pageDescription' => 'Qué recolectamos y cómo protegemos los datos cuando la encuesta está activa.',
            'activeNav' => '',
            'tickerText' => 'Privacidad mínima, protección máxima',
            'tickerSecondary' => 'No exponemos datos sin necesidad',
            'heroBadge' => 'Política de privacidad',
            'heroTitle' => 'Qué recolectamos y por qué',
            'heroLead' => 'La recolección mínima sirve para publicar el voto sin duplicados y proteger la plataforma. No pedimos más de lo que el flujo necesita.',
            'sidebarTitle' => 'Temas',
            'sidebarLinks' => [
                ['href' => '#recoleccion', 'label' => 'Recolección'],
                ['href' => '#proteccion', 'label' => 'Protección'],
                ['href' => '#control', 'label' => 'Control'],
            ],
            'intro' => 'La política se publica antes de expandir cualquier captura nueva. Así el compromiso queda escrito primero y la implementación después.',
            'sections' => [
                [
                    'id' => 'recoleccion',
                    'title' => '1. Qué podemos recolectar',
                    'bullets' => [
                        'Respuestas del voto web cuando el usuario decide participar.',
                        'Datos agregados de uso sin cookies invasivas.',
                        'IP en forma blindada para evitar votos duplicados.',
                    ],
                ],
                [
                    'id' => 'proteccion',
                    'title' => '2. Cómo protegemos',
                    'paragraphs' => [
                        'La IP se trata con blindaje server-side y el control antiabuso nunca depende de un dato que el cliente pueda fabricar.',
                    ],
                    'cards' => [
                        [
                            'icon' => 'fas fa-user-shield',
                            'title' => 'Señales, no confianza ciega',
                            'body' => 'Fingerprint, token de dispositivo y GPS ayudan a detectar abuso, pero la validación real vive en servidor.',
                        ],
                        [
                            'icon' => 'fas fa-database',
                            'title' => 'Secretos aislados',
                            'body' => 'Credenciales, llaves y configuración sensible quedan fuera del web root y del alcance público.',
                        ],
                    ],
                ],
                [
                    'id' => 'control',
                    'title' => '3. Control del usuario',
                    'paragraphs' => [
                        'Si algo cambia, actualizamos esta página primero. No activamos nuevos flujos sin dejar la política por escrito.',
                    ],
                ],
            ],
            'cta' => [
                'title' => '¿Necesitas revisar un dato?',
                'body' => 'Escríbenos antes de asumir que el sistema cambió la política.',
                'label' => 'Contacto',
                'href' => 'mailto:contacto@encuestaselectorales.pe',
            ],
        ];
    }
}
