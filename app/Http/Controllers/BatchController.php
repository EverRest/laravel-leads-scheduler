<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\BatchRequest;
use App\Services\Schedule\ScheduleService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Response;

final class BatchController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param ScheduleService $scheduleService
     */
    public function __construct(private readonly ScheduleService $scheduleService)
    {
    }

    /**
     * @param BatchRequest $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function __invoke(BatchRequest $request): JsonResponse
    {
        $importedLeads = $request->leads;
        $partnerId = $request->partner_id;
        $fromDate = Carbon::parse($request->fromDate);
        $toDate = Carbon::parse($request->toDate);
        $leads = $this->scheduleService->scheduleLeads($importedLeads, $partnerId, $fromDate, $toDate);

        return Response::data($leads);
    }
}
