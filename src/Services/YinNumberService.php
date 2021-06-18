<?php

namespace tanyudii\YinNumber\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use tanyudii\YinNumber\Contracts\NumberModel;
use tanyudii\YinNumber\Contracts\WithNumberSetting;
use tanyudii\YinNumber\Exceptions\YinNumberException;

class YinNumberService
{
    /**
     * @param string $modelNamespace
     * @param null $date
     * @param null $subjectId
     * @return string
     * @throws YinNumberException
     */
    public function generateNumber(
        string $modelNamespace,
        $date = null,
        $subjectId = null
    ) {
        if (!app(config("yin-number.models.number")) instanceof NumberModel) {
            throw new YinNumberException(
                "The number model is not instance of NumberModel"
            );
        } elseif (!app($modelNamespace) instanceof WithNumberSetting) {
            throw new YinNumberException(
                "The number model is not instance of WithNumberSetting"
            );
        }

        $numberSetting = config("yin-number.models.number")
            ::with("numberComponents")
            ->where("model", $modelNamespace)
            ->first();

        $model = app($modelNamespace);
        $tableName = $model->getTable();

        if (
            empty($numberSetting) ||
            $numberSetting->numberComponents->isEmpty()
        ) {
            return DB::select("show table status like '{$tableName}'")[0]
                ->Auto_increment ??
                DB::table($tableName)->count("*") + 1;
        }

        $date = is_null($date) ? Carbon::now() : Carbon::parse($date);

        $prefixDigit = 0;
        $digitBeforeCounter = 0;
        $generatedNumberArray = [];
        $queryNumber = "";

        foreach ($numberSetting->numberComponents as $component) {
            if (
                !in_array(null, $generatedNumberArray) &&
                $component->type != NumberService::COMPONENT_TYPE_COUNTER
            ) {
                $digitBeforeCounter += strlen($component->format);
            }

            switch ($component->type) {
                case NumberService::COMPONENT_TYPE_TEXT:
                    array_push($generatedNumberArray, $component->format);
                    $queryNumber .= str_replace("_", "\\_", $component->format);
                    break;
                case NumberService::COMPONENT_TYPE_YEAR:
                    $dateText = $date->format($component->format);
                    array_push($generatedNumberArray, $dateText);

                    if (is_null($numberSetting->reset_type)) {
                        $dateText = str_repeat("_", strlen($dateText));
                    }

                    $queryNumber .= $dateText;
                    break;
                case NumberService::COMPONENT_TYPE_MONTH:
                    $dateText = $date->format($component->format);
                    array_push($generatedNumberArray, $dateText);

                    if (
                        is_null($numberSetting->reset_type) ||
                        $numberSetting->reset_type ==
                            NumberService::RESET_TYPE_YEARLY
                    ) {
                        $dateText = str_repeat("_", strlen($dateText));
                    }

                    $queryNumber .= $dateText;
                    break;
                case NumberService::COMPONENT_TYPE_DAY:
                    $dateText = date($component->format, strtotime($date));
                    array_push($generatedNumberArray, $dateText);

                    if (
                        is_null($numberSetting->reset_type) ||
                        in_array($numberSetting->reset_type, [
                            NumberService::RESET_TYPE_YEARLY,
                            NumberService::RESET_TYPE_MONTHLY,
                        ])
                    ) {
                        $dateText = str_repeat("_", strlen($dateText));
                    }

                    $queryNumber .= $dateText;
                    break;
                case NumberService::COMPONENT_TYPE_COUNTER:
                    array_push($generatedNumberArray, null);
                    $queryNumber .= str_repeat("_", $component->format);
                    $prefixDigit = $component->format;
                    break;
            }
        }

        $dateColumn = $model->getDateColumn();

        $subjectNumbers = app($modelNamespace)
            ->withoutGlobalScopes()
            ->where("number", "like", $queryNumber)
            ->when(
                in_array($numberSetting->reset_type, [
                    NumberService::RESET_TYPE_YEARLY,
                    NumberService::RESET_TYPE_MONTHLY,
                ]),
                function ($query) use ($dateColumn, $date) {
                    $query->whereYear($dateColumn, $date->format("Y"));
                }
            )
            ->when(
                $numberSetting->reset_type == NumberService::RESET_TYPE_MONTHLY,
                function ($query) use ($dateColumn, $date) {
                    $query->whereMonth($dateColumn, $date->format("m"));
                }
            )
            ->when($subjectId, function ($query) use ($subjectId) {
                $query->where("id", "!=", $subjectId);
            })
            ->orderBy("number")
            ->pluck("number")
            ->toArray();

        $existingNumbers = array_map(function ($subjectNo) use (
            $generatedNumberArray,
            $prefixDigit,
            $digitBeforeCounter
        ) {
            $counterIndex = array_search(null, $generatedNumberArray);
            if ($counterIndex == 0) {
                return intval(substr($subjectNo, 0, $prefixDigit));
            } elseif ($counterIndex + 1 == count($generatedNumberArray)) {
                return intval(substr($subjectNo, $prefixDigit * -1));
            }

            return intval(
                substr($subjectNo, $digitBeforeCounter, $prefixDigit)
            );
        },
        $subjectNumbers);

        sort($existingNumbers);

        if (empty($existingNumbers)) {
            $newCounter = 1;
        } else {
            $idealNos = range(
                $existingNumbers[0],
                $existingNumbers[count($existingNumbers) - 1]
            );
            $suggestedNos = array_values(
                array_diff($idealNos, $existingNumbers)
            );
            $newCounter = empty($suggestedNos)
                ? $existingNumbers[count($existingNumbers) - 1] + 1
                : $suggestedNos[0];
        }

        $newCounter = str_pad($newCounter, $prefixDigit, "0", STR_PAD_LEFT);
        $generatedNumberArray[
            array_search(null, $generatedNumberArray)
        ] = $newCounter;

        return implode("", $generatedNumberArray);
    }
}
