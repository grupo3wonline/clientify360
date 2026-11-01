import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import Swal from 'sweetalert2';
import tokens from '../brand/tokens.json';
import documentField from './masks';
import countdown from './components/countdown';
import registerTheme from './theme';
import { initLoadingOverlay } from './loading-overlay';
import { initConsent } from './consent';

// Static assets (fonts) go through the Vite manifest, so Blade can preload them.
import.meta.glob(['../fonts/**']);

// Brand palette for inline scripts (e.g. SweetAlert2 confirm colors).
window.brandColors = tokens.colors;

// SweetAlert2 with the brand defaults; Blade views call `Swal.fire(...)`.
window.Swal = Swal.mixin({
    confirmButtonColor: tokens.colors.brand['600'],
    cancelButtonColor: tokens.colors.surface['500'],
    confirmButtonText: 'OK',
    cancelButtonText: 'Cancelar',
    customClass: { popup: 'rounded-2xl' },
});
window.Alpine = Alpine;

// `x-collapse` is used by the public FAQ accordion.
Alpine.plugin(collapse);
registerTheme(Alpine);
Alpine.data('documentField', documentField);
Alpine.data('countdown', countdown);

initLoadingOverlay();
initConsent();
Alpine.start();
