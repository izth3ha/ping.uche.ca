<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\PrinterService;
use Cake\Http\Response;

class MessagesController extends AppController
{
    /**
     * Initialize method
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('RequestHandler');
    }

    /**
     * Index method
     *
     * @return void
     */
    public function index(): void
    {
        $messages = $this->paginate($this->Messages->find()->order(['Messages.created' => 'DESC']));
        $this->set(compact('messages'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null
     */
    public function add(): ?Response
    {
        $message = $this->Messages->newEmptyMessage();

        if ($this->request->is('post')) {
            $message = $this->Messages->patchMessage($message, $this->request->getData());
            $message->ip_address = $this->request->getClientIp();
            $message->status = 'pending';

            if ($this->Messages->save($message)) {
                // Create transaction data for display
                $transactionData = [
                    'transactionNum' => $message->id,
                    'content' => $message->content,
                    'ipAddress' => $message->ip_address,
                ];
                $this->set('transactionData', $transactionData);
                $this->set('transactionComplete', true);
            } else {
                $this->Flash->error(__('Unable to save message. Please try again.'));
            }
        }

        $this->set(compact('message'));
    }

    /**
     * Preview method
     *
     * @param int $id Message ID.
     * @return void
     */
    public function preview(int $id)
    {
        $message = $this->Messages->get($id);

        $printer = new PrinterService();
        $receiptText = $printer->buildReceipt($message);

        $this->set('message', $message);
        $this->set('receiptText', $receiptText);
        $this->set('printUrl', ['action' => 'print', $id]);
    }

    /**
     * Print method
     *
     * @param int|null $id Message ID.
     * @return \Cake\Http\Response|null
     */
    public function print(?int $id = null): ?Response
    {
        if (!$id) {
            $this->Flash->error(__('Invalid message ID'));

            return $this->redirect(['action' => 'index']);
        }

        $printer = new PrinterService();
        if ($printer->printMessage($id)) {
            $this->Flash->success(__('Message printed successfully.'));
        } else {
            $this->Flash->error(__('Failed to print message. Check printer connection.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
