<?php


namespace Modules\Messages\Classes;

use Carbon\Carbon;
use Exception;
use Modules\Messages\Classes\Messagers\WhatsApp;
use Modules\Messages\Classes\Traits\MessagerTemplate;

use Modules\Messages\Entities\Message;


abstract class Notification
{

    use MessagerTemplate;

    protected Message $message;

    protected array $messagers_sequence;

    protected bool $useCascade = true;

    public function __construct(Message $message = null)
    {
        $this->message = $message ?? new Message();
    }

    /**
     * Начальные обязательные сеттеры (один из)
     */

    public static function setPhone(int $phone): Notification
    {
        return (new static())->setMessageParam('phone', $phone);
    }


    /**
     * Дополнительные необязательные сеттеры
     */

    public function setMessager(Messager $messager): Notification
    {
        $this->useCascade = false;
        $this->message->messager = $messager;
        return $this;
    }


    /**
     * @param bool|array $messagers_sequence
     * @return $this
     */
    public function useCascade(bool|array $messagers_sequence = []): Notification
    {
        if(is_bool($messagers_sequence) && !$messagers_sequence)
            $this->useCascade = false;

        if(is_array($messagers_sequence) && !empty($messagers_sequence))
            $this->messagers_sequence = $messagers_sequence;

        if(!empty($this->messagers_sequence))
        $this->message->messagers_sequence = $this->messagers_sequence;

        return $this;
    }

    public function setTime(Carbon $time): Notification
    {
        return $this->setMessageParam('datetime', $time);
    }


    final protected function setMessageParam(string $param, $value): Notification
    {
        $this->message->$param = $value;
        return $this;
    }

    public function setTemplateArguments(...$arguments): Notification
    {
        $this->templateArgumentsTransform($arguments);
        $this->message->argumentsTemplate = $arguments;
        return $this;
    }


    /**
     * @throws Exception
     */
    public function send(): array
    {
        $this->message->notification_class = $this::class;

        if($this->useCascade)
            $this->message->createCascade();

        if (empty($this->message->text))
            $this->message->text = $this->renderTemplate($this->message->messager, $this->message->argumentsTemplate);

        return $this->message->send();
    }



    //
    //вызывается при входящем сообщении после определенного типа уведомления
    //если реакция отрабатывается - возвращать true
    //в противном случае отправится дальше для уведомления оператору
    //
    public function reactionNewMessage(Message $incoming_message): bool
    {
        return false;
    }

    //если нужно отредактировать входящие аргументы шаблона
    protected function templateArgumentsTransform(array &$arguments): void
    {
    }


}
