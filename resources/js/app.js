import Alpine from 'alpinejs';
import sidebar from './components/sidebar';
import themeSwitcher from './components/theme-switcher';

window.Alpine = Alpine;

Alpine.data('sidebar', sidebar);
Alpine.data('themeSwitcher', themeSwitcher);

Alpine.start();
