<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Helpers\Base64ToUploadedFile;
use App\Repositories\LeadRepository;
use App\Repositories\LeadResultRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class LeadController extends Controller
{
    /**
     * @param LeadResultRepository $leadResultRepository
     * @param LeadRepository $leadRepository
     */
    public function __construct(
        private readonly LeadResultRepository $leadResultRepository,
        private readonly LeadRepository $leadRepository
    )
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
        $leadId = intval(Arr::get($attributes, 'lead_id'));
        $lead = $this->leadRepository->findOrFail($leadId);
        $file = (new Base64ToUploadedFile(Arr::get($attributes, 'file')))->file();
        Storage::disk('local')->put($lead->import, $file);
        $leadResult = $this->leadResultRepository->store(
            [
                ...Arr::except($attributes, 'file'),
                'screen_shot' => $file->getFilename()
            ]
        );

        return Response::data($leadResult);
    }
}
