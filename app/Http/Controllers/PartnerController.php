<?php

namespace App\Http\Controllers;

use App\Repositories\PartnerRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PartnerController extends Controller
{
    /**
     * @param PartnerRepository $partnerRepository
     */
    public function __construct(private readonly PartnerRepository $partnerRepository)
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
        $attributes = $request->all();
        $partners = $this->partnerRepository->list($attributes);

        return Response::data($partners);
    }
}
