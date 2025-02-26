<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'type' => 'user',
            'id' => $this->id,
            'attributes' => [
                'userName' => $this->user_name,
                'email' => $this->email,
                'firstName' => $this->first_name,
                'lastName' => $this->last_name,
                'createdBy' => $this->created_by,
                $this->mergeWhen($request->routeIs('users.*'), [
                    'emailVerifiedAt' => $this->email_verified_at,
                    'hasApiAccess' => $this->hasApiAccess,
                    'createdAt' => $this->created_at,
                    'updatedAt' => $this->updated_at,
                ])
            ]
        ];
    }
}
