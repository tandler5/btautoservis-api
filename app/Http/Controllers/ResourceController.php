<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ResourceService;

class ResourceController extends Controller
{
    protected $resourceService;

    public function __construct(ResourceService $resourceService)
    {
        $this->resourceService = $resourceService;
    }

    public function get_resources_grouped_by_day(Request $request)
    {
        $resources = $this->resourceService->get_resources_grouped_by_day($request->booking_request, $request->date_from, $request->date_to, $request->settings);

        // Vrácení dat jako JSON
        return response()->json($resources);
    }

    // Podobně pro ostatní metody
}
