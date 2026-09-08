<?php
use Cake\Database\Schema\Schema;
use Cake\Migration\Migration;

class CreateMessagesTable extends Migration
{
    public function up(): void
    {
        $table = $this->table('messages');
        $table->addColumn('content', 'text', [
            'null' => false,
            'comment' => 'Message content to print'
        ]);
        $table->addColumn('ip_address', 'string', [
            'null' => false,
            'comment' => 'IP address of the submitter'
        ]);
        $table->addColumn('status', 'string', [
            'null' => false,
            'default' => 'pending',
            'comment' => 'Message status (pending, printed, failed)'
        ]);
        $table->addColumn('printed_at', 'datetime', [
            'null' => true,
            'comment' => 'When the message was printed'
        ]);
        $table->addColumn('created', 'datetime', [
            'null' => false,
            'default' => 'CURRENT_TIMESTAMP',
            'comment' => 'Creation timestamp'
        ]);
        $table->addColumn('modified', 'datetime', [
            'null' => false,
            'default' => 'CURRENT_TIMESTAMP',
            'comment' => 'Last modification timestamp'
        ]);
        $table->create();
    }

    public function down(): void
    {
        $this->table('messages')->drop()->save();
    }
}
