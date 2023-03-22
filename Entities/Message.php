<?php

namespace Modules\Messages\Entities;

use Eloquent;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Messages\Classes\Messager;
use Modules\Messages\Classes\Messagers\WhatsApp;
use Modules\Messages\Classes\Sender;
use Modules\Messages\Entities\Helpers\DeliveryStatus;
use Modules\Messages\Entities\Helpers\SerializeMessageMessager;
use Modules\Messages\Entities\Helpers\useCascade;


/**
 * php artisan ide-helper:models "Modules\Messages\Entities\Message"
 * Modules\Messages\Entities\Message
 *
 * @property int $id
 * @property string|null $message_id
 * @property Messager $messager
 * @property int $phone
 * @property string|null $text
 * @property string|null $notification_class
 * @property int $incoming
 * @property DeliveryStatus $delivery_status
 * @property string $datetime
 * @method static Builder|Message newModelQuery()
 * @method static Builder|Message newQuery()
 * @method static Builder|Message query()
 * @method static Builder|Message whereDatetime($value)
 * @method static Builder|Message whereDeliveryStatus($value)
 * @method static Builder|Message whereId($value)
 * @method static Builder|Message whereIncoming($value)
 * @method static Builder|Message whereMessage($value)
 * @method static Builder|Message whereMessageId($value)
 * @method static Builder|Message whereMessager($value)
 * @method static Builder|Message wherePhone($value)
 * @property int|null $cascade_id
 * @property-read Cascade|null $cascade
 * @method static Builder|Message whereCascadeId($value)
 * @method static Builder|Message whereNotificationClass($value)
 * @method static Builder|Message whereText($value)
 * @mixin Eloquent
 */
class Message extends Model
{
    use useCascade;

    protected $fillable = [
        'id',
        'message_id',
        'messager',
        'phone',
        'text',
        'notification_class',
        'incoming',
        'delivery_status',
        'cascade_id',
        'datetime'
    ];

    //значение по умолчанию
    protected $attributes = [
        'messager' => WhatsApp::class
    ];

    protected $casts = [
        'messager' => SerializeMessageMessager::class,
        'delivery_status' => DeliveryStatus::class
    ];

    public $timestamps = false;

    protected $table = 'messages_messages';


    public function cascade(): BelongsTo
    {
        return $this->belongsTo(Cascade::class, 'cascade_id', 'id');
    }

    /**
     * @throws Exception
     */
    public function send(): array
    {
        return Sender::set($this)->send();
    }

}



