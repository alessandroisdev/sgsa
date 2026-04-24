<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\Priority;
use App\Services\QueueService;
use App\Models\ServicePriority; // Wait, there's no ServicePriority pivot, Services and Priorities are independent.

class TotemController extends Controller
{
    /**
     * Obter Configuração do Totem
     *
     * Retorna a lista de Prioridades e Serviços disponíveis baseados na Unidade e Área na qual o Totem está instalado.
     * O Cabeçalho `X-Device-ID` é obrigatório.
     */
    public function config(Request $request)
    {
        $totem = $request->get('device');
        $areaId = $request->get('area_id');

        $services = Service::where('area_id', $areaId)
                           ->where('active', true)
                           ->orderBy('name')
                           ->get();

        $priorities = Priority::where('active', true)
                              ->orderBy('weight', 'desc')
                              ->get();

        return response()->json([
            'totem' => $totem,
            'services' => $services,
            'priorities' => $priorities
        ]);
    }

    /**
     * Gerar Nova Senha
     *
     * Cria um novo atendimento (Ticket) para o serviço e prioridade selecionados no Totem.
     * @unauthenticated
     */
    public function ticket(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'priority_id' => 'required|exists:priorities,id'
        ]);

        $totem = $request->get('device');

        $queueService = app(QueueService::class);
        $ticket = $queueService->generateTicket(
            $request->service_id,
            $request->priority_id,
            $totem->id
        );

        return response()->json([
            'message' => 'Ticket generated successfully',
            'ticket' => $ticket
        ]);
    }
}
