<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        return [
            'type'=> 'application',
            'id' => $this->id,
            'attributes' => [
                'name' => $this->name,
                'status' => $this->status, 
                'apikey' => $this->apikey,
            ],
        ];
    }
}