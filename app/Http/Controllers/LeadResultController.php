<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\LeadRequest;
use App\Services\LeadResultService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;

final class LeadResultController extends Controller
{
    /**
     * @param LeadResultService $leadResultService
     */
    public function __construct(
        private readonly LeadResultService $leadResultService,
    )
    {
    }

    /**
     * @param LeadRequest $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function __invoke(LeadRequest $request): JsonResponse
    {
        $attributes = $request->validated();
        $leadResult = $this->leadResultService->store($attributes);

        return Response::data($leadResult);
    }
}
