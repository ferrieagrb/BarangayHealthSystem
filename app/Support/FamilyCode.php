<?php

namespace App\Support;

class FamilyCode
{
    public static function prefixFor(string $purokName, ?string $subgroupName): string
    {
        $purokCode = self::identifier($purokName);
        $subgroupCode = self::identifier($subgroupName ?? '');

        if ($subgroupCode === '') {
            return $purokCode !== '' ? $purokCode : 'AREA';
        }

        if ($purokCode === '') {
            return $subgroupCode;
        }

        preg_match('/([A-Z]+)$/', $subgroupCode, $matches);
        $subgroupSuffix = $matches[1] ?? '';

        return $purokCode.$subgroupSuffix;
    }

    public static function subgroupLabel(?string $purokName, ?string $subgroupName): string
    {
        if (blank($subgroupName)) {
            return 'No subgroup';
        }

        return 'Purok '.self::prefixFor($purokName ?? '', $subgroupName);
    }

    private static function identifier(string $name): string
    {
        $name = preg_replace('/\b(?:sub[\s-]*purok|purok|subgroup)\b/i', '', $name) ?? $name;
        $identifier = strtoupper(preg_replace('/[^a-z0-9]/i', '', $name) ?? '');

        return preg_replace('/^P(?=\d)/', '', $identifier) ?? $identifier;
    }
}
