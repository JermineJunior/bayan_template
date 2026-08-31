import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';
import mask from '@alpinejs/mask';
import sidebar from './components/sidebar';
import themeSwitcher from './components/theme-switcher';
import oilChangeForm from './components/oil-change-form';
import filterChangeForm from './components/filter-change-form';
import notificationBell from './components/notification-bell';
import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';
import './maintenance';
window.Alpine = Alpine;
window.Swal = Swal;
window.Chart = Chart;

Alpine.plugin(mask);

Alpine.data('sidebar', sidebar);

Alpine.data('themeSwitcher', themeSwitcher);
Alpine.data('oilChangeForm', oilChangeForm);
Alpine.data('filterChangeForm', filterChangeForm);
Alpine.data('notificationBell', notificationBell);

Alpine.start();

// Format a numeric value as money with thousands separators; strips a trailing
// ".00" when the number is whole but keeps decimals when present.
window.formatMoney = (value) => {
    const num = parseFloat(value);
    if (Number.isNaN(num)) return '';
    return num.toLocaleString('en-US', {
        maximumFractionDigits: 2,
        minimumFractionDigits: num % 1 === 0 ? 0 : 2,
    });
};

// Parse a money-formatted string (e.g. "100,000.50") back into a number.
window.parseMoney = (value) => parseFloat(String(value).replace(/[^0-9.-]+/g, '')) || 0;

// Global delegation: any element with the .money-input class is formatted as the
// user types, and its comma-separated value is stripped back to a plain number
// before the surrounding form is submitted.
document.addEventListener('input', (e) => {
    const el = e.target.closest('.money-input');
    if (!el) return;
    const digits = String(el.value).replace(/[^0-9.]/g, '');
    if (digits !== '') {
        const [whole = '', ...rest] = digits.split('.');
        const dec = rest.length ? `.${rest.join('')}` : '';
        el.value = `${whole.replace(/\B(?=(\d{3})+(?!\d))/g, ',')}${dec}`;
    }
});

document.addEventListener('submit', (e) => {
    if (!e.target.matches('form')) return;
    e.target.querySelectorAll('.money-input').forEach((el) => {
        if (el.value) {
            el.value = String(el.value).replace(/,/g, '');
        }
    });
});

// Quick date-range buttons on report filter forms. Clicking one populates the
// from_date / to_date inputs of the enclosing form and submits it immediately.
document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-quick-range]');
    if (!btn) return;
    e.preventDefault();

    const form = btn.closest('form');
    if (!form) return;

    const from = form.querySelector('[name="from_date"]');
    const to = form.querySelector('[name="to_date"]');
    if (from) from.value = btn.dataset.from;
    if (to) to.value = btn.dataset.to;
});

// Confirm dialog used by the destructive-action forms (delete / end assignment).
// Returns false so the native submit is suppressed; the form is submitted
// manually only after the user confirms the dialog.
window.confirmForm = (form, text, confirmButtonText = 'نعم، تأكيد') => {
    Swal.fire({
        title: 'تأكيد العملية',
        text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText,
        cancelButtonText: 'إلغاء',
        confirmButtonColor: '#dc2626',
        reverseButtons: true,
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });

    return false;
};
