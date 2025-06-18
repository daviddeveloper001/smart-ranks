<?php
namespace App\DataTransferObjects;

class ProductDTO
{
    public function __construct(
        public int $category_id,
        public string $name,
        public ?string $description,
        public float $price,
        public int $stock
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            category_id: $data['category_id'],
            name: $data['name'],
            description: $data['description'] ?? null,
            price: (float) $data['price'],
            stock: (int) $data['stock']
        );
    }

    public function toArray(): array
    {
        return [
            'category_id' => $this->category_id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'stock' => $this->stock,
        ];
    }
}
