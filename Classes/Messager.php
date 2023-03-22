<?php
namespace Modules\Messages\Classes;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Modules\Messages\Entities\Message;


abstract class Messager
{

    abstract public function send(Message $message): Message;
    abstract  public function webHookHandler(Request $request): ?Message;


    public function getRouteSecret(): string
    {
        return Config::get('messages.'.strtoupper($this->getName()).'_ROUTE_SECRET');
    }
    public function getNamePatternFile(): string
    {
        return $this->getName().'.blade.php';
    }
    final public function getName(): string
    {
        return MessagerType::getNameMessager($this);
    }
    final public function getClassName(): string
    {
        return MessagerType::getMessagerClass($this);
    }


}
