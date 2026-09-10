<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Monitoring\Queries\DashboardSummaryQuery;
use App\Domain\Monitoring\Queries\TrafficQuery;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function summary(DashboardSummaryQuery $query)
    {
        return response()->json(['data' => $query->execute()]);
    }

    public function traffic(Request $request, TrafficQuery $query)
    {
        return response()->json($query->execute(
            $request->integer('device_id') ?: null,
            $request->string('from')->trim()->value() ?: null,
            $request->string('to')->trim()->value() ?: null,
        ));
    }
}
