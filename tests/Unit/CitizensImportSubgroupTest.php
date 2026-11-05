<?php

namespace Tests\Unit;

use App\Imports\CitizensImport;
use App\Models\Purok;
use App\Models\PurokSubgroup;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CitizensImportSubgroupTest extends TestCase
{
    private function purok(string $name, array $subgroupNames): Purok
    {
        $purok = new Purok(['name' => $name]);
        $subgroups = collect();
        foreach ($subgroupNames as $id => $subgroupName) {
            $subgroup = new PurokSubgroup(['name' => $subgroupName]);
            $subgroup->id = $id + 1;
            $subgroups->push($subgroup);
        }
        $purok->setRelation('subgroups', $subgroups);

        return $purok;
    }

    private function find(Purok $purok, string $input): ?string
    {
        $import = new CitizensImport();
        $method = new \ReflectionMethod($import, 'findSubgroup');

        return $method->invoke($import, $purok, $input)?->name;
    }

    #[DataProvider('inputs')]
    public function test_each_subgroup_is_matched_to_itself(string $input, ?string $expected): void
    {
        $purok = $this->purok('Purok 3', ['3A', '3B', '3C', '3D']);

        $this->assertSame($expected, $this->find($purok, $input));
    }

    public static function inputs(): array
    {
        return [
            ['3a', '3A'],
            ['3B', '3B'],
            ['3c', '3C'],
            ['3 d', '3D'],
            ['3-C', '3C'],
            ['c', '3C'],
            ['Purok 3C', '3C'],
            ['4c', null],
            ['3e', null],
        ];
    }

    public function test_database_names_with_other_formatting_still_match(): void
    {
        $purok = $this->purok('Purok 12', ['Sub-purok 12 A', '12-b', 'Purok 12C']);

        $this->assertSame('Sub-purok 12 A', $this->find($purok, '12a'));
        $this->assertSame('12-b', $this->find($purok, '12B'));
        $this->assertSame('Purok 12C', $this->find($purok, '12c'));
        $this->assertSame('Purok 12C', $this->find($purok, 'c'));
    }
}
