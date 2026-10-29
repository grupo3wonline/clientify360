// Filament panel entry: Filament ships its own Alpine/Livewire, so only the shared
// loading overlay is added here (the consent module joins in a later phase).
import { initLoadingOverlay } from './loading-overlay';

initLoadingOverlay();
