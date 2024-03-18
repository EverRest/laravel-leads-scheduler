<?php
declare(strict_types=1);

namespace App\Http\Requests;

use App\Rules\LeadPartnerRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @property array<string, mixed> $leads
 * @property string|numeric $partner_id
 * @property string $fromDate
 * @property string $toDate
 */
class BatchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'leads' => ['required', 'array', new LeadPartnerRule($this->partner_id, $this->fromDate, $this->toDate)],
            "leads.*.external_id" => "required|numeric",
            'leads.*.first_name' => 'required|string|min:2|max:50',
            'leads.*.last_name' => 'required|string|min:2|max:100',
            'leads.*.email' => 'required|email|max:100',
            'leads.*.area_code' => 'required|string|min:1|max:10',
            'leads.*.country_code' => 'required|string|max:10',
            'leads.*.country' => 'required|string|min:1|max:100',
            'leads.*.phone' => 'nullable|string|min:2|max:50',
            'leads.*.offerName' => 'nullable|string|min:2|max:50',
            'leads.*.offerUrl' => 'nullable|string|min:2|max:255',
            'leads.*.traffic_source' => 'nullable|string|min:2|max:50',
            'partner_id' => 'required|numeric|exists:partners,external_id',
            'fromDate' => 'required|date|after:now',
            'toDate' => 'required|date|after:now',
        ];
    }
}
