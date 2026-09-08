<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\Message;
use Cake\Database\Schema\TableSchemaInterface;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class MessagesTable extends Table
{
    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('messages');
        $this->setPrimaryKey('id');
        $this->setDisplayField('id');
        $this->setEntityClass(Message::class);
    }

    /**
     * Build schema
     *
     * @param \Cake\Database\Schema\TableSchemaInterface $schema The schema object to modify.
     * @return \Cake\Database\Schema\TableSchemaInterface
     */
    public function buildSchema(TableSchemaInterface $schema): TableSchemaInterface
    {
        return $schema->addColumn('content', 'text', [
            'null' => false,
            'comment' => 'Message content to print',
        ])
        ->addColumn('ip_address', 'string', [
            'null' => false,
            'comment' => 'IP address of the submitter',
        ])
        ->addColumn('status', 'string', [
            'null' => false,
            'default' => 'pending',
            'comment' => 'Message status (pending, printed, failed)',
        ])
        ->addColumn('printed_at', 'datetime', [
            'null' => true,
            'comment' => 'When the message was printed',
        ])
        ->addColumn('created', 'datetime', [
            'null' => false,
            'default' => 'CURRENT_TIMESTAMP',
            'comment' => 'Creation timestamp',
        ])
        ->addColumn('modified', 'datetime', [
            'null' => false,
            'default' => 'CURRENT_TIMESTAMP',
            'comment' => 'Last modification timestamp',
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        return $validator
            ->requirePresence('content', 'create')
            ->notEmptyString('content', 'Please enter a message')
            ->maxLength('content', 500, 'Message too long (max 500 characters)');
    }

    /**
     * Patch a message entity
     *
     * @param \App\Model\Entity\Message $entity The entity to patch.
     * @param array $data The data to patch.
     * @return \App\Model\Entity\Message
     */
    public function patchMessage(Message $entity, array $data): Message
    {
        return $this->patchEntity($entity, $data);
    }
}
