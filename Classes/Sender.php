<?php

namespace Modules\Messages\Classes;

use Exception;
use Illuminate\Support\Carbon;
use Modules\Messages\Entities\Cascade;
use Modules\Messages\Entities\Helpers\DeliveryStatus;
use Modules\Messages\Entities\Message;

class Sender
{

    public Message $message;

    //Максимальное время на отправку сообщения
    const MAX_TIME = 3600;

    /**
     * Месседжеры, через которые отправились сообщения в данном запросе
     * @var array<Message>
     */
    private array $sent_recently_messages = [];


    /**
     * @param Message $message
     * @throws Exception
     */
    public function __construct(Message $message)
    {
        if (empty($message->phone) || empty($message->text))
            throw new Exception("Не заполнено сообщение или телефон");

        if (empty($message->messager) && empty($message->cascade_id))
            throw new Exception("Сообщение не имеет информации через какой месседжер его отправлять");

        if ($message->delivery_status == DeliveryStatus::DEFERRED && (strtotime($message->datetime) + self::MAX_TIME) < time()) {
            $message->update(['delivery_status' => DeliveryStatus::ERROR]);
            throw new Exception('Превышено время отправления сообщения');
        }

        if (isset($message->cascade) && (strtotime($message->cascade->datetime) + self::MAX_TIME) < time()) {
            $message->cascade->update(['completed' => true]);
            throw new Exception('Превышено время отправления каскада');
        }
        $this->message = $message;
    }


    /**
     * @throws Exception
     */
    public static function set(Message $message): static
    {
        return new static($message);
    }

    /**
     * @return array<Message>
     * @throws Exception
     */
    public function send(): array
    {
        //отправка в очередь отложенных сообщений
        if (!empty($this->message->datetime) && (strtotime($this->message->datetime) > time())) {
            $this->message->delivery_status = DeliveryStatus::DEFERRED;
            $this->message->save();
            return [$this->message];
        }

        //Отправка каскада сообщений
        if (isset($this->message->cascade_id))
            return $this->sendViaCascade($this->message->getCascade());

        //отправка одного сообщения
        $this->sendAndSave($this->message);

        return $this->sent_recently_messages;
    }


    /**
     * Обработка исключений, которые не должны произойти - для безопасности и отлавливания некорректного поведения кода
     *
     * @throws Exception
     */
    private function cascadeValidate(Cascade $cascade): void
    {
        //
        if ($cascade->completed) {

            if ($this->message->delivery_status == DeliveryStatus::DEFERRED)
                $this->message->update(['delivery_status' => DeliveryStatus::ERROR]);
            throw new Exception('Попытка отправить сообщения из уже завершенного каскада');
        }
        if (!$cascade->wasRecentlyCreated
            && isset($cascade->datetime)
            && strtotime($cascade->datetime) > time()
            && strtotime($this->message->datetime ?? 0) > time()
        )
            throw new Exception('Попытка отправить сообщение каскадом раньше времени');
    }


    /**
     * @param Cascade $cascade
     * @return array
     * @throws Exception
     */
    private function sendViaCascade(Cascade $cascade): array
    {

        $this->cascadeValidate($cascade);

        //определение через какие месседжеры еще не отправлено и разбитие на фрагменты, которые нужно отправить
        //В данном запросе отправляется первый фрагмент
        $chunk_messagers_to_send = $cascade->messagers_sequence
            ->except(self::getSentMessagers($cascade))
            ->chunkWhile(function ($read_time, $messager_class, $prev_read_time) {
                return ($prev_read_time->last() == 0);
            });


        //отправка сообщений
        foreach ($chunk_messagers_to_send->first() as $messager_class => $read_time) {

            $messager = MessagerType::getMessagerObject($messager_class);

            //Создание и отправка сообщения
            $message_send = ($this->message->messager->getClassName() == $messager_class) ? $this->message : new Message();
            $message_send->phone = $this->message->phone;
            $message_send->messager = $messager;
            $message_send->cascade_id = $cascade->id;
            $message_send->notification_class = $this->message->notification_class;
            $message_send->text = $this->getTextMessage($messager, $cascade->template_vars);
            self::sendAndSave($message_send);
        }

        $cascade->completed = ($chunk_messagers_to_send->count() < 2);
        $cascade->datetime = Carbon::now()->addSeconds(
            ($chunk_messagers_to_send->first())->last() ?? 0
        );
        $cascade->save();

        return $this->sent_recently_messages;
    }


    private function sendAndSave(Message $message): void
    {
        $message->delivery_status = DeliveryStatus::PENDING;
        $message = $message->messager->send($message);
        $message->save();
        $this->sent_recently_messages[] = $message;
    }

    /**
     * @throws Exception
     */
    private function getTextMessage(Messager $messager, array $arguments_template = []): string
    {
        if (isset($this->message->notification_class)) {

            $notification = new $this->message->notification_class();

            if (is_subclass_of($notification, Notification::class))
                $textFromTemplate = $notification->renderTemplate($messager, $arguments_template);
        }
        return $textFromTemplate ?? $this->message->text;
    }


    /**
     * Получение месседжеров, через которые уже отправилось сообщение
     *
     * @param Cascade $cascade
     * @return array
     * @throws Exception
     */
    private function getSentMessagers(Cascade $cascade): array
    {

        foreach ($this->sent_recently_messages as $message)
            $sent_messagers[] = $message->messager->getClassName();

        //если каскад был создан не в этом запросе - иначе лишний раз в БД не лезем
        if (isset($cascade->id) && !$cascade->wasRecentlyCreated) {

            Message::query()
                ->where('cascade_id', $cascade->id)
                ->where('delivery_status', '!=', DeliveryStatus::DEFERRED->name)
                ->each(function (Message $message) use (&$sent_messagers) {
                    $sent_messagers[] = $message->messager->getClassName();
                });
        }

        return $sent_messagers ?? [];
    }

}
