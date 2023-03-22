<?php


namespace Modules\Messages\Classes\Messagers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Messages\Classes\Messager;
use Modules\Messages\Entities\Message;

class SMS extends Messager
{


    public function getRouteSecret(): string
    {
        // TODO: Implement getRouteSecret() method.
        return '';
    }


    public function send(Message $message): Message
    {

        log::alert('Отправка сообщения через SMS', $message->toArray());

        return  $message;
    }

    public function webHookHandler(Request $request): ?Message
    {
        return null;
    }
}
