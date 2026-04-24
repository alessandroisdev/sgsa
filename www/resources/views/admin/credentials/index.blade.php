@extends('layouts.admin')

@section('title', 'Impressão de Credenciais')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h3 class="mb-1 fw-bold text-dark">Impressão de Credenciais</h3>
        <p class="text-secondary mb-0">Selecione os dispositivos (Totens, TVs ou Guichês) para gerar as etiquetas com QR Code.</p>
    </div>
</div>

@if(session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif

<form action="{{ route('admin.credentials.printBatch') }}" method="POST" target="_blank" id="printForm">
    @csrf
    
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">Dispositivos Disponíveis</h5>
            <button type="submit" class="btn btn-primary px-4 py-2" id="printBtn" disabled>
                <i class="bi bi-printer me-2"></i> Imprimir Selecionados (<span id="selectedCount">0</span>)
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 w-100">
                    <thead class="bg-light">
                        <tr>
                            <th width="5%" class="text-center">
                                <input class="form-check-input" type="checkbox" id="selectAll">
                            </th>
                            <th>Tipo</th>
                            <th>Nome do Dispositivo</th>
                            <th>Unidade</th>
                            <th>Área</th>
                            <th>Identificador (UUID)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($devices as $device)
                        <tr>
                            <td class="text-center">
                                <input class="form-check-input device-checkbox" type="checkbox" name="identifiers[]" value="{{ $device['identifier'] }}">
                            </td>
                            <td>
                                @if($device['type'] == 'Totem')
                                    <span class="badge bg-primary"><i class="bi bi-terminal me-1"></i> Totem</span>
                                @elseif($device['type'] == 'TV')
                                    <span class="badge bg-info"><i class="bi bi-display me-1"></i> TV</span>
                                @else
                                    <span class="badge bg-secondary"><i class="bi bi-person-workspace me-1"></i> Guichê</span>
                                @endif
                            </td>
                            <td class="fw-bold">{{ $device['name'] }}</td>
                            <td>{{ $device['unit'] }}</td>
                            <td>{{ $device['area'] }}</td>
                            <td class="text-secondary small font-monospace">{{ $device['identifier'] }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-secondary">Nenhum dispositivo cadastrado.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.device-checkbox');
        const selectedCount = document.getElementById('selectedCount');
        const printBtn = document.getElementById('printBtn');

        function updateState() {
            const count = document.querySelectorAll('.device-checkbox:checked').length;
            selectedCount.innerText = count;
            printBtn.disabled = count === 0;
            selectAll.checked = checkboxes.length > 0 && count === checkboxes.length;
        }

        if(selectAll) {
            selectAll.addEventListener('change', (e) => {
                checkboxes.forEach(cb => cb.checked = e.target.checked);
                updateState();
            });
        }

        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateState);
        });
        
        // Uncheck all after submit since it opens a new tab
        document.getElementById('printForm').addEventListener('submit', () => {
            setTimeout(() => {
                checkboxes.forEach(cb => cb.checked = false);
                updateState();
            }, 1000);
        });
    });
</script>
@endpush
