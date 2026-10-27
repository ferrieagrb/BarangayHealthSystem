<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\HealthRecord;
use App\Models\Vaccination;
use App\Models\VaccinationRecord;
use App\Models\MedicationRecord;
use App\Models\MaintenanceRecord;
use App\Models\PurokSubgroup;

class citizens extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'Citizen_FName',
        'Citizen_MName',
        'Citizen_LName',
        'Citizen_BirthDate',
        'Citizen_ContactNo',
        'Citizen_Age',
        'Citizen_Purok',
        'subgroup_id',
        'family_id',
        'qr_token',
        'photo_path',
        'Citizen_Sex',
        'Citizen_Height',
        'Citizen_Weight',
        'Citizen_HousingStatus',
        'Citizen_Pregnant',
        'Citizen_InPills',
        'Citizen_IsDeceased',
    ];

    protected $table = 'citizens';

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'Citizen_Pregnant' => 'boolean',
            'Citizen_InPills' => 'boolean',
            'Citizen_IsDeceased' => 'boolean',
        ];
    }

    public function healthRecords()
{
    return $this->hasMany(HealthRecord::class, 'citizen_id', 'id');
}

    public function vaccinations()
{
    return $this->hasMany(VaccinationRecord::class, 'citizen_id', 'id');
}

    public function hasImmunization(string $vaccineCode): bool
    {
        $searchTerms = [
            'bcg' => ['bcg'],
            'hepb' => ['hepatitis b', 'hepb'],
            'penta' => ['pentavalent', 'penta'],
            'opv' => ['oral polio', 'opv'],
            'ipv' => ['inactivated polio', 'ipv'],
            'pcv' => ['pneumococcal', 'pcv'],
            'mmr' => ['measles, mumps', 'mmr'],
            'mr' => ['measles-rubella', 'mr'],
            'td' => ['tetanus', 'td'],
            'hpv' => ['human papillomavirus', 'hpv'],
        ];

        $terms = $searchTerms[$vaccineCode] ?? [];

        return $this->vaccinations->contains(function (VaccinationRecord $record) use ($terms) {
            $vaccineName = strtolower($record->vaccine_name);

            foreach ($terms as $term) {
                if (strlen($term) <= 3) {
                    if (preg_match('/(^|[^a-z0-9])' . preg_quote($term, '/') . '([^a-z0-9]|$)/', $vaccineName)) {
                        return true;
                    }
                } elseif (str_contains($vaccineName, $term)) {
                    return true;
                }
            }

            return false;
        });
    }

    public function vaccinationSchedules()
    {
        return $this->hasMany(VaccinationSchedule::class, 'citizen_id');
    }

    public function medications()
{
    return $this->hasMany(MedicationRecord::class, 'citizen_id', 'id');
}

    public function maintenanceRecords()
{
    return $this->hasMany(MaintenanceRecord::class, 'citizen_id');
}

public function family()
{
    return $this->belongsTo(Family::class, 'family_id');
}

public function subgroup()
{
    return $this->belongsTo(PurokSubgroup::class, 'subgroup_id');
}

}

