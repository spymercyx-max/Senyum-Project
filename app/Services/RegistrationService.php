<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RegistrationService
{
    public function __construct(
        protected TerritoryService $territories
    ) {}

    /**
     * @param  array{name:string,username:string,email?:?string,phone?:?string,password:string,whatsapp:string,city:string,district:string,experience?:?string,notes?:?string}  $data
     */
    public function register(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $city = trim($data['city']);
            $district = trim($data['district']);

            $territory = $this->territories->findOrCreate($city, $district);

            $user = User::create([
                'name' => $data['name'],
                'username' => $data['username'],
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? ($data['whatsapp'] ?? null),
                'password' => $data['password'],
                'role' => 'distributor',
                'status' => 'active',
            ]);

            $user->profile()->create([
                'city' => $city,
                'district' => $district,
                'experience' => $data['experience'] ?? null,
                'whatsapp' => $data['whatsapp'],
                'notes' => $data['notes'] ?? null,
            ]);

            $user->distributorProfile()->create([
                'territory_id' => $territory->id,
                'status' => 'pending',
            ]);

            ActivityLog::create([
                'actor_id' => $user->id,
                'action' => 'distributor.registered',
                'entity' => User::class,
                'entity_id' => $user->id,
                'metadata' => [
                    'username' => $user->username,
                    'territory_id' => $territory->id,
                ],
                'ip' => request()->ip(),
            ]);

            return $user->load(['profile', 'distributorProfile']);
        });
    }
}
