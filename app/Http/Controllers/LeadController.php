<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Repositories\LeadRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

final class LeadController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param LeadRepository $leadRepository
     */
    public function __construct(private readonly LeadRepository $leadRepository)
    {
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function __invoke(Request $request): JsonResponse
    {
        $leads = $this->leadRepository->list($request->all());

        return Response::data($leads);
    }
}