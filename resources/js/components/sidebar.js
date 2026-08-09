export default function () {
    return {
        collapsed: (() => {
            try {
                return JSON.parse(localStorage.getItem('sidebar-collapsed') || 'false') === true;
            } catch {
                return false;
            }
        })(),

        mobileOpen: false,

        toggle() {
            this.collapsed = !this.collapsed;

            try {
                localStorage.setItem('sidebar-collapsed', JSON.stringify(this.collapsed));
            } catch {
                // Storage unavailable — the collapse still applies for this session.
            }
        },

        close() {
            this.mobileOpen = false;
        },
    };
}
