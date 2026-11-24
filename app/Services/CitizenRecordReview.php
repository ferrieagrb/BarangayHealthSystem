<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;

class CitizenRecordReview
{
    public function apply(Builder $query): Builder
    {
        // Inspect the full accessible set before search and pagination, so both
        // records in a duplicate pair are found even when they fall on different pages.
        $citizens = (clone $query)->withoutEagerLoads()->reorder()->get([
            'citizens.id', 'Citizen_FName', 'Citizen_MName', 'Citizen_LName',
            'Citizen_Sex', 'Citizen_BirthDate',
        ]);
        $matches = [];
        $names = [];

        foreach ($citizens as $citizen) {
            if (trim((string) $citizen->Citizen_Sex) === '' || $citizen->validBirthDate() === null) {
                $matches[$citizen->id] = true;
            }

            if (trim((string) $citizen->Citizen_FName) === '' || trim((string) $citizen->Citizen_LName) === '') {
                continue;
            }
            $name = mb_strtolower(trim(preg_replace('/\s+/u', ' ', implode(' ', [
                $citizen->Citizen_FName, $citizen->Citizen_MName, $citizen->Citizen_LName,
            ]))));
            $names[$name][] = $citizen->id;
        }

        foreach ($names as $ids) {
            if (count($ids) > 1) {
                foreach ($ids as $id) {
                    $matches[$id] = true;
                }
            }
        }

        return $query->whereIntegerInRaw('citizens.id', array_keys($matches));
    }
}
