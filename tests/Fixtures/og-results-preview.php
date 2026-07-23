<?php

// Fase 1 — fixture visual estático para el prototipo de miniatura OG.
// No conectar con base de datos ni datos reales.
// bar_width se calcula en la vista a partir de percentage, no vive aquí.

return [
    'eyebrow' => 'SONDEO CIUDADANO · PERÚ 2026',
    'title' => 'Distrito de San Isidro',
    'subtitle' => 'Alcaldía Distrital 2026 · Ronda 1',
    'footer_text' => 'Base: 18,420 votos · Actualizado: 23/07/2026 14:32',
    'results' => [
        [
            'position' => 1,
            'candidate_name' => 'María Fernanda Quispe Rojas',
            'party_name' => 'AVANZA PAÍS',
            'percentage' => '39.7%',
            'votes' => '7,311',
        ],
        [
            'position' => 2,
            'candidate_name' => 'Jorge Luis Delgado',
            'party_name' => 'FUERZA POPULAR',
            'percentage' => '28.2%',
            'votes' => '5,203',
        ],
        [
            'position' => 3,
            'candidate_name' => 'Ana Lucía Torres',
            'party_name' => 'PERÚ LIBRE',
            'percentage' => '16.9%',
            'votes' => '3,120',
        ],
        [
            'position' => 4,
            'candidate_name' => 'Carlos Mendoza',
            'party_name' => 'SOMOS PERÚ',
            'percentage' => '10.3%',
            'votes' => '1,890',
        ],
        [
            'position' => 5,
            'candidate_name' => 'Rosa Elvira Huamán',
            'party_name' => 'PODEMOS PERÚ',
            'percentage' => '4.9%',
            'votes' => '896',
        ],
    ],
];
