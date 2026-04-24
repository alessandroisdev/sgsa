// resources/ts/services/ToastService.ts
import { Toast } from 'bootstrap';

export class ToastService {
    static show(message: string, type: 'success' | 'danger' | 'warning' | 'info' = 'info') {
        const container = document.getElementById('toast-container');
        if (!container) return;

        const id = 'toast-' + Date.now();
        const icon = type === 'success' ? 'bi-check-circle' : (type === 'danger' ? 'bi-x-circle' : 'bi-info-circle');

        const toastHtml = `
            <div id="${id}" class="toast align-items-center text-bg-${type} border-0 mb-2" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="bi ${icon} me-2"></i> ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', toastHtml);
        const toastElement = document.getElementById(id);
        if (toastElement) {
            const bsToast = new Toast(toastElement, { delay: 4000 });
            bsToast.show();
            toastElement.addEventListener('hidden.bs.toast', () => {
                toastElement.remove();
            });
        }
    }
}
