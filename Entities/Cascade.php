<?php

namespace Modules\Messages\Entities;

use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Modules\Messages\Entities\Helpers\SerializeCascadeSequence;
use Modules\Messages\Entities\Helpers\SerializeJsonUnicode;


/**
 * Modules\Messages\Entities\Cascade
 *
 * @property int $id
 * @property boolean $completed
 * @property array|null $template_vars
 * @property Collection $messagers_sequence
 * @property string $datetime
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Messages\Entities\Message> $messages
 * @property-read int|null $messages_count
 * @method static  Builder|Cascade newModelQuery()
 * @method static  Builder|Cascade newQuery()
 * @method static  Builder|Cascade query()
 * @method static  Builder|Cascade whereDatetime($value)
 * @method static  Builder|Cascade whereId($value)
 * @method static  Builder|Cascade whereMessagersVars($value)
 * @method static  Builder|Cascade whereTemplateVars($value)
 * @mixin  Eloquent
 */
class Cascade extends Model
{

    protected $table = 'messages_cascades';

    protected $fillable = [
        'id',
        'template_vars',
        'messagers_sequence',
        'completed',
        'datetime'
    ];

    protected $casts = [
        'template_vars' => SerializeJsonUnicode::class,
        'messagers_sequence' => SerializeCascadeSequence::class,
    ];

    public $timestamps = false;


    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'cascade_id', 'id');
    }



}
