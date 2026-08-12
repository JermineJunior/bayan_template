// Quick-add modal for the filter-change form. Lets the user create a catalog
// filter on the spot (POST filters.store via fetch) without leaving the
// filter-change form and losing what they already typed. On success the new
// filter is appended to the main #filter_id <select> and selected
// automatically.
export default function (storeUrl) {
    return {
        filterModal: false,
        saving: false,

        filterName: '',
        filterCode: '',
        filterType: 'oil',
        filterLife: '',

        modalErrors: {},

        openFilterModal() {
            this.filterModal = true;
            this.modalErrors = {};
        },

        closeFilterModal() {
            if (this.saving) {
                return;
            }

            this.filterModal = false;
        },

        quickAddFilter() {
            this.saving = true;
            this.modalErrors = {};

            const formData = new FormData();
            formData.append('filter_name', this.filterName);
            formData.append('filter_code', this.filterCode);
            formData.append('filter_type', this.filterType);
            formData.append('filter_life', this.filterLife);

            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            fetch(storeUrl, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': token,
                },
                body: formData,
            })
                .then(async (response) => {
                    if (!response.ok) {
                        const data = await response.json().catch(() => ({}));

                        if (response.status === 422) {
                            this.modalErrors = data.errors || {};
                        } else {
                            this.modalErrors = { filter_name: 'حدث خطأ غير متوقع أثناء إضافة الفلتر.' };
                        }

                        return;
                    }

                    const filter = await response.json();
                    const select = document.getElementById('filter_id');
                    const option = document.createElement('option');
                    option.value = filter.id;
                    option.textContent = filter.filter_name;
                    select.appendChild(option);
                    select.value = filter.id;

                    this.filterName = '';
                    this.filterCode = '';
                    this.filterType = 'oil';
                    this.filterLife = '';
                    this.filterModal = false;
                })
                .catch(() => {
                    this.modalErrors = { filter_name: 'تعذّر الاتصال بالخادم. حاول مرة أخرى.' };
                })
                .finally(() => {
                    this.saving = false;
                });
        },
    };
}
