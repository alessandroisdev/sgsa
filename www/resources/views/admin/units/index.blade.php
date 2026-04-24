@extends('layouts.admin')

@section('title', 'Unidades de Atendimento')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="mb-1 fw-bold text-dark">Unidades</h3>
            <p class="text-secondary mb-0">Gerencie as unidades de atendimento (Filiais).</p>
        </div>
        <button class="btn btn-primary px-4 py-2" onclick="openCreateModal()">
            <i class="bi bi-plus-lg me-2"></i> Nova Unidade
        </button>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 w-100" id="unitsTable">
                <thead class="bg-light">
                    <!-- Dynamic Headers via TS -->
                </thead>
                <tbody>
                    <!-- Dynamic Content via TS -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Create/Edit -->
<div class="modal fade" id="unitModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="unitForm" action="{{ route('admin.units.store') }}" method="POST">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold" id="modalTitle">Nova Unidade</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-medium">Nome da Unidade</label>
                        <input type="text" class="form-control" name="name" id="unitName" required placeholder="Ex: Filial Centro">
                    </div>
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" role="switch" name="active" id="unitActive" checked value="1">
                        <label class="form-check-label" for="unitActive">Unidade Ativa</label>
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
        // Initialize DataTable
        const columnsJson = '{!! $columnsJson !!}';
        const renderer = new window.DataTableRenderer('unitsTable', '{{ route('admin.units.index') }}', columnsJson);
        renderer.load();

        // Initialize FormHandler
        window.FormHandler.init('unitForm', () => {
            renderer.load(); // reload table on success
        }, 'unitModal');
    });

    function openCreateModal() {
        document.getElementById('modalTitle').innerText = 'Nova Unidade';
        document.getElementById('unitForm').reset();
        document.getElementById('unitForm').setAttribute('action', '{{ route('admin.units.store') }}');
        document.getElementById('formMethod').value = 'POST';
        
        // Remove error classes
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        document.querySelectorAll('.invalid-feedback').forEach(el => el.remove());

        new window.ModalService('unitModal').show();
    }

    async function editUnit(id) {
        try {
            const unit = await window.HttpService.get(`/admin/units/${id}/edit`);
            
            document.getElementById('modalTitle').innerText = 'Editar Unidade';
            document.getElementById('unitName').value = unit.name;
            document.getElementById('unitActive').checked = unit.active;
            
            document.getElementById('unitForm').setAttribute('action', `/admin/units/${id}`);
            document.getElementById('formMethod').value = 'PUT';

            new window.ModalService('unitModal').show();
        } catch (error) {
            window.ToastService.show('Erro ao carregar dados da unidade.', 'danger');
        }
    }

    async function deleteUnit(id) {
        const confirmed = await window.ModalService.confirm(
            'Remover Unidade?', 
            'Tem certeza que deseja remover esta unidade? Os dados associados poderão ser afetados.'
        );
        
        if (confirmed) {
            try {
                const response = await window.HttpService.delete(`/admin/units/${id}`);
                window.ToastService.show(response.message || 'Removido com sucesso', 'success');
                // Reload table
                const columnsJson = '{!! $columnsJson !!}';
                const renderer = new window.DataTableRenderer('unitsTable', '{{ route('admin.units.index') }}', columnsJson);
                renderer.load();
            } catch (error) {
                window.ToastService.show('Erro ao remover unidade.', 'danger');
            }
        }
    }
</script>
@endpush
