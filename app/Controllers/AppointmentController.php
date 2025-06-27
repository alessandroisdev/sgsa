<?php

namespace App\Controllers;

use App\Core\Route;
use App\Services\TicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function __construct(
        protected TicketService $ticketService
    )
    {
    }

    #[Route('POST', '/appointments/create')]
    public function createAppointment(Request $request): JsonResponse
    {
        $data = $request->all();

        $requiredFields = ['full_name', 'contact_info', 'document_info', 'sector_id', 'appointment_date', 'ticket_type_id'];
        $errors = [];

        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $errors[$field] = "$field is required";
            }
        }

        if (!empty($errors)) {
            return $this->fail('Houve erros', $errors);
        }

        $sectorId = (int)$data['sector_id'];

        $appointment = $this->ticketService->createAppointment($data, $sectorId);

        return $this->success('Agendamento criado', ['appointment' => $appointment]);
    }
}