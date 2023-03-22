<?php

namespace Modules\Messages\Classes\Messagers;


use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Modules\Messages\Classes\Messager;
use Modules\Messages\Entities\Helpers\DeliveryStatus;
use Modules\Messages\Entities\Message;
use Modules\Messages\Jobs\WhatsAppSendJob;


class WhatsApp extends Messager
{


    public function send(Message $message): Message
    {

        $message->delivery_status = DeliveryStatus::IN_QUEUE;
        $message->save();

        log::alert('Отправка сообщения через WhatsApp', $message->toArray());

        //отправка в однопоточную очередь, чтобы снизить риск блокировки
        //dispatch(new WhatsAppSendJob($message))->onQueue('whatsapp');

        return $message;
    }


    public function webHookHandler(Request $request): ?Message
    {

        //разбор веб хука
        $request_json_obj = json_decode($request->getContent())->messages;
        $message_request = is_array($request_json_obj) ? $request_json_obj[0] : $request_json_obj;


        if($message_request->wh_type == 'incoming_message'){

            $this->delete_proxy_webhook($message_request);

            //todo добавить аттачменты вместо проверки (сейчас в запросе посылаются в сериализованном виде даже видео)
            if($message_request->type == 'chat') {
                $message = new Message();
                $message->message_id = $message_request->id;
                $message->incoming = true;
                $message->phone = strstr($message_request->from, '@', true);
                $message->text = $message_request->body;
            }

        }
        elseif ($message_request->wh_type == 'delivery_status'){

            if($message = Message::query()->where('message_id', $message_request->id)->first())
                $message->delivery_status = $message_request->status;

        }
        return $message ?? null;
    }



    /**
     * TODO удалить после переноса функционала
     */
    private function delete_proxy_webhook( $request_json): void
    {

        if($request_json) {

            $data = [
                'remoteJid' => $request_json->from,
                'fromMe' => false,
                'id' => $request_json->id,
                'messageTimestamp' => $request_json->timestamp,
                'pushName' => $request_json->senderName,
                'message' => $request_json->body
            ];
            $array_send = [
                'instance_id' => '123456789',
                'event' => 'messages.upsert',
                'data' => $data
            ];
            Http::retry(3, 900)
                ->post('https://crm.volos.me/mailing/sender/sub.php', $array_send);

        }
    }



}
