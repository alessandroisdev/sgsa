<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ticket;

class TvController extends Controller
{
    /**
     * Obter Configuração da TV
     *
     * Retorna os últimos 5 atendimentos chamados na área onde a TV está instalada.
     * O Cabeçalho `X-Device-ID` é obrigatório.
     */
    public function config(Request $request)
    {
        $tv = $request->get('device');
        $areaId = $request->get('area_id');

        // Fetch last 5 called tickets in this area
        $lastTickets = Ticket::with(['service', 'priority', 'counter'])
            ->whereHas('service', function ($q) use ($areaId) {
                $q->where('area_id', $areaId);
            })
            ->where('status', 'called')
            ->orderBy('called_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'tv' => $tv,
            'history' => $lastTickets
        ]);
    }
}
