export default function () {
    const el = this.$el;

    return {
        open: false,

        themes: JSON.parse(el.dataset.themes || '[]'),
        labels: JSON.parse(el.dataset.labels || '{}'),
        cookieName: el.dataset.cookieName || 'theme',
        defaultTheme: el.dataset.defaultTheme || 'light',

        get current() {
            return document.documentElement.dataset.theme || this.defaultTheme;
        },

        toggle() {
            this.open = !this.open;
        },

        toggleTheme() {
            const index = this.themes.indexOf(this.current);
            const next = this.themes[(index + 1) % this.themes.length] ?? this.themes[0] ?? this.defaultTheme;
            this.setTheme(next);
        },

        setTheme(theme) {
            document.documentElement.dataset.theme = theme;
            document.cookie = `${this.cookieName}=${theme}; path=/; max-age=31536000; SameSite=Lax`;
            this.open = false;
        },
    };
}
