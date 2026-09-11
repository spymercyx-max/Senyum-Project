<?php

namespace App\Services;

use App\Models\Outlet;
use App\Models\Territory;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LegacyImportService
{
    public function __construct(
        protected TerritoryService $territories
    ) {}

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array{created:int, skipped:int, errors:array<int, string>}
     */
    public function importTerritories(array $rows): array
    {
        $report = ['created' => 0, 'skipped' => 0, 'errors' => []];

        DB::transaction(function () use ($rows, &$report) {
            foreach ($rows as $i => $row) {
                $errors = $this->validateRow($row, ['city', 'district']);

                if (! empty($errors)) {
                    $report['skipped']++;
                    $report['errors'][] = 'Baris ' . ($i + 1) . ': ' . implode('; ', $errors);
                    continue;
                }

                $city = trim((string) $row['city']);
                $district = trim((string) $row['district']);

                $exists = Territory::where('city_normalized', TerritoryService::normalizeCity($city))
                    ->where('district_normalized', TerritoryService::normalizeDistrict($district))
                    ->exists();

                if ($exists) {
                    $report['skipped']++;
                    continue;
                }

                Territory::create([
                    'city' => $city,
                    'district' => $district,
                    'city_normalized' => TerritoryService::normalizeCity($city),
                    'district_normalized' => TerritoryService::normalizeDistrict($district),
                    'code' => $row['code'] ?? null,
                    'status' => $row['status'] ?? 'active',
                    'notes' => $row['notes'] ?? 'legacy-import',
                ]);

                $report['created']++;
            }
        });

        return $report;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array{created:int, skipped:int, errors:array<int, string>}
     */
    public function importOutlets(array $rows): array
    {
        $report = ['created' => 0, 'skipped' => 0, 'errors' => []];

        DB::transaction(function () use ($rows, &$report) {
            foreach ($rows as $i => $row) {
                $errors = $this->validateRow($row, ['name', 'address', 'city', 'district']);

                if (! empty($errors)) {
                    $report['skipped']++;
                    $report['errors'][] = 'Baris ' . ($i + 1) . ': ' . implode('; ', $errors);
                    continue;
                }

                $territory = $this->territories->findOrCreate(
                    trim((string) $row['city']),
                    trim((string) $row['district'])
                );

                $distributor = null;

                if (! empty($row['distributor_username'])) {
                    $distributor = User::where('username', $row['distributor_username'])->first();

                    if (! $distributor) {
                        $report['skipped']++;
                        $report['errors'][] = 'Baris ' . ($i + 1) . ': distributor tidak ditemukan.';
                        continue;
                    }
                } elseif (! empty($row['distributor_id'])) {
                    $distributor = User::find($row['distributor_id']);

                    if (! $distributor) {
                        $report['skipped']++;
                        $report['errors'][] = 'Baris ' . ($i + 1) . ': distributor tidak ditemukan.';
                        continue;
                    }
                } else {
                    $report['skipped']++;
                    $report['errors'][] = 'Baris ' . ($i + 1) . ': distributor wajib diisi.';
                    continue;
                }

                $duplicate = Outlet::where('distributor_id', $distributor->id)
                    ->where('name', trim((string) $row['name']))
                    ->where('address', trim((string) $row['address']))
                    ->exists();

                if ($duplicate) {
                    $report['skipped']++;
                    continue;
                }

                Outlet::create([
                    'territory_id' => $territory->id,
                    'distributor_id' => $distributor->id,
                    'name' => trim((string) $row['name']),
                    'address' => trim((string) $row['address']),
                    'city' => trim((string) $row['city']),
                    'district' => trim((string) $row['district']),
                    'phone' => $row['phone'] ?? null,
                    'latitude' => $row['latitude'] ?? null,
                    'longitude' => $row['longitude'] ?? null,
                    'status' => $row['status'] ?? 'active',
                    'notes' => $row['notes'] ?? 'legacy-import',
                ]);

                $report['created']++;
            }
        });

        return $report;
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array<int, string>  $required
     * @return array<int, string>
     */
    public function validateRow(array $row, array $required): array
    {
        $errors = [];

        foreach ($required as $field) {
            if (! isset($row[$field]) || trim((string) $row[$field]) === '') {
                $errors[] = "Field {$field} wajib diisi.";
            }
        }

        return $errors;
    }
}
