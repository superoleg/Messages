<?php


namespace Modules\Messages\Classes;

use Illuminate\Http\Request;


//use Illuminate\Support\Facades\Log;
use Modules\Messages\Entities\Cascade;
use Modules\Messages\Entities\Helpers\DeliveryStatus;
use Modules\Messages\Entities\Message;
use Modules\Messages\Events\NewMessageWithoutReaction;

class MessagersWebHook
{

    /**
     * Сюда приходят все вебхуки от всех месседжеров
     *
     * @param Messager $messager
     * @param Request $request
     */
    public static function handle(Messager $messager, Request $request): void
    {

        if ($message = $messager->webHookHandler($request)) {

            $message->messager = $messager;

            //если это входящее сообщение и (модель сообщения была создана в текущем запросе)
            if ((empty($message->id) || $message->wasRecentlyCreated) && $message->incoming) {

                self::incomingMessageHandle($message);
            }
            $message->save();


            if($message->delivery_status == DeliveryStatus::READ && isset($message->cascade_id)){

                Cascade::find($message->cascade_id)->update(['completed' => true]);
            }
        }
    }


    /**
     * Обработка нового входящего сообщения
     *
     * @param Message $message
     */
    private static function incomingMessageHandle(Message $message): void
    {
        if ($previous_message = self::getPreviousMessageInMessager($message)) {

            if (!self::runReactionOnMessage($message, $previous_message)) {

                //если реакция не отработала - уведомляем о новом сообщении
                event(new NewMessageWithoutReaction($message));
            }
        }
    }

    /**
     * Запуск реакции на сообщение, если она предполагается у уведомления
     *
     * @param Message $message
     * @param ?Message $previous_message
     * @return bool
     */
    private static function runReactionOnMessage(Message $message, ?Message $previous_message): bool
    {
        $class = $previous_message->notification_class ?? null;

        if ($class && class_exists($class)) {

            $notification = new $class();
            if ($notification instanceof Notification) {

                $notification->useCascade(false);
                $notification->setMessager(MessagerType::getMessagerObject($message->messager));

                return $notification->reactionNewMessage($message);
            }
        }
        return false;
    }

    /**
     * Получение последнего(предыдущего) сообщения от клиента в выбранном месседжере
     *
     * @param Message $message
     * @return Message|null
     */
    private static function getPreviousMessageInMessager(Message $message): ?Message
    {
        $message = Message::query()->where('messager', '=', $message->messager->getName())
            ->where('id', '!=', $message->id)
            ->where('phone', '=', $message->phone)
            ->orderByDesc('id')
            ->first();

        return $message ?? null;
    }


}
