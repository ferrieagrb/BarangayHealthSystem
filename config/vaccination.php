<?php

return [
    'policy' => 'philippines_routine',
    'outside_routine_age_message' => 'Outside the configured routine schedule. Refer to the health center for catch-up review.',
    'vaccines' => [
        'BCG' => [
            'label' => 'BCG',
            'guideline' => 'Routine dose at birth. Older age requires health-center catch-up review.',
            'source' => 'https://unicef.org/philippines/stories/routine-immunization-children-philippines',
            'doses' => [
                ['name' => 'Single Dose', 'min_days' => 0, 'max_days' => 0],
            ],
        ],
        'HepB' => [
            'label' => 'Hepatitis B (HepB)',
            'guideline' => 'Birth dose within 24 hours of birth.',
            'source' => 'https://doh.gov.ph/uhc/health-programs/expanded-program-on-immunization/',
            'doses' => [
                ['name' => 'Single Dose', 'min_days' => 0, 'max_days' => 0],
            ],
        ],
        'Penta' => [
            'label' => 'Pentavalent (Penta)',
            'guideline' => 'Routine doses at 6, 10, and 14 weeks.',
            'source' => 'https://unicef.org/philippines/stories/routine-immunization-children-philippines',
            'doses' => [
                ['name' => 'Dose 1', 'min_days' => 42, 'max_days' => 69],
                ['name' => 'Dose 2', 'min_days' => 70, 'max_days' => 97],
                ['name' => 'Dose 3', 'min_days' => 98, 'max_days' => 98],
            ],
        ],
        'OPV' => [
            'label' => 'Oral Polio Vaccine (OPV)',
            'guideline' => 'Routine doses at 6, 10, and 14 weeks.',
            'source' => 'https://unicef.org/philippines/stories/routine-immunization-children-philippines',
            'doses' => [
                ['name' => 'Dose 1', 'min_days' => 42, 'max_days' => 69],
                ['name' => 'Dose 2', 'min_days' => 70, 'max_days' => 97],
                ['name' => 'Dose 3', 'min_days' => 98, 'max_days' => 98],
            ],
        ],
        'IPV' => [
            'label' => 'Inactivated Polio Vaccine (IPV)',
            'guideline' => 'Routine doses at 14 weeks and 9 months.',
            'source' => 'https://unicef.org/philippines/stories/routine-immunization-children-philippines',
            'doses' => [
                ['name' => 'Dose 1', 'min_days' => 98, 'max_days' => 273],
                ['name' => 'Dose 2', 'min_days' => 274, 'max_days' => 274],
            ],
        ],
        'PCV' => [
            'label' => 'Pneumococcal Conjugate Vaccine (PCV)',
            'guideline' => 'Routine doses at 6, 10, and 14 weeks.',
            'source' => 'https://unicef.org/philippines/stories/routine-immunization-children-philippines',
            'doses' => [
                ['name' => 'Dose 1', 'min_days' => 42, 'max_days' => 69],
                ['name' => 'Dose 2', 'min_days' => 70, 'max_days' => 97],
                ['name' => 'Dose 3', 'min_days' => 98, 'max_days' => 98],
            ],
        ],
        'MMR' => [
            'label' => 'Measles, Mumps, Rubella (MMR)',
            'guideline' => 'Routine doses at 9 months and 1 year.',
            'source' => 'https://unicef.org/philippines/stories/routine-immunization-children-philippines',
            'doses' => [
                ['name' => 'Dose 1', 'min_months' => 9, 'max_months' => 11],
                ['name' => 'Dose 2', 'min_months' => 12, 'max_months' => 12],
            ],
        ],
        'MR' => [
            'label' => 'Measles-Rubella (MR)',
            'guideline' => '2026 school-based routine cohorts: Grade 1 (6-7 years) and Grade 7 (12-13 years).',
            'source' => 'https://pia.gov.ph/news/luzon/car/doh-car-calls-for-public-support-for-2026-bakuna-eskwela',
            'doses' => [
                ['name' => 'Dose 1', 'min_years' => 6, 'max_years' => 7],
                ['name' => 'Dose 2', 'min_years' => 12, 'max_years' => 13],
            ],
        ],
        'Td' => [
            'label' => 'Tetanus-Diphtheria (Td)',
            'guideline' => '2026 school-based routine cohorts: Grade 1 (6-7 years) and Grade 7 (12-13 years).',
            'source' => 'https://pia.gov.ph/news/luzon/car/doh-car-calls-for-public-support-for-2026-bakuna-eskwela',
            'doses' => [
                ['name' => 'Dose 1', 'min_years' => 6, 'max_years' => 7],
                ['name' => 'Dose 2', 'min_years' => 12, 'max_years' => 13],
            ],
        ],
        'HPV' => [
            'label' => 'Human Papillomavirus (HPV)',
            'guideline' => 'DOH-CAR routine age guidance: 9-14 years; verify the dose schedule with the health center.',
            'source' => 'https://caro.doh.gov.ph/hpv-vaccine/',
            'doses' => [
                ['name' => 'Single Dose', 'min_years' => 9, 'max_years' => 14],
            ],
        ],
    ],
];
