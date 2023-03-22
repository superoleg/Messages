<?php


namespace Modules\Messages\Classes;

use Illuminate\Http\Request;

class RouteWebHook
{

    const NAMESPACE_MESSAGERS = 'Modules\Messages\Classes\Messagers';

    public function route(string $messager, string $secret_route, Request $request): void
    {
        if($messager = $this->loadClass($messager))
            if ($messager->getRouteSecret() == $secret_route) {

                 MessagersWebHook::handle($messager, $request);
                 return;
            }
        abort(404);
    }

    private function loadClass($name_class): ?Messager
    {
        $class = self::NAMESPACE_MESSAGERS.'\\'.$name_class;

        if(class_exists($class)){
            if (($messager = new $class()) instanceof Messager) {
                return $messager;
            }
        }
        return null;
    }

}
