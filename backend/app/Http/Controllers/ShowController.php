<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\SearchShowsRequest;
use App\Http\Resources\ShowDetailResource;
use App\Http\Resources\ShowResource;
use App\Services\ShowService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ShowController extends Controller
{
    public function __construct(private readonly ShowService $shows) {}

    public function index(SearchShowsRequest $request): AnonymousResourceCollection
    {
        return ShowResource::collection($this->shows->search($request->string('q')->value()));
    }

    public function show(int $id): ShowDetailResource
    {
        return new ShowDetailResource($this->shows->find($id));
    }
}
