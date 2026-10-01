import './bootstrap';

import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import Swal from 'sweetalert2';

window.Swal = Swal;
window.Alpine = Alpine;

Alpine.plugin(collapse);

Alpine.start();
