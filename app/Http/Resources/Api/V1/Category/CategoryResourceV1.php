<?php

namespace App\Http\Resources\Api\V1\Category;

use App\Http\Resources\Api\V1\Product\ProductResourceV1;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResourceV1 extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'categories',
            'id' => $this->id,
            'attributes' => [
                'name' => $this->name,
                'description' => $this->when(
                    $request->routeIs('categories.show'),
                    $this->description,
                ),
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at
            ],
            'includes' => ProductResourceV1::collection($this->whenLoaded('products')),
            'links' => [
                ['self' => route('categories.show', ['category' => $this->id])]
            ]
        ];
    }
}
