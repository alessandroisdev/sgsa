@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="mb-1 fw-bold text-dark">Visão Global</h3>
            <p class="text-secondary mb-0">Métricas em tempo real de hoje.</p>
        </div>
        <div class="d-flex align-items-center gap-2 text-secondary">
            <div class="spinner-grow spinner-grow-sm text-success" role="status"></div>
            <small>Atualizando em tempo real...</small>
        </div>
    </div>
</div>

<!-- Global Metrics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-secondary mb-2"><i class="bi bi-ticket-detailed me-2"></i>Atendimentos Hoje</div>
                <h2 class="fw-bold mb-0" id="totalTicketsToday">-</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-secondary mb-2"><i class="bi bi-hourglass-split me-2"></i>Fila de Espera</div>
                <h2 class="fw-bold text-warning mb-0" id="pendingTickets">-</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-secondary mb-2"><i class="bi bi-clock-history me-2"></i>Tempo Médio Atendimento</div>
                <h2 class="fw-bold text-info mb-0" id="avgServiceTime">-</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-secondary mb-2"><i class="bi bi-check2-circle me-2"></i>Finalizados</div>
                <h2 class="fw-bold text-success mb-0" id="completedTickets">-</h2>
            </div>
        </div>
    </div>
</div>

<!-- Rankings -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card border-0 bg-primary text-white shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-white-50 text-uppercase fw-bold"><i class="bi bi-trophy me-2"></i>Funcionário Destaque</h6>
                <h4 class="fw-bold mb-1" id="topUser">-</h4>
                <p class="mb-0 text-white-50"><span id="topUserCount">0</span> atendimentos concluídos</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 bg-dark text-white shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-white-50 text-uppercase fw-bold"><i class="bi bi-bookmark-star me-2"></i>Serviço Mais Buscado</h6>
                <h4 class="fw-bold mb-1" id="topService">-</h4>
                <p class="mb-0 text-white-50"><span id="topServiceCount">0</span> senhas geradas</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 bg-danger text-white shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-white-50 text-uppercase fw-bold"><i class="bi bi-exclamation-circle me-2"></i>Prioridade Destaque</h6>
                <h4 class="fw-bold mb-1" id="topPriority">-</h4>
                <p class="mb-0 text-white-50"><span id="topPriorityCount">0</span> prioridades</p>
            </div>
        </div>
    </div>
</div>

<!-- Units Monitor -->
<h4 class="fw-bold text-dark mb-3">Monitoramento das Unidades</h4>
<div class="row" id="unitsGrid">
    <div class="col-12 text-center text-secondary py-5">
        <div class="spinner-border text-primary" role="status"></div>
        <p class="mt-2">Carregando unidades...</p>
    </div>
</div>

