// resources/ts/services/FormHandler.ts
import { HttpService } from './HttpService';
import { ToastService } from './ToastService';
import { ModalService } from './ModalService';

export class FormHandler {
    static init(formId: string, onSuccess?: (data: any) => void, modalIdToClose?: string) {
        const form = document.getElementById(formId) as HTMLFormElement;
        if (!form) return;

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const submitBtn = form.querySelector('button[type="submit"]') as HTMLButtonElement;
            const originalText = submitBtn.innerHTML;
            
            // Lock button to prevent double submit
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Processando...';

            // Clear previous errors
            form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            form.querySelectorAll('.invalid-feedback').forEach(el => el.remove());

            try {
                const formData = new FormData(form);
                const url = form.getAttribute('action') || '';
                const method = (form.getAttribute('method') || 'POST').toUpperCase();
                
                // If it's a PUT/PATCH mapped through form fields
                const overrideMethod = formData.get('_method') as string;
                const finalMethod = overrideMethod ? overrideMethod.toUpperCase() : method;
                
                const data = await HttpService.request(url, finalMethod, formData);
                
                ToastService.show(data.message || 'Operação realizada com sucesso!', 'success');
                
                if (modalIdToClose) {
                    const modal = document.getElementById(modalIdToClose);
                    if (modal) {
                         // Close using bootstrap Modal instance
                         // Since we don't have the exact instance, we dispatch the hide event 
                         // or we can use bootstrap's getInstance
                         const bootstrap = (window as any).bootstrap;
                         if (bootstrap) {
                             const bsModal = bootstrap.Modal.getInstance(modal);
                             if (bsModal) bsModal.hide();
                         }
                    }
                }

                if (onSuccess) onSuccess(data);

            } catch (error: any) {
                if (error.status === 422 && error.data && error.data.errors) {
                    // Validation errors
                    ToastService.show('Verifique os campos preenchidos.', 'warning');
                    Object.keys(error.data.errors).forEach(field => {
                        const input = form.querySelector(`[name="${field}"]`);
                        if (input) {
                            input.classList.add('is-invalid');
                            const feedback = document.createElement('div');
                            feedback.className = 'invalid-feedback';
                            feedback.innerText = error.data.errors[field][0];
                            input.parentElement?.appendChild(feedback);
                        }
                    });
                } else {
                    ToastService.show(error.data?.message || 'Erro inesperado ao processar solicitação.', 'danger');
                }
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
    }
}
