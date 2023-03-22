<?php

namespace Modules\Messages\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Modules\Messages\Entities\Helpers\DeliveryStatus;
use Modules\Messages\Entities\Message;

class SendingDeferredMessagesJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public int $tries = 1;

    const MAX_COUNT = 30;



    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     */
    public function handle(): void
    {

        $messages = $this->findDeferredMessages();

        foreach ($messages as $message){

            $message->send();
        }
    }

    /**
     * @return Collection<Message>
     */
    private function findDeferredMessages(): Collection
    {
        return Message::query()->where('delivery_status', '=', 'DEFERRED')
           ->where('datetime', '<', now())
           ->limit(self::MAX_COUNT)
           ->get();
    }

}
