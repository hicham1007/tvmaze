<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\DTOs\CastMember;
use App\DTOs\ShowDetail;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read ShowDetail $resource
 */
class ShowDetailResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $show = $this->resource;

        return [
            'id' => $show->id,
            'name' => $show->name,
            'image' => $show->image,
            'genres' => $show->genres,
            'rating' => $show->rating,
            'premiered' => $show->premiered,
            'ended' => $show->ended,
            'summary' => $show->summary,
            'type' => $show->type,
            'language' => $show->language,
            'status' => $show->status,
            'runtime' => $show->runtime,
            'network' => $show->network,
            'country' => $show->country,
            'schedule' => [
                'time' => $show->scheduleTime,
                'days' => $show->scheduleDays,
            ],
            'officialSite' => $show->officialSite,
            'cast' => array_map(fn (CastMember $member): array => [
                'id' => $member->personId,
                'name' => $member->name,
                'character' => $member->character,
                'image' => $member->image,
            ], $show->cast),
        ];
    }
}
