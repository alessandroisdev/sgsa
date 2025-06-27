<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Ticket;
use App\Models\TicketHistory;
use App\Models\Transfer;

class TicketService
{
    /**
     * Criar uma nova senha (ticket) com número sequencial diário
     *
     * @param int $ticketTypeId
     * @param int $sectorId
     * @return Ticket
     */
    public function createTicket(int $ticketTypeId, int $sectorId): Ticket
    {
        $today = date('Y-m-d');

        // Busca o maior número de ticket do setor no dia para gerar o próximo
        $lastTicket = Ticket::where('date', $today)
            ->where('sector_id', $sectorId)
            ->orderBy('number', 'desc')
            ->first();

        $nextNumber = $lastTicket ? $lastTicket->number + 1 : 1;

        return Ticket::create([
            'number' => $nextNumber,
            'date' => $today,
            'ticket_type_id' => $ticketTypeId,
            'sector_id' => $sectorId,
            'status' => 'waiting',
        ]);
    }

    /**
     * Listar tickets em espera para um setor, ordenados por prioridade e número
     *
     * @param int $sectorId
     * @return array
     */
    public function listWaitingTickets(int $sectorId): array
    {
        return Ticket::where('sector_id', $sectorId)
            ->where('status', 'waiting')
            ->join('ticket_types', 'tickets.ticket_type_id', '=', 'ticket_types.id')
            ->orderBy('ticket_types.priority', 'asc')
            ->orderBy('tickets.number', 'asc')
            ->select('tickets.*')
            ->get()
            ->toArray();
    }

    /**
     * Chamar uma senha (alterar posição e registrar histórico)
     *
     * @param int $ticketId
     * @param int $userId
     * @return bool
     */
    public function callTicket(int $ticketId, int $userId): bool
    {
        $ticket = Ticket::find($ticketId);
        if (!$ticket || $ticket->status !== 'waiting') {
            return false; // Ticket não existe ou não está esperando
        }

        $ticket->status = 'called';
        $ticket->save();

        TicketHistory::create([
            'ticket_id' => $ticket->id,
            'action' => 'called',
            'description' => 'Senha chamada no guichê',
            'user_id' => $userId,
            'timestamp' => date('Y-m-d H:i:s'),
        ]);

        return true;
    }

    /**
     * Registrar atendimento concluído
     *
     * @param int $ticketId
     * @param int $userId
     * @return bool
     */
    public function attendTicket(int $ticketId, int $userId): bool
    {
        $ticket = Ticket::find($ticketId);
        if (!$ticket || $ticket->status !== 'called') {
            return false;
        }

        $ticket->status = 'attended';
        $ticket->save();

        TicketHistory::create([
            'ticket_id' => $ticket->id,
            'action' => 'attended',
            'description' => 'Atendimento concluído',
            'user_id' => $userId,
            'timestamp' => date('Y-m-d H:i:s'),
        ]);

        return true;
    }

    /**
     * Transferir atendimento para outro setor
     *
     * @param int $ticketId
     * @param int $toSectorId
     * @param int $userId
     * @return bool
     */
    public function transferTicket(int $ticketId, int $toSectorId, int $userId): bool
    {
        $ticket = Ticket::find($ticketId);
        if (!$ticket || !in_array($ticket->status, ['waiting', 'called'])) {
            return false;
        }

        $fromSectorId = $ticket->sector_id;

        // Atualiza setor e status para waiting na nova fila
        $ticket->sector_id = $toSectorId;
        $ticket->status = 'waiting';
        $ticket->save();

        // Registra transferência
        Transfer::create([
            'ticket_id' => $ticket->id,
            'from_sector_id' => $fromSectorId,
            'to_sector_id' => $toSectorId,
            'user_id' => $userId,
            'timestamp' => date('Y-m-d H:i:s'),
        ]);

        // Registra histórico da transferência
        TicketHistory::create([
            'ticket_id' => $ticket->id,
            'action' => 'transferred',
            'description' => "Transferido do setor $fromSectorId para $toSectorId",
            'user_id' => $userId,
            'timestamp' => date('Y-m-d H:i:s'),
        ]);

        return true;
    }

    /**
     * Criar agendamento online e gerar senha para o dia
     *
     * @param array $clientData
     * @param int $sectorId
     * @return Appointment
     */
    public function createAppointment(array $clientData, int $sectorId): Appointment
    {
        $appointmentDate = $clientData['appointment_date']; // formato 'Y-m-d'

        $appointment = Appointment::create([
            'full_name' => $clientData['full_name'],
            'contact_info' => $clientData['contact_info'],
            'document_info' => $clientData['document_info'],
            'sector_id' => $sectorId,
            'appointment_date' => $appointmentDate,
        ]);

        // Gerar senha para o dia do agendamento
        $ticket = $this->createTicket($clientData['ticket_type_id'], $sectorId);
        $ticket->date = $appointmentDate;
        $ticket->save();

        $appointment->ticket_id = $ticket->id;
        $appointment->save();

        return $appointment;
    }
}