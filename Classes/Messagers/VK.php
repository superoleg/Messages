<?php
namespace Modules\Messages\Classes\Messagers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Messages\Classes\Messager;
use Modules\Messages\Entities\Message;

class VK extends Messager
{



    public function send(Message $message): Message
    {
        // TODO: Implement send() method.

        log::alert('Отправка сообщения через VK', $message->toArray());

        return $message;
    }


    public function getRouteSecret(): string
    {
        // TODO: Implement getRouteSecret() method.
        return '';
    }


    public function webHookHandler(Request $request): ?Message
    {
        return null;
    }
}
