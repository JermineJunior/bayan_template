<?php

namespace App\Http\Controllers;

use App\Models\Filter;
use App\Models\Oil;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    /**
     * Display the combined oils + filters catalog on a single page.
     *
     * Both catalogs were merged into one listing, so this page is the only
     * index — the old oils.index / filters.index routes redirect here. A user
     * may open it with either catalog permission; each table is rendered only
     * when its own permission is held.
     */
    public function index(Request $request): View
    {
        abort_unless(
            $request->user()->can('oils.view') || $request->user()->can('filters.view'),
            403,
        );

        return view('catalog.index', [
            'oils' => Oil::withCount('changes')
                ->orderBy('oil_name')
                ->paginate(10),
            'filters' => Filter::withCount('changes')
                ->orderBy('filter_name')
                ->paginate(10),
        ]);
    }
}
