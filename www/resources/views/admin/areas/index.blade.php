@extends('layouts.admin')

@section('title', 'Áreas de Atendimento')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="mb-1 fw-bold text-dark">Áreas</h3>
            <p class="text-secondary mb-0">Gerencie as áreas dentro das unidades (Ex: Recepção, Triagem).</p>
        </div>
        <button class="btn btn-primary px-4 py-2" onclick="openCreateModal()">
            <i class="bi bi-plus-lg me-2"></i> Nova Área
        </button>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 w-100" id="areasTable">
                <thead class="bg-light"></thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Create/Edit -->
<div class="modal fade" id="areaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="areaForm" action="{{ route('admin.areas.store') }}" method="POST">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold" id="modalTitle">Nova Área</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-medium">Unidade <span class="text-danger">*</span></label>
                        <select class="form-select" name="unit_id" id="areaUnitId" required>
                            <option value="">Selecione...</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Nome da Área <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" id="areaName" required placeholder="Ex: Recepção Principal">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Descrição</label>
                        <textarea class="form-control" name="description" id="areaDescription" rows="2"></textarea>
                    </div>
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" role="switch" name="active" id="areaActive" checked value="1">
                        <label class="form-check-label" for="areaActive">Área Ativa</label>
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
        const columnsJson = '{!! $columnsJson !!}';
        const renderer = new window.DataTableRenderer('areasTable', '{{ route('admin.areas.index') }}', columnsJson);
        renderer.load();

        window.FormHandler.init('areaForm', () => {
            renderer.load();
        }, 'areaModal');
    });

    function openCreateModal() {
        document.getElementById('modalTitle').innerText = 'Nova Área';
        document.getElementById('areaForm').reset();
        document.getElementById('areaForm').setAttribute('action', '{{ route('admin.areas.store') }}');
        document.getElementById('formMethod').value = 'POST';
        
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        document.querySelectorAll('.invalid-feedback').forEach(el => el.remove());

        new window.ModalService('areaModal').show();
    }

    async function editEntity(id) {
        try {
            const data = await window.HttpService.get(`/admin/areas/${id}/edit`);
            
            document.getElementById('modalTitle').innerText = 'Editar Área';
            document.getElementById('areaUnitId').value = data.unit_id;
            document.getElementById('areaName').value = data.name;
            document.getElementById('areaDescription').value = data.description || '';
            document.getElementById('areaActive').checked = data.active;
            
            document.getElementById('areaForm').setAttribute('action', `/admin/areas/${id}`);
            document.getElementById('formMethod').value = 'PUT';

            new window.ModalService('areaModal').show();
        } catch (error) {
            window.ToastService.show('Erro ao carregar dados.', 'danger');
        }
    }

    async function deleteEntity(id) {
        const confirmed = await window.ModalService.confirm('Remover?', 'Tem certeza que deseja remover esta área?');
        if (confirmed) {
            try {
                const response = await window.HttpService.delete(`/admin/areas/${id}`);
                window.ToastService.show(response.message, 'success');
                const renderer = new window.DataTableRenderer('areasTable', '{{ route('admin.areas.index') }}', '{!! $columnsJson !!}');
                renderer.load();
            } catch (error) {
                window.ToastService.show('Erro ao remover.', 'danger');
            }
        }
    }
</script>
@endpush