<!-- Unit Details Modal -->
<div class="modal fade" id="unitMonitorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" id="monitorModalTitle">Detalhes da Unidade</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pb-4" id="monitorModalBody">
                <!-- Injected via JS -->
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let pollingInterval;
    let modalPollingInterval;

    document.addEventListener('DOMContentLoaded', () => {
        fetchMetrics();
        // Poll global metrics every 10 seconds
        pollingInterval = setInterval(fetchMetrics, 10000);

        // Clear modal polling when modal closes
        document.getElementById('unitMonitorModal').addEventListener('hidden.bs.modal', () => {
            clearInterval(modalPollingInterval);
        });
    });

    async function fetchMetrics() {
        try {
            const response = await window.HttpService.get('{{ route("admin.dashboard.metrics") }}');
            
            document.getElementById('totalTicketsToday').innerText = response.total_today;
            document.getElementById('pendingTickets').innerText = response.pending;
            document.getElementById('avgServiceTime').innerText = response.avg_service_time;
            document.getElementById('completedTickets').innerText = response.completed;

            if(response.top_user) {
                document.getElementById('topUser').innerText = response.top_user.name;
                document.getElementById('topUserCount').innerText = response.top_user.count;
            }
            if(response.top_service) {
                document.getElementById('topService').innerText = response.top_service.name;
                document.getElementById('topServiceCount').innerText = response.top_service.count;
            }
            if(response.top_priority) {
                document.getElementById('topPriority').innerText = response.top_priority.name;
                document.getElementById('topPriorityCount').innerText = response.top_priority.count;
            }

            renderUnitsGrid(response.units);
        } catch (error) {
            console.error('Falha ao carregar métricas:', error);
        }
    }

    function renderUnitsGrid(units) {
        const grid = document.getElementById('unitsGrid');
        if(!units || units.length === 0) {
            grid.innerHTML = `<div class="col-12"><div class="alert alert-info">Nenhuma unidade ativa encontrada.</div></div>`;
            return;
        }

        let html = '';
        units.forEach(unit => {
            html += `
            <div class="col-md-4 mb-3">
                <div class="card border-0 shadow-sm h-100 cursor-pointer" onclick="openUnitMonitor('${unit.id}', '${unit.name}')" style="cursor:pointer; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0 text-dark">${unit.name}</h5>
                            <span class="badge bg-light text-dark border"><i class="bi bi-circle-fill text-success" style="font-size: 0.5rem; vertical-align: middle;"></i> Online</span>
                        </div>
                        <div class="d-flex justify-content-between text-secondary">
                            <div>
                                <h4 class="fw-bold text-dark mb-0">${unit.pending}</h4>
                                <small>Na Fila</small>
                            </div>
                            <div class="text-end">
                                <h4 class="fw-bold text-primary mb-0">${unit.in_progress}</h4>
                                <small>Sendo Atendidos</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>`;
        });
        grid.innerHTML = html;
    }

    async function openUnitMonitor(unitId, unitName) {
        document.getElementById('monitorModalTitle').innerText = unitName;
        document.getElementById('monitorModalBody').innerHTML = `<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></div>`;
        new window.ModalService('unitMonitorModal').show();

        fetchUnitDetails(unitId);
        clearInterval(modalPollingInterval);
        modalPollingInterval = setInterval(() => fetchUnitDetails(unitId), 5000);
    }

    async function fetchUnitDetails(unitId) {
        try {
            const response = await window.HttpService.get(`/admin/dashboard/units/${unitId}/monitor`);
            let html = '';
            
            if(response.areas.length === 0) {
                html = '<div class="alert alert-warning">Nenhuma área configurada nesta unidade.</div>';
            } else {
                response.areas.forEach(area => {
                    html += `
                    <div class="border rounded p-3 mb-3 bg-light-soft">
                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3"><i class="bi bi-geo-alt-fill text-primary me-2"></i>${area.name}</h6>
                        
                        <div class="row text-center mb-3">
                            <div class="col">
                                <h3 class="fw-bold text-dark mb-0">${area.tickets_pending}</h3>
                                <small class="text-secondary">Fila</small>
                            </div>
                            <div class="col">
                                <h3 class="fw-bold text-primary mb-0">${area.tickets_in_progress}</h3>
                                <small class="text-secondary">Atendimento</small>
                            </div>
                            <div class="col">
                                <h3 class="fw-bold text-success mb-0">${area.tickets_completed_today}</h3>
                                <small class="text-secondary">Concluídos</small>
                            </div>
                        </div>

                        <div class="d-flex gap-2 justify-content-center">
                            <span class="badge bg-secondary"><i class="bi bi-terminal me-1"></i> ${area.totems_count} Totens Ativos</span>
                            <span class="badge bg-secondary"><i class="bi bi-display me-1"></i> ${area.tvs_count} TVs Ativas</span>
                            <span class="badge bg-secondary"><i class="bi bi-person-workspace me-1"></i> ${area.counters_count} Guichês Ativos</span>
                        </div>
                    </div>`;
                });
            }
            
            document.getElementById('monitorModalBody').innerHTML = html;
        } catch(error) {
            console.error('Falha ao carregar detalhes da unidade:', error);
        }
    }
</script>
@endpush
