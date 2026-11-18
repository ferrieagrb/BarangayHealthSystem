<?php

return [
    // Personal names use the same title casing as imported citizen records.
    'title' => [
        'Citizen_FName', 'Citizen_MName', 'Citizen_LName',
        'first_name', 'middle_name', 'last_name', 'family_name',
        'address', 'referred_by', 'supplier',
    ],

    // Keep existing abbreviations and mixed-case medical terms intact.
    'words' => [
        'name', 'title', 'batch_title', 'description', 'details',
        'comments', 'notes', 'requests_for', 'location', 'venue',
        'diagnosis', 'medicine_name', 'vaccine_name',
        'treatment_given', 'medication_given', 'self_medication',
        'maintenance_schedule',
    ],

    'preserve_words' => ['mg', 'mcg', 'µg', 'g', 'kg', 'ml', 'mL', 'L', 'mm', 'cm', 'mmol', 'mmHg', 'bpm', 'pH', 'mEq', 'IU'],
];
