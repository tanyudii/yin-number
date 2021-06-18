<?php

namespace tanyudii\YinNumber\Controllers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use tanyudii\YinNumber\Resources\NumberResource;
use tanyudii\YinNumber\Services\NumberService;

class NumberController
{
    protected $numberService;

    public function __construct(NumberService $numberService)
    {
        $this->numberService = $numberService;
    }

    public function index(Request $request)
    {
        $data = $this->numberService->findAll($request->all());

        return NumberResource::collection($data);
    }

    public function show(Request $request, $id)
    {
        $data = $this->numberService->findOne(
            array_merge(
                [
                    "id" => $id,
                ],
                $request->all()
            )
        );

        if (empty($data)) {
            throw new ModelNotFoundException();
        }

        return new NumberResource($data);
    }

    public function store(Request $request)
    {
        $data = $this->numberService->create($request->all());

        return $this->show($request, $data->id);
    }

    public function destroy(Request $request, $id)
    {
        $totalDeleted = $this->numberService->delete(["id" => $id]);

        return response()->json([
            "success" => true,
            "data" => $totalDeleted,
        ]);
    }
}
