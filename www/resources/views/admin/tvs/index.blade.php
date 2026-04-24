@extends('layouts.admin')

@section('title', 'TVs / Painéis de Chamada')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="mb-1 fw-bold text-dark">TVs e Painéis</h3>
            <p class="text-secondary mb-0">Gerencie os displays de chamada de senha.</p>
        </div>
        <button class="btn btn-primary px-4 py-2" onclick="openCreateModal()">
            <i class="bi bi-plus-lg me-2"></i> Nova TV
        </button>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 w-100" id="tvsTable">
                <thead class="bg-light"></thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="tvModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="tvForm" action="{{ route('admin.tvs.store') }}" method="POST">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold" id="modalTitle">Nova TV</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-medium">Área de Atendimento <span class="text-danger">*</span></label>
                        <select class="form-select" name="area_id" id="tvAreaId" required>
                            <option value="">Selecione...</option>
                            @foreach($areas as $area)
                                <option value="{{ $area->id }}">{{ $area->name }} ({{ $area->unit->name ?? 'Sem Unidade' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Nome de Exibição <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" id="tvName" required placeholder="Ex: TV Principal Recepção">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Identificador do Dispositivo (Handshake) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="device_identifier" id="tvIdentifier" required placeholder="Ex: TV-RECEP-01">
                        <div class="form-text">Este ID deve ser inserido na configuração do aplicativo Electron da TV.</div>
                    </div>
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" role="switch" name="active" id="tvActive" checked value="1">
                        <label class="form-check-label" for="tvActive">TV Ativa</label>
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
        const renderer = new window.DataTableRenderer('tvsTable', '{{ route('admin.tvs.index') }}', '{!! $columnsJson !!}');
        renderer.load();

        window.FormHandler.init('tvForm', () => {
            renderer.load();
        }, 'tvModal');
    });

    function openCreateModal() {
        document.getElementById('modalTitle').innerText = 'Nova TV';
        document.getElementById('tvForm').reset();
        document.getElementById('tvForm').setAttribute('action', '{{ route('admin.tvs.store') }}');
        document.getElementById('formMethod').value = 'POST';
        new window.ModalService('tvModal').show();
    }

    async function editEntity(id) {
        try {
            const data = await window.HttpService.get(`/admin/tvs/${id}/edit`);
            document.getElementById('modalTitle').innerText = 'Editar TV';
            document.getElementById('tvAreaId').value = data.area_id;
            document.getElementById('tvName').value = data.name;
            document.getElementById('tvIdentifier').value = data.device_identifier;
            document.getElementById('tvActive').checked = data.active;
            
            document.getElementById('tvForm').setAttribute('action', `/admin/tvs/${id}`);
            document.getElementById('formMethod').value = 'PUT';

            new window.ModalService('tvModal').show();
        } catch (error) {
            window.ToastService.show('Erro ao carregar dados.', 'danger');
        }
    }

    async function deleteEntity(id) {
        if (await window.ModalService.confirm('Remover?', 'Tem certeza que deseja remover?')) {
            try {
                const response = await window.HttpService.delete(`/admin/tvs/${id}`);
                window.ToastService.show(response.message, 'success');
                const renderer = new window.DataTableRenderer('tvsTable', '{{ route('admin.tvs.index') }}', '{!! $columnsJson !!}');
                renderer.load();
            } catch (error) {
                window.ToastService.show('Erro ao remover.', 'danger');
            }
        }
    }
</script>
@endpush
