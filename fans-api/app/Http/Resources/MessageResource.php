<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'sender_id' => $this->resource->sender_id,
            'receiver_id' => $this->resource->receiver_id,
            'content' => $this->resource->content,
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
            'read_at' => $this->resource->read_at ? $this->resource->read_at->toIso8601String() : null,
            'media' => MediaResource::collection($this->resource->media),
            'media_previews' => $this->when(!$this->resource->user_has_permission, function () {
                return MediaPreviewResource::collection(
                    $this->resource->media->flatMap(function ($media) {
                        return $media->previews;
                    })
                );
            }),
            'sender' => new UserResource($this->whenLoaded('sender')),
            'receiver' => new UserResource($this->whenLoaded('receiver')),
            'user_has_permission' => $this->resource->user_has_permission ?? false,
            'required_permissions' => $this->when(
                !($this->resource->user_has_permission ?? false), 
                $this->resource->required_permissions ?? []
            ),
        ];
    }
}

