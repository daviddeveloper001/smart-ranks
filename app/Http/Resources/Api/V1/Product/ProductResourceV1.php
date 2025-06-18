<?php

namespace App\Http\Resources\Api\V1\Product;

use App\Http\Resources\Api\V1\Category\CategoryResourceV1;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResourceV1 extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'products',
            'id' => $this->id,
            'attributes' => [
                'name' => $this->name,
                'description' => $this->when(
                    $request->routeIs('products.show'),
                    $this->description,
                ),
                'price' => $this->price,
                'stock' => $this->stock,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at
            ],
            'relationships' => [
                'category' => [
                    'data' => [
                        'type' => 'category',
                        'id' => $this->category_id
                    ],
                    'links' => [
                        'self' => route('categories.show', ['category' => $this->category_id])
                    ]
                ]
            ],
            'includes' => new CategoryResourceV1($this->whenLoaded('category')),
            'links' => [
                ['self' => route('products.show', ['product' => $this->id])]
            ]
        ];
    }
}
