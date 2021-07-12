<?php

namespace tanyudii\YinNumber\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use tanyudii\YinCore\Controllers\YinRestController;
use tanyudii\YinCore\Facades\YinResourceService;
use tanyudii\YinCore\Rules\ValidType;
use tanyudii\YinNumber\Models\Number as NumberSetting;
use tanyudii\YinNumber\Resources\NumberResource;
use tanyudii\YinNumber\Type;

class NumberController
{
    use YinRestController {
        YinRestController::__construct as private __restConstruct;
    }

    public function __construct(NumberSetting $model)
    {
        $this->__restConstruct(
            $model,
            NumberResource::class,
            NumberResource::class
        );
    }

    /**
     * @param Request $request
     * @return JsonResource
     * @throws Exception
     */
    public function store(Request $request)
    {
        $request->validate([
            "name" => ["required", "string"],
            "model" => ["required", "string"],
            "reset_type" => ["required", new ValidType(Type::RESET_TYPE_OPTIONS)]
        ]);

        try {
            DB::beginTransaction();

            $data = $this->repository->create($request->only($this->fillAble));

            DB::commit();

            return YinResourceService::jsonResource(NumberResource::class, $data);
        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }
}
