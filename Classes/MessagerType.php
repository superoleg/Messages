<?php


namespace Modules\Messages\Classes;

use Modules\Messages\Classes\Messagers\SMS;
use Modules\Messages\Classes\Messagers\VK;
use Modules\Messages\Classes\Messagers\WhatsApp;

class MessagerType
{

    const MESSAGERS = [
        'WhatsApp' => WhatsApp::class,
        'VK' => VK::class,
        'SMS' => SMS::class,
    ];


    public static function getMessagerObject(Messager|string $name): Messager
    {
        $class = self::getMessagerClass($name);
        return new $class();
    }

    public static function getMessagerClass(Messager|string $name): String
    {
        return self::MESSAGERS[self::getNameMessager($name)];
    }

    public static function getNameMessager(Messager|string $messager): ?string
    {
        return class_basename($messager);
    }

}
