<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Notice;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Notice */
class NoticeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'notice_type' => $this->notice_type,
            'audience_type' => $this->audience_type,
            'body' => $this->body,
            'status' => $this->status,
            'published_at' => $this->published_at?->toIso8601String(),
        ];
    }
}
