<?php $this->assign('title', 'Submit Message'); ?>
<h1><?= __('Submit Message to Printer') ?></h1>

<div class="message-form">
    <?= $this->Form->create() ?>
    <fieldset>
        <legend><?= __('Enter your message') ?></legend>
        <?= $this->Form->control('content', ['rows' => '5', 'label' => __('Message Content')]) ?>
    </fieldset>
    <?= $this->Form->button(__('Submit Message'), ['class' => 'btn-primary']) ?>
    <?= $this->Form->end() ?>
</div>

<?php if (!empty($transactionData)): ?>
<div class="transaction-success">
    <h2><?= __('Transaction Complete') ?></h2>
    <p><strong><?= __('Transaction Number:') ?></strong> <?= h($transactionData['transactionNum']) ?></p>
    <p><strong><?= __('Message:') ?></strong> <?= h($transactionData['content']) ?></p>
    <p><strong><?= __('IP Address:') ?></strong> <?= h($transactionData['ipAddress']) ?></p>
    <p><?= $this->Html->link(__('Submit Another Message'), ['action' => 'add'], ['class' => 'btn-primary']) ?></p>
</div>
<?php endif; ?>
