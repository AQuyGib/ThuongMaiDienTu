<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    /**
     * Chuyển đổi tài nguyên thành dạng mảng JSON chuẩn hóa.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'user_id' => $this->user_id,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone' => $this->phone_number, // Ánh xạ chuẩn hóa từ phone_number trong DB sang phone ở API
            'role_id' => $this->role_id,
            'role' => $this->relationLoaded('role') && $this->role ? [
                'role_id' => $this->role->role_id,
                'name' => $this->role->name,
                'description' => $this->role->description,
            ] : null,
            'status' => $this->status,
            'version' => $this->version ?? 1,
            'created_at' => $this->created_at ? $this->created_at->format('d/m/Y H:i') : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toDateTimeString() : null,
        ];
    }
}
