// resources/ts/services/ModalService.ts
import { Modal } from 'bootstrap';

export class ModalService {
    private bsModal: Modal;
    private element: HTMLElement;

    constructor(elementId: string) {
        this.element = document.getElementById(elementId) as HTMLElement;
        if (!this.element) {
            throw new Error(`Modal element #${elementId} not found`);
        }
        this.bsModal = new Modal(this.element);
    }

    show() {
        this.bsModal.show();
    }

    hide() {
        this.bsModal.hide();
    }

    onHidden(callback: () => void) {
        this.element.addEventListener('hidden.bs.modal', callback, { once: true });
    }

    static confirm(title: string, message: string): Promise<boolean> {
        return new Promise((resolve) => {
            // Simplified confirm modal generation
            const id = 'confirm-modal-' + Date.now();
            const html = `
                <div class="modal fade" id="${id}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header border-0 pb-0">
                                <h5 class="modal-title fw-bold">${title}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-secondary">
                                ${message}
                            </div>
                            <div class="modal-footer border-0 pt-0">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal" id="${id}-no">Cancelar</button>
                                <button type="button" class="btn btn-danger" id="${id}-yes">Confirmar</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', html);
            
            const modalEl = document.getElementById(id)!;
            const modal = new Modal(modalEl);
            modal.show();

            document.getElementById(`${id}-yes`)?.addEventListener('click', () => {
                modal.hide();
                resolve(true);
            });
            document.getElementById(`${id}-no`)?.addEventListener('click', () => {
                resolve(false);
            });

            modalEl.addEventListener('hidden.bs.modal', () => {
                modalEl.remove();
                resolve(false); // Resolve false if closed by other means
            });
        });
    }
}
