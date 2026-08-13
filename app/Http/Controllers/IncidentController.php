<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Incident;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class IncidentController extends Controller
{
    /**
     * Display a paginated listing of incidents across all vehicles.
     */
    public function index(Request $request): View
    {
        $incidents = Incident::query()
            ->with(["vehicle", "driver"])
            ->when($request->filled("vehicle_id"), function ($query) use (
                $request,
            ) {
                $query->where(
                    "vehicle_id",
                    (int) $request->query("vehicle_id"),
                );
            })
            ->when($request->filled("claim_status"), function ($query) use (
                $request,
            ) {
                $query->where(
                    "claim_status",
                    (string) $request->query("claim_status"),
                );
            })
            ->latest("incident_date")
            ->paginate(10)
            ->withQueryString();

        return view("incidents.index", [
            "incidents" => $incidents,
            "vehicles" => Vehicle::orderBy("internal_number")->get(),
        ]);
    }

    /**
     * Show the form for reporting a new incident for the given vehicle.
     */
    public function create(Vehicle $vehicle): View
    {
        return view("incidents.create", [
            "vehicle" => $vehicle,
            "drivers" => Driver::orderBy("full_name")->get(),
            "currentPolicy" => $vehicle->currentInsurancePolicy,
        ]);
    }

    /**
     * Report a new incident for the given vehicle.
     */
    public function store(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $request->merge([
            "repair_cost" => $request->filled("repair_cost")
                ? str_replace(",", "", $request->input("repair_cost"))
                : null,
        ]);
        $validated = $request->validate([
            "report_number" => [
                "required",
                "string",
                "max:50",
                Rule::unique("incidents", "report_number"),
            ],
            "driver_id" => [
                "nullable",
                "integer",
                Rule::exists("drivers", "id"),
            ],
            "incident_date" => ["required", "date", "before_or_equal:today"],
            "location" => ["nullable", "string", "max:255"],
            "description" => ["nullable", "string"],
            "repair_cost" => ["nullable", "numeric", "min:0"],
            "insurance_policy_id" => [
                "nullable",
                "integer",
                Rule::exists("insurance_policies", "id"),
            ],
            "claim_status" => [
                "nullable",
                Rule::in(["pending", "approved", "rejected", "paid"]),
            ],
            "photos" => ["nullable", "array", "max:10"],
            "photos.*" => ["image", "max:5120"],
        ]);

        $incident = Incident::create([
            "report_number" => $validated["report_number"],
            "vehicle_id" => $vehicle->id,
            "driver_id" => $validated["driver_id"] ?? null,
            "incident_date" => $validated["incident_date"],
            "location" => $validated["location"] ?? null,
            "description" => $validated["description"] ?? null,
            "repair_cost" => $validated["repair_cost"] ?? null,
            "insurance_policy_id" => $request->has("link_insurance_policy")
                ? $vehicle->currentInsurancePolicy?->id ?? null
                : $validated["insurance_policy_id"] ?? null,
            "claim_status" => $validated["claim_status"] ?? null,
            "recorded_by" => $request->user()->id,
        ]);

        if ($request->hasFile("photos")) {
            foreach ($request->file("photos") as $photo) {
                $path = $photo->store("incidents", "public");

                $incident->photos()->create([
                    "file_path" => $path,
                ]);
            }
        }

        flash()->success("تم تسجيل الحادث بنجاح.");

        return redirect()->route("vehicles.show", $vehicle);
    }

    /**
     * Show the given incident.
     */
    public function show(Incident $incident): View
    {
        $incident->load([
            "vehicle",
            "driver",
            "insurancePolicy",
            "photos",
            "recordedBy",
        ]);

        return view("incidents.show", [
            "incident" => $incident,
        ]);
    }

    /**
     * Update the claim status for the given incident.
     */
    public function updateClaimStatus(
        Request $request,
        Incident $incident,
    ): RedirectResponse {
        $validated = $request->validate([
            "claim_status" => [
                "required",
                Rule::In(["pending", "approved", "rejected", "paid"]),
            ],
        ]);

        $incident->update([
            "claim_status" => $validated["claim_status"],
        ]);

        flash()->success("تم تحديث حالة المطالبة بنجاح.");

        return back();
    }

    /**
     * Remove the given incident and its photos.
     */
    public function destroy(Incident $incident): RedirectResponse
    {
        foreach ($incident->photos as $photo) {
            Storage::disk("public")->delete($photo->file_path);
        }

        $incident->photos()->delete();
        $incident->delete();

        flash()->success("تم حذف الحادث.");

        return back();
    }
}
