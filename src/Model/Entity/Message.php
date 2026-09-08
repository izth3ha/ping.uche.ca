<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Message Entity.
 *
 * @property int $id
 * @property string $content
 * @property string|null $ip_address
 * @property string $status
 * @property \Cake\I18n\FrozenTime|null $printed_at
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 */
class Message extends Entity
{
    /**
     * Fields that can be mass assigned using newEmptyEntity() or patchEntity().
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'content' => true,
        'ip_address' => true,
        'status' => true,
        'printed_at' => true,
        'created' => true,
        'modified' => true,
    ];

    /**
     * Fields that are excluded from JSON versions of the entity.
     *
     * @var array<string>
     */
    protected array $_hidden = [
        'created',
        'modified',
    ];
}
