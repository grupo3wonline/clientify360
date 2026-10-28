import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import Swal from 'sweetalert2';
import documentField from './masks';

// Expose SweetAlert2 globally: Blade views call `Swal.fire(...)` inline.
window.Swal = Swal;

window.Alpine = Alpine;

// `x-collapse` is used by the public FAQ accordion.
Alpine.plugin(collapse);
Alpine.data('documentField', documentField);

Alpine.start();
