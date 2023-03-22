<?php


namespace Modules\Messages\Entities\Helpers;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class SerializeJsonUnicode implements CastsAttributes
{


    /**
     * @param Model $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     * @return array
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): array
    {

        if(is_array($value))
           return $value;

        if(is_string($value))
            return json_decode($value, true);

        return $value;
    }


    /**
     * Отправка в БД
     * @param Model $model
     * @param string $key
     * @param array|Collection $value
     * @param array $attributes
     * @return string
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }


}
