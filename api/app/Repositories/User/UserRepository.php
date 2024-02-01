<?php

declare(strict_types=1);

namespace Repository\User;

use App\Models\User;
use Hamcrest\Type\IsBoolean;
use Repository\BaseRepository;

class UserRepository extends BaseRepository
{

    public function model()
    {
        return User::class;
    }

    public function filterDataForModel()
    {
        return ['username', 'email'];
    }
    public function findByIDWithRole($id)
    {
        return $this->model()::with('role')->find($id);
    }

    public function getLoggedINUserPermissions($userId)
    {
        return $this->model()::select('permissions.name')
            ->where('users.id', $userId)
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->join('role_permission', 'roles.id', '=', 'role_permission.role_id')
            ->join('permissions', 'role_permission.permission_id', '=', 'permissions.id')
            ->get();
    }

    public function generateAccessToken(User $user): string
    {
        return $user->createToken('authToken')->accessToken;
    }

    public function generateDefaultPassword(): string
    {
        return '12345678';
    }

    public function updateOrCreate(string $email, array $modelData)
    {
        $existingRecord = $this->model()::where('email', $email)->first();

        if ($existingRecord) {
            $existingRecord->update($modelData);
        } else {
            $this->model()::create(array_merge(['email' => $email], $modelData));
        }
    }
}
