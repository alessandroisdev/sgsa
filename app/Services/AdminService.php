<?php
namespace App\Services;

use App\Models\User;
use App\Models\Sector;
use App\Models\Counter;
use App\Models\TicketType;

class AdminService
{
    // Usuários
    public function listUsers(): array
    {
        return User::all()->toArray();
    }

    public function createUser(array $data): User
    {
        $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        return User::create($data);
    }

    public function updateUser(int $id, array $data): bool
    {
        $user = User::find($id);
        if (!$user) return false;

        if (isset($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
            unset($data['password']);
        }

        return $user->update($data);
    }

    public function deleteUser(int $id): bool
    {
        $user = User::find($id);
        if (!$user) return false;
        return $user->delete();
    }

    // Setores
    public function listSectors(): array
    {
        return Sector::all()->toArray();
    }

    public function createSector(array $data): Sector
    {
        return Sector::create($data);
    }

    public function updateSector(int $id, array $data): bool
    {
        $sector = Sector::find($id);
        if (!$sector) return false;
        return $sector->update($data);
    }

    public function deleteSector(int $id): bool
    {
        $sector = Sector::find($id);
        if (!$sector) return false;
        return $sector->delete();
    }

    // Guichês
    public function listCounters(): array
    {
        return Counter::all()->toArray();
    }

    public function createCounter(array $data): Counter
    {
        return Counter::create($data);
    }

    public function updateCounter(int $id, array $data): bool
    {
        $counter = Counter::find($id);
        if (!$counter) return false;
        return $counter->update($data);
    }

    public function deleteCounter(int $id): bool
    {
        $counter = Counter::find($id);
        if (!$counter) return false;
        return $counter->delete();
    }

    // Tipos de senha
    public function listTicketTypes(): array
    {
        return TicketType::all()->toArray();
    }

    public function createTicketType(array $data): TicketType
    {
        return TicketType::create($data);
    }

    public function updateTicketType(int $id, array $data): bool
    {
        $ticketType = TicketType::find($id);
        if (!$ticketType) return false;
        return $ticketType->update($data);
    }

    public function deleteTicketType(int $id): bool
    {
        $ticketType = TicketType::find($id);
        if (!$ticketType) return false;
        return $ticketType->delete();
    }
}