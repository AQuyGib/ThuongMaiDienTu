<?php

namespace App\Http\Resources;

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
        return [
            'user_id' => $this->user_id,
            'role_id' => $this->role_id,
            'role_name' => $this->role ? $this->role->role_name : null,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'member_tier' => $this->member_tier,
            'status' => $this->status,
            'is_2fa_enabled' => (bool) $this->is_2fa_enabled,
            'created_at' => $this->created_at ? $this->created_at->toIso8601String() : null,
        ];
    }
}
