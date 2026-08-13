document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('maintenance-form');

    if (!form) {
        return;
    }

    const vehicleSelect = document.getElementById('vehicle_id');
    const odometerInput = document.getElementById('odometer_reading');
    const laborInput = document.getElementById('labor_cost');
    const spareInput = document.getElementById('spare_cost');
    const totalInput = document.getElementById('total_cost');

    vehicleSelect?.addEventListener('change',async function () {

        const vehicleId = this.value;

        if (!vehicleId) {
            odometerInput.value = '';
            return;
        }

        try {
            odometerInput.value = 'جاري التحميل...';
            const response = await fetch(
                `/maintenance/vehicle/${vehicleId}/odometer`,
                {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                }
            );

            if (!response.ok) {
                throw new Error('حدث خطأ أثناء جلب قراءة العداد');
            }

            const data = await response.json();

            odometerInput.value = data.odometer ?? '';

        } catch (error) {

            console.error(error);

            odometerInput.value = '';

            alert('تعذر جلب قراءة العداد للمركبة');

        }
    });

    function calculateTotal() {

        const labor = parseFloat(laborInput?.value) || 0;
        const spare = parseFloat(spareInput?.value) || 0;

        const total = labor + spare;

        if (totalInput) {
            totalInput.value = total.toFixed(2);
        }
    }

    laborInput?.addEventListener('input', calculateTotal);
    spareInput?.addEventListener('input', calculateTotal);

    calculateTotal();

});