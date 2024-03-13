<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Repositories\PartnerRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

final class PartnerController extends Controller
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
        $partners = $this->partnerRepository->getList($attributes);

        return Response::data($partners);
    }
}
