<?php

namespace tanyudii\YinNumber\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use tanyudii\YinNumber\Contracts\NumberModel;
use tanyudii\YinNumber\Contracts\WithNumberSetting;
use tanyudii\YinNumber\Exceptions\YinNumberException;
use tanyudii\YinNumber\Type;

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
        $numberModelNamespace = Config::get("yin-number.models.number");
        $numberModel = new $numberModelNamespace();

        if (!$numberModel instanceof NumberModel) {
            throw new YinNumberException(
                "The number model is not instance of NumberModel"
            );
        } elseif ($numberModel instanceof WithNumberSetting) {
            throw new YinNumberException(
                "The number model is not instance of WithNumberSetting"
            );
        }

        $numberSetting = $numberModelNamespace::with("numberComponents")
            ->where("model", $modelNamespace)
            ->first();

        $model = new $modelNamespace();
        $tableName = $model->getTable();

        if (empty($numberSetting) || $numberSetting->numberComponents->isEmpty()) {
            return DB::select("show table status like '{$tableName}'")[0]->Auto_increment ??
                DB::table($tableName)->count("*") + 1;
        }

        return $this->generate(
            $numberSetting->numberComponents->toArray(),
            $tableName,
            $model->getNumberColumn(),
            $numberSetting->reset_type,
            $model->getDateColumn(),
            $date,
            $subjectId,
        );
    }

    /**
     * @param string $modelNamespace
     * @param null $date
     * @param null $subjectId
     * @return Model
     * @throws YinNumberException
     */
    public function bookingNumber(string $modelNamespace, $date = null, $subjectId = null)
    {
        $numberModelNamespace = Config::get("yin-number.models.number");

        $numberSetting = $numberModelNamespace::with("numberComponents")
            ->where("model", $modelNamespace)
            ->first();

        if (empty($numberSetting)) {
            throw new YinNumberException(
                "The number model is not configured for $modelNamespace."
            );
        }

        $model = new $modelNamespace();
        $tableName = $model->getTable();

        $bookedNumberModelNamespace = Config::get("yin-number.models.booked_number");

        return $bookedNumberModelNamespace::query()->create([
            'entity' => $tableName,
            'number' => $this->generateNumber($modelNamespace, $date, $subjectId),
        ]);
    }

    /**
     * @param array $components
     * @param string $tableName
     * @param string $numberColumn
     * @param string $resetType
     * @param string $dateColumn
     * @param string|null $date
     * @param null $exceptSubjectId
     * @return string
     * @throws YinNumberException
     */
    public function generate(
        array $components,
        string $tableName,
        string $numberColumn,
        string $resetType,
        string $dateColumn,
        string $date = null,
        $exceptSubjectId = null
    ) {
        $date = is_null($date) ? Carbon::now() : Carbon::parse($date);

        $prefixDigit = 0;
        $digitBeforeCounter = 0;
        $generatedNumberArray = [];
        $queryNumber = "";

        if (!in_array($resetType, Type::RESET_TYPE_OPTIONS)) {
            throw new YinNumberException("The reset type is invalid.");
        }

        foreach ($components as $component) {
            if (!isset($component['type']) || !in_array($component['type'], Type::COMPONENT_TYPE_OPTIONS)) {
                throw new YinNumberException("The component type is invalid.");
            } elseif (!isset($component['format'])) {
                throw new YinNumberException("The component format is invalid.");
            }

            $componentType = $component['type'];
            $componentFormat = $component['format'];

            if (
                !in_array(null, $generatedNumberArray) &&
                $component['type'] != Type::COMPONENT_TYPE_COUNTER
            ) {
                $digitBeforeCounter += strlen($componentFormat);
            }

            switch ($componentType) {
                case Type::COMPONENT_TYPE_TEXT:
                    array_push($generatedNumberArray, $componentFormat);
                    $queryNumber .= str_replace("_", "\\_", $componentFormat);
                    break;
                case Type::COMPONENT_TYPE_YEAR:
                    $dateText = $date->format($componentFormat);
                    array_push($generatedNumberArray, $dateText);

                    if (is_null($resetType)) {
                        $dateText = str_repeat("_", strlen($dateText));
                    }

                    $queryNumber .= $dateText;
                    break;
                case Type::COMPONENT_TYPE_MONTH:
                    $dateText = $date->format($componentFormat);
                    array_push($generatedNumberArray, $dateText);

                    if (
                        is_null($resetType) ||
                        $resetType ==
                        Type::RESET_TYPE_YEARLY
                    ) {
                        $dateText = str_repeat("_", strlen($dateText));
                    }

                    $queryNumber .= $dateText;
                    break;
                case Type::COMPONENT_TYPE_DAY:
                    $dateText = date($componentFormat, strtotime($date));
                    array_push($generatedNumberArray, $dateText);

                    if (
                        is_null($resetType) ||
                        in_array($resetType, [
                            Type::RESET_TYPE_YEARLY,
                            Type::RESET_TYPE_MONTHLY,
                        ])
                    ) {
                        $dateText = str_repeat("_", strlen($dateText));
                    }

                    $queryNumber .= $dateText;
                    break;
                case Type::COMPONENT_TYPE_COUNTER:
                    array_push($generatedNumberArray, null);
                    $queryNumber .= str_repeat("_", $componentFormat);
                    $prefixDigit = $componentFormat;
                    break;
            }
        }

        $subjectNumbers = DB::table($tableName)
            ->where($numberColumn, "like", $queryNumber)
            ->when(
                in_array($resetType, [
                    Type::RESET_TYPE_YEARLY,
                    Type::RESET_TYPE_MONTHLY,
                ]),
                function ($query) use ($dateColumn, $date) {
                    $query->whereYear($dateColumn, $date->format("Y"));
                }
            )
            ->when(
                $resetType == Type::RESET_TYPE_MONTHLY,
                function ($query) use ($dateColumn, $date) {
                    $query->whereMonth($dateColumn, $date->format("m"));
                }
            )
            ->when(!is_null($exceptSubjectId), function ($query) use ($exceptSubjectId) {
                $query->where("id", "!=", $exceptSubjectId);
            })
            ->orderBy($numberColumn)
            ->pluck($numberColumn)
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

        $bookedNumberModelNamespace = Config::get("yin-number.models.booked_number");

        do {
            $tempGeneratedNumberArray = $generatedNumberArray;
            $tempGeneratedNumberArray[array_search(null, $tempGeneratedNumberArray)] = str_pad($newCounter, $prefixDigit, "0", STR_PAD_LEFT);
            $number = implode("", $tempGeneratedNumberArray);

            $isNumberBooked = $bookedNumberModelNamespace::query()
                ->where('table', $tableName)
                ->where('number', $number)
                ->exists();

            if ($isNumberBooked) {
                $newCounter++;
            }
        } while ($isNumberBooked);

        return $number;
    }
}
