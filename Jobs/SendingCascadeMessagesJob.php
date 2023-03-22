<?php


namespace Modules\Messages\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Collection;
use Modules\Messages\Entities\Cascade;
use Modules\Messages\Entities\Message;


class SendingCascadeMessagesJob implements ShouldQueue
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

        $cascades = $this->findCascadesMessages();

        foreach ($cascades as $cascade){

            $message = Message::query()
                ->where('cascade_id', $cascade->id)
                ->orderBy('id')
                ->first();

            if(!empty($message) && $message instanceof Message) {
                $message->setCascade($cascade);
                $message->send();
            }
        }

    }


    private function findCascadesMessages(): Collection
    {
        return Cascade::query()
            ->where('completed', false)
            ->where('datetime', '<', now())
            ->limit(self::MAX_COUNT)
            ->get();
    }

}
