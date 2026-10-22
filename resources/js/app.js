import './bootstrap';

import Alpine from 'alpinejs';
import documentField from './masks';

window.Alpine = Alpine;

Alpine.data('documentField', documentField);

Alpine.start();
