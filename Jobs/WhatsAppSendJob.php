<?php

namespace Modules\Messages\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Messages\Entities\Helpers\DeliveryStatus;
use Modules\Messages\Entities\Message;

class WhatsAppSendJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    protected Message $message;

    const URL = 'https://wappi.pro/api/sync/message/send';


    public function __construct(Message $message)
    {

        $this->onQueue('whatsapp');
        $this->message = $message;
    }

    public function handle(): void
    {

        $message = $this->message;

        $response = Http::retry(3, 900)
            ->withHeaders(['Authorization' => $_ENV['WHATSAPP_TOKEN']])
            ->post(self::URL.'?profile_id='.$_ENV['WHATSAPP_PROFILE_ID'],
                [
                    'recipient' => (string)$message->phone,
                    'body' => $message->text,
                ]
            )->json();

        if(isset($response['status']) && $response['status'] == 'done'){

            $message->message_id = $response['message_id'];
            $message->delivery_status = DeliveryStatus::DELIVERED;

        }else{

            Log::debug($response);
            $message->delivery_status = DeliveryStatus::ERROR;
        }

        $message->save();
        sleep(50);
    }



}
