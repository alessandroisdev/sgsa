@extends('layouts.admin')

@section('title', 'Guichês')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="mb-1 fw-bold text-dark">Guichês de Atendimento</h3>
            <p class="text-secondary mb-0">Gerencie os postos de atendimento onde os atendentes operam.</p>
        </div>
        <button class="btn btn-primary px-4 py-2" onclick="openCreateModal()">
            <i class="bi bi-plus-lg me-2"></i> Novo Guichê
        </button>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 w-100" id="countersTable">
                <thead class="bg-light"></thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="counterModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="counterForm" action="{{ route('admin.counters.store') }}" method="POST">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold" id="modalTitle">Novo Guichê</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-medium">Área de Atendimento <span class="text-danger">*</span></label>
                        <select class="form-select" name="area_id" id="counterAreaId" required>
                            <option value="">Selecione...</option>
                            @foreach($areas as $area)
                                <option value="{{ $area->id }}">{{ $area->name }} ({{ $area->unit->name ?? 'Sem Unidade' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Identificação do Guichê <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" id="counterName" required placeholder="Ex: Guichê 01">
                    </div>
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" role="switch" name="active" id="counterActive" checked value="1">
                        <label class="form-check-label" for="counterActive">Guichê Ativo</label>
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
        const renderer = new window.DataTableRenderer('countersTable', '{{ route('admin.counters.index') }}', '{!! $columnsJson !!}');
        renderer.load();

        window.FormHandler.init('counterForm', () => {
            renderer.load();
        }, 'counterModal');
    });

    function openCreateModal() {
        document.getElementById('modalTitle').innerText = 'Novo Guichê';
        document.getElementById('counterForm').reset();
        document.getElementById('counterForm').setAttribute('action', '{{ route('admin.counters.store') }}');
        document.getElementById('formMethod').value = 'POST';
        new window.ModalService('counterModal').show();
    }

    async function editEntity(id) {
        try {
            const data = await window.HttpService.get(`/admin/counters/${id}/edit`);
            document.getElementById('modalTitle').innerText = 'Editar Guichê';
            document.getElementById('counterAreaId').value = data.area_id;
            document.getElementById('counterName').value = data.name;
            document.getElementById('counterActive').checked = data.active;
            
            document.getElementById('counterForm').setAttribute('action', `/admin/counters/${id}`);
            document.getElementById('formMethod').value = 'PUT';

            new window.ModalService('counterModal').show();
        } catch (error) {
            window.ToastService.show('Erro ao carregar dados.', 'danger');
        }
    }

    async function deleteEntity(id) {
        if (await window.ModalService.confirm('Remover?', 'Tem certeza que deseja remover este guichê?')) {
            try {
                const response = await window.HttpService.delete(`/admin/counters/${id}`);
                window.ToastService.show(response.message, 'success');
                const renderer = new window.DataTableRenderer('countersTable', '{{ route('admin.counters.index') }}', '{!! $columnsJson !!}');
                renderer.load();
            } catch (error) {
                window.ToastService.show('Erro ao remover.', 'danger');
            }
        }
    }
</script>
@endpush
