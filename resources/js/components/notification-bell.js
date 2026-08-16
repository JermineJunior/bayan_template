// Notification bell dropdown for the topbar. The dropdown's list is rendered
// server-side in the layout; this component only toggles it and updates read
// state in place via the mark-read endpoints, so nothing reloads the page.
export default function (markAllUrl, initialUnreadCount) {
    return {
        open: false,
        unreadCount: initialUnreadCount,

        toggle() {
            this.open = !this.open;
        },

        close() {
            this.open = false;
        },

        markRead(el) {
            const url = el.dataset.readUrl;
            if (!url) {
                return;
            }

            fetch(url, {
                method: 'PATCH',
                headers: this.jsonHeaders(),
            });

            this.markReadVisual(el);
            this.unreadCount = Math.max(0, this.unreadCount - 1);
        },

        markAllRead() {
            fetch(markAllUrl, {
                method: 'PATCH',
                headers: this.jsonHeaders(),
            });

            this.$root.querySelectorAll('[data-notification-item]').forEach((el) => {
                this.markReadVisual(el);
            });
            this.unreadCount = 0;
        },

        markReadVisual(el) {
            el.classList.remove('bg-primary/5');
            el.querySelector('[data-unread-dot]')?.remove();
            const message = el.querySelector('[data-message]');
            if (message) {
                message.classList.remove('font-medium', 'text-foreground');
                message.classList.add('text-muted-foreground');
            }
        },

        jsonHeaders() {
            return {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
            };
        },
    };
}
