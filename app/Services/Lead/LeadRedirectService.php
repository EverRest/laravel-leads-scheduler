<?php
declare(strict_types=1);

namespace App\Services\Lead;

use App\Helpers\Base64ToUploadedFile;
use App\Models\Lead;
use App\Models\LeadRedirect;
use App\Repositories\LeadRedirectRepository;
use App\Services\Partner\PartnerServiceFactory;
use Exception;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

final class LeadRedirectService
{
    /**
     * @param LeadRedirectRepository $leadRedirectRepository
     */
    public function __construct(
        private readonly LeadRedirectRepository $leadRedirectRepository,
    )
    {
    }

    /**
     * @param Lead $lead
     *
     * @return Model
     * @throws Exception
     */
    public function getRedirectLink(Lead $lead): Model
    {
        $redirectLink = $this->generateRedirectLink($lead);
        return $this->storeRedirectLink($lead, $redirectLink);
    }

    /**
     * @param Lead $lead
     * @param string|null $redirectLink
     *
     * @return Model
     */
    public function storeRedirectLink(Lead $lead, ?string $redirectLink): Model
    {
        return $this->leadRedirectRepository->store([
            'lead_id' => $lead->id,
            'link' => $redirectLink,
        ]);
    }

    /**
     * @param Lead $lead
     *
     * @return Model
     * @throws FileNotFoundException
     */
    public function generateScreenshotByLead(Lead $lead): Model
    {
        $link = Arr::get($lead->leadResult->toArray(), $lead->redirectLinkKey);
        /** @var LeadRedirect $leadRedirect */
        $leadRedirect = $this->leadRedirectRepository->patch($lead->leadRedirect, 'link', $link);
        $response = $this->getBrowserResponse($leadRedirect);
        $screenShot = Arr::get($response?->json() ?? [], 'screenshot');
        $uploadedFile = $screenShot ? (new Base64ToUploadedFile($screenShot))->file() : null;
        if ($uploadedFile && $uploadedFile->isValid()) {
            return $this->storeScreenshot($leadRedirect, $uploadedFile);
        }

        return $leadRedirect;
    }

    /**
     * @param Lead $lead
     *
     * @return Model
     * @throws FileNotFoundException
     */
    public function generateScreenshotByLeadRedirect(Lead $lead): Model
    {
        $link = Arr::get($lead->leadResult->toArray(), $lead->redirectLinkKey);
        /** @var LeadRedirect $leadRedirect */
        $leadRedirect = $this->leadRedirectRepository->patch($lead->leadRedirect, 'link', $link);
        $response = $this->getBrowserResponse($leadRedirect);
        $screenShot = Arr::get($response?->json() ?? [], 'screenshot');
        $uploadedFile = $screenShot ? (new Base64ToUploadedFile($screenShot))->file() : null;
        if ($uploadedFile && $uploadedFile->isValid()) {
            return $this->storeScreenshot($leadRedirect, $uploadedFile);
        }

        return $leadRedirect;
    }

    /**
     * @param Lead $lead
     *
     * @return string|null
     */
    private function generateRedirectLink(Lead $lead): ?string
    {
        try {
            $service = PartnerServiceFactory::createService($lead->partner->external_id);
            return $service->send($lead);
        } catch (Exception $e) {
            Log::error(get_class($this) . ": Redirect link was not generated for lead $lead->id. Reason: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * @param LeadRedirect $leadRedirect
     *
     * @return bool
     */
    private function validateLeadRedirect(LeadRedirect $leadRedirect): bool
    {
        $lead = $leadRedirect->lead;
        if (!$leadRedirect->link) {
            Log::error("Can't find redirect link for lead: {$lead->id}");
            return false;
        }
        if (!$lead->leadProxy) {
            Log::error("Can't find proxy for lead: $lead->id");
            return false;
        }
        return true;
    }

    /**
     * @param LeadRedirect $leadRedirect
     *
     * @return Response|null
     */
    private function getBrowserResponse(LeadRedirect $leadRedirect): ?Response
    {
        try {
            $leadResult = $leadRedirect->lead->leadResult;
            $link = Arr::get($leadResult->toArray(), $leadRedirect->lead->redirectLinkKey);
            $this->leadRedirectRepository->patch($leadRedirect, 'link', $link);
            $proxy = [
                'host' => $leadRedirect->lead->leadProxy->host,
                'port' => $leadRedirect->lead->leadProxy->port,
                'protocol' => $leadRedirect->lead->leadProxy->protocol,
                'username' => $leadRedirect->lead->leadProxy->username,
                'password' => $leadRedirect->lead->leadProxy->password,
            ];
            Log::info("Link: $link");
            Log::info("Proxy: " . json_encode($proxy));
            return Http::post("http://browser:4000/browser", [
                'url' => $link,
                'proxy' => $proxy
            ]);
        } catch (Exception $e) {
            Log::error(get_class($this) . ": Screenshot was not generated for lead {$leadRedirect->lead->id}. Reason: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * @param LeadRedirect $leadRedirect
     * @param UploadedFile $uploadedFile
     *
     * @return Model
     * @throws FileNotFoundException
     */
    private function storeScreenshot(LeadRedirect $leadRedirect, UploadedFile $uploadedFile): Model
    {
        $lead = $leadRedirect->lead;
        $fileName = "screenshots/$lead->id.png";
        Storage::disk('public')->exists($fileName) && Storage::disk('public')->delete($fileName);
        Storage::disk('public')->put($fileName, $uploadedFile->get());

        return $this->leadRedirectRepository->update(
            $leadRedirect,
            ['file' => $fileName,],
        );
    }
}
