<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\DTOs\Show;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read Show $resource
 */
class ShowResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'image' => $this->resource->image,
            'genres' => $this->resource->genres,
            'rating' => $this->resource->rating,
            'premiered' => $this->resource->premiered,
        ];
    }
}
