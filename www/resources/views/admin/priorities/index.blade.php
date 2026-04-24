@extends('layouts.admin')

@section('title', 'Prioridades')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="mb-1 fw-bold text-dark">Tipos de Prioridade</h3>
            <p class="text-secondary mb-0">Gerencie os pesos de atendimento (Ex: Idosos, Gestantes, Preferencial).</p>
        </div>
        <button class="btn btn-primary px-4 py-2" onclick="openCreateModal()">
            <i class="bi bi-plus-lg me-2"></i> Nova Prioridade
        </button>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 w-100" id="prioritiesTable">
                <thead class="bg-light"></thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="priorityModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="priorityForm" action="{{ route('admin.priorities.store') }}" method="POST">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold" id="modalTitle">Nova Prioridade</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-medium">Nome <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" id="priorityName" required placeholder="Ex: Preferencial +60">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Peso (0-10) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="weight" id="priorityWeight" required min="0" max="10" value="0">
                        <div class="form-text">Pesos maiores são chamados antes. 0 é Regular.</div>
                    </div>
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" role="switch" name="active" id="priorityActive" checked value="1">
                        <label class="form-check-label" for="priorityActive">Ativa</label>
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
        const renderer = new window.DataTableRenderer('prioritiesTable', '{{ route('admin.priorities.index') }}', '{!! $columnsJson !!}');
        renderer.load();

        window.FormHandler.init('priorityForm', () => {
            renderer.load();
        }, 'priorityModal');
    });

    function openCreateModal() {
        document.getElementById('modalTitle').innerText = 'Nova Prioridade';
        document.getElementById('priorityForm').reset();
        document.getElementById('priorityForm').setAttribute('action', '{{ route('admin.priorities.store') }}');
        document.getElementById('formMethod').value = 'POST';
        new window.ModalService('priorityModal').show();
    }

    async function editEntity(id) {
        try {
            const data = await window.HttpService.get(`/admin/priorities/${id}/edit`);
            document.getElementById('modalTitle').innerText = 'Editar Prioridade';
            document.getElementById('priorityName').value = data.name;
            document.getElementById('priorityWeight').value = data.weight;
            document.getElementById('priorityActive').checked = data.active;
            
            document.getElementById('priorityForm').setAttribute('action', `/admin/priorities/${id}`);
            document.getElementById('formMethod').value = 'PUT';

            new window.ModalService('priorityModal').show();
        } catch (error) {
            window.ToastService.show('Erro ao carregar dados.', 'danger');
        }
    }

    async function deleteEntity(id) {
        if (await window.ModalService.confirm('Remover?', 'Tem certeza que deseja remover?')) {
            try {
                const response = await window.HttpService.delete(`/admin/priorities/${id}`);
                window.ToastService.show(response.message, 'success');
                const renderer = new window.DataTableRenderer('prioritiesTable', '{{ route('admin.priorities.index') }}', '{!! $columnsJson !!}');
                renderer.load();
            } catch (error) {
                window.ToastService.show('Erro ao remover.', 'danger');
            }
        }
    }
</script>
@endpush
