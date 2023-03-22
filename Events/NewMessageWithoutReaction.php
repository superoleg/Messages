<?php

namespace Modules\Messages\Events;

use Illuminate\Queue\SerializesModels;
use Modules\Messages\Entities\Message;

class NewMessageWithoutReaction
{
    use SerializesModels;



    public Message $message;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }
}
