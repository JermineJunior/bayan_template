import Alpine from 'alpinejs';
import sidebar from './components/sidebar';
import themeSwitcher from './components/theme-switcher';
import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

window.Alpine = Alpine;
window.Swal = Swal;

Alpine.data('sidebar', sidebar);
Alpine.data('themeSwitcher', themeSwitcher);

Alpine.start();

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
