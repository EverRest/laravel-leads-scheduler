<?php
declare(strict_types=1);

namespace App\Services\Lead;

use App\Helpers\Base64ToUploadedFile;
use App\Repositories\LeadRepository;
use App\Repositories\LeadResultRepository;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

final class LeadResultService
{
    public function __construct(
        private readonly LeadResultRepository $leadResultRepository,
        private readonly LeadRepository       $leadRepository,
    )
    {

    }

    /**
     * @param array $attributes
     *
     * @return Model
     * @throws Exception
     */
    public function store(array $attributes): Model
    {
        $leadId = intval(Arr::get($attributes, 'lead_id'));
        $lead = $this->leadRepository->findOrFail($leadId);
        $file = (new Base64ToUploadedFile(Arr::get($attributes, 'file')))->file();
        Storage::disk('local')->put($lead->import, $file);

        return $this->leadResultRepository->store(
            [
                ...Arr::except($attributes, 'file'),
                'screen_shot' => $file->getFilename()
            ]
        );
    }
}
