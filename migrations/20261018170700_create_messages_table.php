<?php
use Cake\Database\Schema\TableSchemaInterface;
use Cake\Database\Schema\TableSchema;
use Cake\Migrations\Migration;

class CreateMessagesTable extends Migration
{
    public function up()
    {
        $table = $this->table('messages');
        $table->addColumn('content', 'text', [
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
        ])
        ->create();
    }

    public function down()
    {
        $this->dropTable('messages');
    }
}
