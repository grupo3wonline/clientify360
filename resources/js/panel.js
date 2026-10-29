// Filament panel entry: Filament ships its own Alpine/Livewire, so only the shared
// loading overlay and the cookie-consent module are added here.
import { initLoadingOverlay } from './loading-overlay';
import { initConsent } from './consent';

initLoadingOverlay();
initConsent();
