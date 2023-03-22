<?php


namespace Modules\Messages\Entities\Helpers;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Modules\Messages\Classes\Messager;
use Modules\Messages\Classes\MessagerType;

class SerializeMessageMessager implements CastsAttributes
{


    /**
     * @param Model $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     * @return Messager
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): Messager
    {

        if($value instanceof Messager)
            return $value;

        return MessagerType::getMessagerObject($value);
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
        return MessagerType::getNameMessager($value);
    }


}
