@extends('layouts.admin')

@section('title', 'Serviços e Filas')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="mb-1 fw-bold text-dark">Serviços</h3>
            <p class="text-secondary mb-0">Gerencie os serviços que gerarão filas (Ex: Clínico Geral, Exames).</p>
        </div>
        <button class="btn btn-primary px-4 py-2" onclick="openCreateModal()">
            <i class="bi bi-plus-lg me-2"></i> Novo Serviço
        </button>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 w-100" id="servicesTable">
                <thead class="bg-light"></thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="serviceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="serviceForm" action="{{ route('admin.services.store') }}" method="POST">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold" id="modalTitle">Novo Serviço</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-medium">Área de Atendimento <span class="text-danger">*</span></label>
                        <select class="form-select" name="area_id" id="serviceAreaId" required>
                            <option value="">Selecione...</option>
                            @foreach($areas as $area)
                                <option value="{{ $area->id }}">{{ $area->name }} ({{ $area->unit->name ?? 'Sem Unidade' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Nome do Serviço <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" id="serviceName" required placeholder="Ex: Clínico Geral">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Prefixo da Senha <span class="text-danger">*</span></label>
                        <input type="text" class="form-control text-uppercase" name="prefix" id="servicePrefix" required maxlength="5" placeholder="Ex: MED">
                        <div class="form-text">As senhas serão geradas como MED-001, MED-002...</div>
                    </div>
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" role="switch" name="active" id="serviceActive" checked value="1">
                        <label class="form-check-label" for="serviceActive">Serviço Ativo</label>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary px-4">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const renderer = new window.DataTableRenderer('servicesTable', '{{ route('admin.services.index') }}', '{!! $columnsJson !!}');
        renderer.load();

        window.FormHandler.init('serviceForm', () => {
            renderer.load();
        }, 'serviceModal');
    });

    function openCreateModal() {
        document.getElementById('modalTitle').innerText = 'Novo Serviço';
        document.getElementById('serviceForm').reset();
        document.getElementById('serviceForm').setAttribute('action', '{{ route('admin.services.store') }}');
        document.getElementById('formMethod').value = 'POST';
        new window.ModalService('serviceModal').show();
    }

    async function editEntity(id) {
        try {
            const data = await window.HttpService.get(`/admin/services/${id}/edit`);
            document.getElementById('modalTitle').innerText = 'Editar Serviço';
            document.getElementById('serviceAreaId').value = data.area_id;
            document.getElementById('serviceName').value = data.name;
            document.getElementById('servicePrefix').value = data.prefix;
            document.getElementById('serviceActive').checked = data.active;
            
            document.getElementById('serviceForm').setAttribute('action', `/admin/services/${id}`);
            document.getElementById('formMethod').value = 'PUT';

            new window.ModalService('serviceModal').show();
        } catch (error) {
            window.ToastService.show('Erro ao carregar dados.', 'danger');
        }
    }

    async function deleteEntity(id) {
        if (await window.ModalService.confirm('Remover?', 'Tem certeza que deseja remover este serviço?')) {
            try {
                const response = await window.HttpService.delete(`/admin/services/${id}`);
                window.ToastService.show(response.message, 'success');
                const renderer = new window.DataTableRenderer('servicesTable', '{{ route('admin.services.index') }}', '{!! $columnsJson !!}');
                renderer.load();
            } catch (error) {
                window.ToastService.show('Erro ao remover.', 'danger');
            }
        }
    }
</script>
@endpush
