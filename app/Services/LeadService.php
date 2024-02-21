<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\LeadRepository;
use App\Repositories\LeadResultRepository;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;

class LeadService
{
    /**
     * @var LeadRepository $leadRepository
     */
    protected LeadRepository $leadRepository;

    /**
     * @var LeadResultRepository $leadResultRepository
     */
    protected LeadResultRepository $leadResultRepository;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->leadRepository = App::make(LeadRepository::class);
        $this->leadResultRepository = App::make(LeadResultRepository::class);
    }

}
