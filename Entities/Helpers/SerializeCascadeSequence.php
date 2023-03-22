<?php


namespace Modules\Messages\Entities\Helpers;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Modules\Messages\Classes\MessagerType;

class SerializeCascadeSequence implements CastsAttributes
{


    /**
     * @param Model $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     * @return Collection<>
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): Collection
    {

        if(is_string($value))
            $value = json_decode($value, true);

        return collect($value)->mapWithKeys(function ($read_time, $messager_name) {

            return [MessagerType::getMessagerClass($messager_name) => (int)$read_time];
        });
    }


    /**
     * @param Model $model
     * @param string $key
     * @param array|Collection $value
     * @param array $attributes
     * @return string
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {

        return collect($value)->mapWithKeys(function ($read_time, $messager_class) {

            return [MessagerType::getNameMessager($messager_class) => (int)$read_time];
        })->toJson();
    }


}
