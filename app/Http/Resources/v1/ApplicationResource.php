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
            'type' => 'application',
            'id' => $this->id,
            'attributes' => [
                'name' => $this->name,
                'description' => $this->description,
                'createdBy' => $this->created_by,
                'status' => $this->status,
                'allowedAbilities' => $this->allowed_abilities,
                'assignedServices' => $this->assigned_services ?? [],
                'apiKeyRotatedAt' => $this->api_key_rotated_at,
                'apiKeyRevokedAt' => $this->api_key_revoked_at,
                'apiKeyExpiresAt' => $this->api_key_expires_at,
                'apiKeyLastUsedAt' => $this->api_key_last_used_at,
                'apiKeyLastUsedIp' => $this->api_key_last_used_ip,
            ],
        ];
    }
}