<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiServiceProviderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'api_service_provider',
            'id' => $this->id,
            'attributes' => [
                'serviceKey' => $this->service_key,
                'displayName' => $this->display_name,
                'description' => $this->description,
                'enabled' => (bool) $this->enabled,
                'meta' => $this->meta,
                'accessEnabled' => (bool) ($this->pivot->enabled ?? false),
            ],
        ];
    }
}
