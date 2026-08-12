// Quick-add modal for the oil-change form. Lets the user create a catalog oil
// on the spot (POST oils.store via fetch) without leaving the oil-change form
// and losing what they already typed. On success the new oil is appended to
// the main #oil_id <select> and selected automatically.
export default function (storeUrl) {
    return {
        oilModal: false,
        saving: false,

        oilName: '',
        oilCode: '',
        oilType: 'engine',
        oilLife: '',

        modalErrors: {},

        openOilModal() {
            this.oilModal = true;
            this.modalErrors = {};
        },

        closeOilModal() {
            if (this.saving) {
                return;
            }

            this.oilModal = false;
        },

        quickAddOil() {
            this.saving = true;
            this.modalErrors = {};

            const formData = new FormData();
            formData.append('oil_name', this.oilName);
            formData.append('oil_code', this.oilCode);
            formData.append('oil_type', this.oilType);
            formData.append('oil_life', this.oilLife);

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
                            this.modalErrors = { oil_name: 'حدث خطأ غير متوقع أثناء إضافة الزيت.' };
                        }

                        return;
                    }

                    const oil = await response.json();
                    const select = document.getElementById('oil_id');
                    const option = document.createElement('option');
                    option.value = oil.id;
                    option.textContent = oil.oil_name;
                    select.appendChild(option);
                    select.value = oil.id;

                    this.oilName = '';
                    this.oilCode = '';
                    this.oilType = 'engine';
                    this.oilLife = '';
                    this.oilModal = false;
                })
                .catch(() => {
                    this.modalErrors = { oil_name: 'تعذّر الاتصال بالخادم. حاول مرة أخرى.' };
                })
                .finally(() => {
                    this.saving = false;
                });
        },
    };
}
