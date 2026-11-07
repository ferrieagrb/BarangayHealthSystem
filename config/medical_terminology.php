<?php

return [
    'timeout' => (int) env('MEDICAL_TERMINOLOGY_TIMEOUT', 10),
    'reference_path' => resource_path('data/medical-terminology/official-references.json'),
    'brand_reference_path' => resource_path('data/medical-terminology/verified-brands.json'),
    'cache_minutes' => (int) env('MEDICAL_TERMINOLOGY_CACHE_MINUTES', 1440),
    'vaccine_aliases' => [
        'BCG' => [
            'bacillus calmette guerin',
            'bacille calmette guerin',
        ],
        'HepB' => [
            'hepatitis b',
            'hepatitis b vaccine',
            'hepb',
        ],
        'Penta' => [
            'pentavalent',
            'diphtheria tetanus pertussis hepatitis b haemophilus influenzae type b',
        ],
        'OPV' => [
            'oral polio',
            'oral poliovirus vaccine',
        ],
        'IPV' => [
            'inactivated polio',
            'inactivated poliovirus vaccine',
        ],
        'PCV' => [
            'pneumococcal conjugate',
            'pneumococcal conjugate vaccine',
        ],
        'MMR' => [
            'measles mumps rubella',
        ],
        'MR' => [
            'measles rubella',
        ],
        'Td' => [
            'tetanus diphtheria',
            'tetanus and diphtheria',
        ],
        'HPV' => [
            'human papillomavirus',
            'human papilloma virus',
        ],
    ],
];
