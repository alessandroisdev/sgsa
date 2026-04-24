// resources/ts/app.ts
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

import { HttpService } from './services/HttpService';
import { ToastService } from './services/ToastService';
import { ModalService } from './services/ModalService';
import { FormHandler } from './services/FormHandler';
import { DataTableRenderer } from './services/DataTableRenderer';

// Expose globally for inline scripts (Blade)
(window as any).HttpService = HttpService;
(window as any).ToastService = ToastService;
(window as any).ModalService = ModalService;
(window as any).FormHandler = FormHandler;
(window as any).DataTableRenderer = DataTableRenderer;

document.addEventListener('DOMContentLoaded', () => {
    console.log('SGSA Admin Core initialized.');
});
