@extends('layouts.admin')

@section('title', 'Usuários e Permissões')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="mb-1 fw-bold text-dark">Gestão de Usuários</h3>
            <p class="text-secondary mb-0">Gerencie administradores e atendentes do sistema.</p>
        </div>
        <button class="btn btn-primary px-4 py-2" onclick="openCreateModal()">
            <i class="bi bi-plus-lg me-2"></i> Novo Usuário
        </button>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 w-100" id="usersTable">
                <thead class="bg-light"></thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Usuário -->
<div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="userForm" action="{{ route('admin.users.store') }}" method="POST">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold" id="modalTitle">Novo Usuário</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-medium">Nome Completo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" id="userName" required placeholder="Ex: João Silva">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-medium">E-mail <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email" id="userEmail" required placeholder="Ex: joao@clinica.com">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-medium">Senha <span class="text-danger" id="passwordAsterisk">*</span></label>
                            <input type="password" class="form-control" name="password" id="userPassword" placeholder="No mínimo 6 caracteres">
                            <div class="form-text" id="passwordHelp"></div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-medium">Nível de Acesso <span class="text-danger">*</span></label>
                            <select class="form-select" name="role" id="userRole" required onchange="togglePermissionsPanel()">
                                <option value="attendant">Atendente (Acesso apenas ao Guichê)</option>
                                <option value="admin">Administrador (Acesso Total ao Painel)</option>
                            </select>
                        </div>
                    </div>

                    <div id="permissionsPanel" class="mt-3 p-3 bg-light rounded border">
                        <h6 class="fw-bold mb-3 text-primary"><i class="bi bi-shield-lock me-2"></i>Vínculos de Acesso (RBAC)</h6>
                        <p class="small text-secondary mb-3">
                            Selecione onde este atendente poderá trabalhar. Vincular a uma Unidade dá acesso a todas as suas áreas. Vincular a uma Área limita o acesso estritamente a ela.
                        </p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <label class="fw-bold mb-2">Unidades Vinculadas</label>
                                <div class="border rounded bg-white p-2" style="max-height: 200px; overflow-y: auto;">
                                    @forelse($units as $unit)
                                    <div class="form-check">
                                        <input class="form-check-input unit-checkbox" type="checkbox" name="unit_ids[]" value="{{ $unit->id }}" id="unit_{{ $unit->id }}">
                                        <label class="form-check-label" for="unit_{{ $unit->id }}">
                                            {{ $unit->name }}
                                        </label>
                                    </div>
                                    @empty
                                    <small class="text-muted">Nenhuma unidade cadastrada.</small>
                                    @endforelse
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="fw-bold mb-2">Áreas Vinculadas</label>
                                <div class="border rounded bg-white p-2" style="max-height: 200px; overflow-y: auto;">
                                    @forelse($areas as $area)
                                    <div class="form-check">
                                        <input class="form-check-input area-checkbox" type="checkbox" name="area_ids[]" value="{{ $area->id }}" id="area_{{ $area->id }}">
                                        <label class="form-check-label" for="area_{{ $area->id }}">
                                            {{ $area->name }} <small class="text-muted">({{ $area->unit->name ?? '' }})</small>
                                        </label>
                                    </div>
                                    @empty
                                    <small class="text-muted">Nenhuma área cadastrada.</small>
                                    @endforelse
                                </div>
                            </div>
                        </div>
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
        const renderer = new window.DataTableRenderer('usersTable', '{{ route('admin.users.index') }}', '{!! $columnsJson !!}');
        renderer.load();

        window.FormHandler.init('userForm', () => {
            renderer.load();
        }, 'userModal');
    });

    function togglePermissionsPanel() {
        const role = document.getElementById('userRole').value;
        const panel = document.getElementById('permissionsPanel');
        if(role === 'attendant') {
            panel.style.display = 'block';
        } else {
            panel.style.display = 'none';
        }
    }

    function openCreateModal() {
        document.getElementById('modalTitle').innerText = 'Novo Usuário';
        document.getElementById('userForm').reset();
        document.getElementById('userForm').setAttribute('action', '{{ route('admin.users.store') }}');
        document.getElementById('formMethod').value = 'POST';
        
        document.getElementById('userPassword').required = true;
        document.getElementById('passwordAsterisk').style.display = 'inline';
        document.getElementById('passwordHelp').innerText = '';
        
        // Reset checkboxes
        document.querySelectorAll('.unit-checkbox, .area-checkbox').forEach(cb => cb.checked = false);
        
        togglePermissionsPanel();
        new window.ModalService('userModal').show();
    }

    async function editEntity(id) {
        try {
            const data = await window.HttpService.get(`/admin/users/${id}/edit`);
            const user = data.user;
            
            document.getElementById('modalTitle').innerText = 'Editar Usuário';
            document.getElementById('userName').value = user.name;
            document.getElementById('userEmail').value = user.email;
            document.getElementById('userRole').value = user.role;
            
            document.getElementById('userPassword').required = false;
            document.getElementById('passwordAsterisk').style.display = 'none';
            document.getElementById('passwordHelp').innerText = 'Deixe em branco para manter a senha atual.';
            
            document.getElementById('userForm').setAttribute('action', `/admin/users/${id}`);
            document.getElementById('formMethod').value = 'PUT';
            
            // Checkboxes
            document.querySelectorAll('.unit-checkbox').forEach(cb => {
                cb.checked = data.unit_ids.includes(cb.value);
            });
            document.querySelectorAll('.area-checkbox').forEach(cb => {
                cb.checked = data.area_ids.includes(cb.value);
            });

            togglePermissionsPanel();
            new window.ModalService('userModal').show();
        } catch (error) {
            window.ToastService.show('Erro ao carregar dados.', 'danger');
        }
    }

    async function deleteEntity(id) {
        if (await window.ModalService.confirm('Remover?', 'Tem certeza que deseja excluir este usuário? Ele perderá acesso ao sistema.')) {
            try {
                const response = await window.HttpService.delete(`/admin/users/${id}`);
                window.ToastService.show(response.message, 'success');
                const renderer = new window.DataTableRenderer('usersTable', '{{ route('admin.users.index') }}', '{!! $columnsJson !!}');
                renderer.load();
            } catch (error) {
                window.ToastService.show(error.message || 'Erro ao remover.', 'danger');
            }
        }
    }
</script>
@endpush
