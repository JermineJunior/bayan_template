<?php

namespace App\Http\Controllers\BasicData;

use App\Exceptions\InvalidOdometerReadingException;
use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Management;
use App\Models\Vehicle;
use App\Services\OdometerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VehicleController extends Controller
{
    /**
     * Display a paginated listing of the vehicles.
     */
    public function index(Request $request): View
    {
        $vehicles = Vehicle::query()
            ->with(['management'])
            // التحقق من وجود معايير البحث في الطلب وتطبيقها على الاستعلام
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->string('search'));

                $query->where(function ($query) use ($search) {
                    $query->where('internal_number', 'like', "%{$search}%")
                        ->orWhere('plate_number', 'like', "%{$search}%")
                        ->orWhere('type', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%")
                        ->orWhere('model', 'like', "%{$search}%")
                        ->orWhere('chassis_number', 'like', "%{$search}%")
                        ->orWhere('engine_number', 'like', "%{$search}%")
                        ->orWhere('color', 'like', "%{$search}%");
                });
            })
            // فرز حسب الإدارة إذا تم تمرير management_id في الطلب
            ->when($request->filled('management_id'), function ($query) use ($request) {
                $query->where('management_id', (int) $request->query('management_id'));
            })
            // فرز حسب الحالة إذا تم تمرير status في الطلب
            ->when(
                in_array((string) $request->string('status'), ['active', 'maintenance', 'stopped', 'sold', 'out_of_service'], true),
                function ($query) use ($request) {
                    $query->where('status', (string) $request->string('status'));
                }
            )
            ->orderBy('internal_number')
            ->paginate(10)
            ->withQueryString();

        return view('basic-data.vehicles.index', [
            'vehicles' => $vehicles,
            'managements' => Management::orderBy('name')->get(),
        ]);
    }

    /**
     * Show the form for creating a new vehicle.
     */
    public function create(): View
    {
        return view('basic-data.vehicles.create', [
            'vehicle' => null,
            'managements' => Management::orderBy('name')->get(),
        ]);
    }

    /**
     * Store a newly created vehicle.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->storePublicly('vehicles', 'public');
        }

        $initialOdometer = $validated['initial_odometer'] ?? null;
        unset($validated['image'], $validated['initial_odometer']);

        $vehicle = Vehicle::create($validated);

        try {
            // أول قراءة عداد تُسجَّل كسجل تاريخي بدل الكتابة المباشرة على العمود.
            app(OdometerService::class)->record(
                $vehicle,
                (float) $initialOdometer,
                $request->user(),
                false,
                'القراءة الأولية عند إنشاء المركبة' // هذا السجل يُنشأ تلقائيًا عند إنشاء المركبة، ما يحتاج إدخال ملاحظة من المستخدم
            );
        } catch (InvalidOdometerReadingException $e) {
            $vehicle->delete();

            return back()
                ->withInput()
                ->withErrors(['initial_odometer' => $e->getMessage()]);
        }

        flash()->success('تم إنشاء المركبة.');

        return redirect()
            ->route('vehicles.index');
    }

    /**
     * Show the given vehicle.
     */
    public function show(Vehicle $vehicle): View
    {
        $vehicle->load([
            'management',
            'currentAssignment.driver',
            'odometerLogs.recordedBy',
            'fuelLogs.driver',
            'oilChanges.oil',
            'filterChanges.filter',
            'insurancePolicies',
            'currentInsurancePolicy',
        ]);

        return view('basic-data.vehicles.show', [
            'vehicle' => $vehicle,
            'drivers' => Driver::query()
                ->with('currentAssignment.vehicle')
                ->where('status', 'active') // لا يمكن اسناد مركبة لسائق متوقف أو موقوف
                ->orderBy('full_name')
                ->get(),
        ]);
    }

    /**
     * Show the form for editing the given vehicle.
     */
    public function edit(Vehicle $vehicle): View
    {
        return view('basic-data.vehicles.edit', [
            'vehicle' => $vehicle,
            'managements' => Management::orderBy('name')->get(),
        ]);
    }

    /**
     * Update the given vehicle.
     */
    public function update(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $validated = $request->validate($this->rules($vehicle));

        if ($request->hasFile('image')) {
            if ($vehicle->image_path) {
                Storage::disk('public')->delete($vehicle->image_path);
            }

            $validated['image_path'] = $request->file('image')->storePublicly('vehicles', 'public');
        }

        unset($validated['image']);

        $vehicle->update($validated);

        flash()->success('تم تحديث المركبة.');

        return redirect()
            ->route('vehicles.index');
    }

    /**
     * Remove the given vehicle.
     */
    public function destroy(Vehicle $vehicle): RedirectResponse
    {
        if ($vehicle->driverAssignments()->exists()) {
            flash()->error('لا يمكن حذف هذه المركبة لوجود سائقين مخصصين لها.');

            return redirect()
                ->route('vehicles.index');
        }

        if ($vehicle->image_path) {
            Storage::disk('public')->delete($vehicle->image_path);
        }

        $vehicle->delete();

        flash()->success('تم حذف المركبة.');

        return redirect()
            ->route('vehicles.index');
    }

    /**
     * Shared validation rules for creating and updating a vehicle.
     *
     * @return array<string, array<int, mixed>>
     */
    private function rules(?Vehicle $vehicle = null): array
    {
        return [
            'internal_number' => ['required', 'string', 'max:50', Rule::unique('vehicles')->ignore($vehicle?->id)],
            'plate_number' => ['required', 'string', 'max:50', Rule::unique('vehicles')->ignore($vehicle?->id)],
            'type' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:100'],
            'manufacture_year' => ['nullable', 'integer', 'min:1950', 'max:'.(date('Y') + 1)],
            'color' => ['nullable', 'string', 'max:100'],
            'chassis_number' => ['nullable', 'string', 'max:100'],
            'engine_number' => ['nullable', 'string', 'max:100'],
            'fuel_type' => ['nullable', Rule::in(['gasoline', 'diesel'])],
            'engine_capacity' => ['nullable', 'string', 'max:100'],
            'management_id' => ['nullable', 'integer', Rule::exists('management', 'id')],
            'status' => ['required', Rule::in(['active', 'maintenance', 'stopped', 'sold', 'out_of_service'])],
            'initial_odometer' => ['nullable', 'numeric', 'min:0'],
            'operating_hours' => ['nullable', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ];
    }
}
