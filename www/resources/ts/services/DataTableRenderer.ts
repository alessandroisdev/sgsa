// resources/ts/services/DataTableRenderer.ts
import { HttpService } from './HttpService';
import { ToastService } from './ToastService';

export class DataTableRenderer {
    private tableId: string;
    private url: string;
    private columns: any[];

    constructor(tableId: string, url: string, columnsJson: string) {
        this.tableId = tableId;
        this.url = url;
        this.columns = JSON.parse(columnsJson);
    }

    async load() {
        const tbody = document.querySelector(`#${this.tableId} tbody`);
        if (!tbody) return;

        tbody.innerHTML = `<tr><td colspan="${this.columns.length}" class="text-center py-4"><span class="spinner-border spinner-border-sm text-primary"></span> Carregando...</td></tr>`;

        try {
            // Very simplified version for the proof of concept. 
            // In a real scenario, this would integrate with Datatables.net or a custom pagination handler.
            // For this phase, we just load the initial data via the BaseDataTable format.
            const response = await HttpService.post(this.url, {
                draw: 1,
                start: 0,
                length: 50
            });

            tbody.innerHTML = '';
            
            if (response.data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="${this.columns.length}" class="text-center text-muted py-4">Nenhum registro encontrado.</td></tr>`;
                return;
            }

            response.data.forEach((row: any) => {
                const tr = document.createElement('tr');
                this.columns.forEach(col => {
                    const td = document.createElement('td');
                    td.innerHTML = row[col.data] ?? '';
                    tr.appendChild(td);
                });
                tbody.appendChild(tr);
            });

        } catch (error) {
            tbody.innerHTML = `<tr><td colspan="${this.columns.length}" class="text-center text-danger py-4">Erro ao carregar dados.</td></tr>`;
            ToastService.show('Erro ao carregar tabela.', 'danger');
        }
    }
}
