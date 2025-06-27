<?php

namespace App\Controllers;

use App\Core\Route;
use App\Services\TicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class TicketController extends Controller
{
    public function __construct(
        protected TicketService $ticketService
    )
    {
    }

    #[Route('POST', '/tickets/create')]
    public function createTicket(Request $request): JsonResponse
    {
        $ticketTypeId = $request->input('ticket_type_id', 0);
        $sectorId = $request->input('sector_id', 0);

        if (!$ticketTypeId || !$sectorId) {
            throw new BadRequestException('ticket_type_id and sector_id are required');
        }

        $ticket = $this->ticketService->createTicket($ticketTypeId, $sectorId);

        return $this->success('Senha criada com sucesso', ['ticket' => $ticket]);
    }

    #[Route('GET', '/tickets/waiting')]
    public function listWaitingTickets(Request $request): JsonResponse
    {
        $sectorId = $request->query('sector_id', 0);

        if (!$sectorId) {
            throw new BadRequestException('sector_id is required');
        }

        $tickets = $this->ticketService->listWaitingTickets($sectorId);

        return $this->success('Senhas disponíveis', ['tickets' => $tickets]);
    }

    #[Route('POST', '/tickets/call')]
    public function callTicket(Request $request): JsonResponse
    {
        $ticketId = $request->input('ticket_id', 0);
        $userId = $request->input('user_id', 0);

        if (!$ticketId || !$userId) {
            throw new BadRequestException('ticket_id and user_id are required');
        }

        $result = $this->ticketService->callTicket($ticketId, $userId);

        if ($result) {
            return $this->success('Chamar uma senha');
        }

        return $this->fail('Ticket not found or invalid status');
    }

    #[Route('POST', '/tickets/attend')]
    public function attendTicket(Request $request): JsonResponse
    {
        $ticketId = $request->input('ticket_id', 0);
        $userId = $request->input('user_id', 0);

        if (!$ticketId || !$userId) {
            throw new BadRequestException('ticket_id and user_id are required');
        }

        $result = $this->ticketService->attendTicket($ticketId, $userId);

        if ($result) {
            return $this->success('Registrar atendimento');
        }

        return $this->fail('Ticket not found or invalid status');
    }

    #[Route('POST', '/tickets/transfer')]
    public function transferTicket(Request $request): JsonResponse
    {
        $ticketId = $request->input('ticket_id', 0);
        $toSectorId = $request->input('to_sector_id', 0);
        $userId = $request->input('user_id', 0);

        if (!$ticketId || !$toSectorId || !$userId) {
            throw new BadRequestException('ticket_id, to_sector_id and user_id are required');
        }

        $result = $this->ticketService->transferTicket($ticketId, $toSectorId, $userId);

        if ($result) {
            return $this->success('Transfere atendimento para outro setor');
        }

        return $this->fail('Ticket not found or invalid status');
    }
}