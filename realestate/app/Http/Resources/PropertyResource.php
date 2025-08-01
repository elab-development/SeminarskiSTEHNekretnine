<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'price' => $this->price,
            'address' => $this->address,
            'city' => $this->city,
            'status' => $this->status,
            'type' => new PropertyTypeResource($this->propertyType),
            'listed_by' => $this->listedBy->name,
        ];
    }
}
