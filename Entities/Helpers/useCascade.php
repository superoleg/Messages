<?php

namespace Modules\Messages\Entities\Helpers;

use Carbon\Carbon;
use Modules\Messages\Classes\Messagers\SMS;
use Modules\Messages\Classes\Messagers\VK;
use Modules\Messages\Classes\Messagers\WhatsApp;
use Modules\Messages\Entities\Message;
use Modules\Messages\Entities\Cascade;

trait useCascade
{

    private ?Cascade $cascade_object;

    public array $messagers_sequence = [
        WhatsApp::class => 300,
        VK::class => 200,
        SMS::class => 0
    ];

    public array $argumentsTemplate = [];


    //abstract public function createCascade(array $messagers_sequence = []): ?Cascade;

    public function createCascade(array $messagers_sequence = []): ?Cascade
    {

        $message = $this;

        if (!empty($messagers_sequence))
            $this->messagers_sequence = $messagers_sequence;

        //если месседжеров меньше 2-х - то смысла в каскаде нет
        if (count($this->messagers_sequence) < 2)
            return null;

        if (empty($message->cascade_id) && ($message instanceof Message)) {

            $cascade = new Cascade();
            $cascade->messagers_sequence = $this->messagers_sequence;
            $cascade->template_vars = $message->argumentsTemplate;
            $cascade->datetime = $this->cascadeUseTime($message);
            ($this->cascade_object = $cascade)->save();

            //Изменение состояния у сообщения
            $message->messager = $cascade->messagers_sequence->keys()->first();
            $message->cascade_id = $this->cascade_object->id;
        }
        return $cascade ?? $this->getCascade();
    }

    public function setCascade(Cascade $cascade): Cascade
    {
        return $this->cascade_object = $cascade;
    }


    public function getCascade(): ?Cascade
    {
        $cascade = $this->cascade_object ?? null;

        if (empty($cascade) && isset($this->cascade_id)) {

            return $this->cascade_object = Cascade::find($this->cascade_id);
        }
        return $cascade;
    }


    private function cascadeUseTime(Message $message): Carbon
    {
        $time_send = new Carbon($message->datetime ?? 'now');

        return $time_send->addSeconds(reset($this->messagers_sequence));
    }


}
