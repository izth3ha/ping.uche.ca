<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Entity\Message;
use Cake\Chronos\Chronos;
use Cake\Log\Log;
use Cake\ORM\TableRegistry;
use Exception;
use Mike42\Escpos\PrintConnectors\UsbConnector;
use Mike42\Escpos\Printer;
use stdClass;

class PrinterService
{
    /** Receipt line width in characters. */
    public const WIDTH = 48;

    /** Branding shown at the top of every receipt. */
    public const HEADER = 'PING.UCHE.CA';

    /** Closing line shown at the bottom of every receipt. */
    public const FOOTER = 'THANK YOU!';

    /**
     * @var string|null Printer file path
     */
    protected ?string $printerPath = null;

    /**
     * Constructor
     *
     * @param string|null $printerPath The file path to the printer device.
     */
    public function __construct(?string $printerPath = null)
    {
        $this->printerPath = $printerPath ?? env('PRINTER_PATH', '/dev/usb/lp0');
    }

    /**
     * Build a receipt from a database message.
     *
     * @param \App\Model\Entity\Message $message The message entity.
     * @return string The formatted receipt text.
     */
    public function buildReceipt(Message $message): string
    {
        $content = (string)($message->content ?? '');
        $ip = (string)($message->ip_address ?? '');
        $txNumber = (string)($message->id ?? '');

        $date = $message->created?->format('Y-m-d') ?? date('Y-m-d');
        $time = $message->created?->format('H:i:s') ?? date('H:i:s');

        $w = self::WIDTH;

        $lines = [];

        $lines[] = $this->rule('=', $w);
        $lines[] = $this->center(self::HEADER, $w);
        $lines[] = $this->rule('-', $w);
        $lines[] = '';
        $lines[] = 'Date:          ' . $date;
        $lines[] = 'Time:          ' . $time;
        $lines[] = 'Transaction:   ' . $txNumber;
        $lines[] = 'IP Address:    ' . $ip;
        $lines[] = '';

        // Wrap content to width
        $wrapped = $this->wrap($content, $w);
        foreach ($wrapped as $line) {
            $lines[] = $line;
        }

        $lines[] = '';
        $lines[] = $this->rule('-', $w);
        $lines[] = '';
        $lines[] = $this->center(self::FOOTER, $w);
        $lines[] = $this->rule('=', $w);

        return implode("\n", $lines) . "\n";
    }

    /**
     * Build a receipt from a raw content string (no database message).
     *
     * @param string $content The message content.
     * @param string|null $ip The IP address of the submitter.
     * @param string|null $txNumber The transaction number.
     * @return string The formatted receipt text.
     */
    public function buildReceiptFromString(string $content, ?string $ip = null, ?string $txNumber = null): string
    {
        $message = new stdClass();
        $message->content = $content;
        $message->ip_address = $ip;
        $message->id = $txNumber;
        $message->created = new Chronos();

        return $this->buildReceipt($message);
    }

    /**
     * Build a repeated separator line.
     *
     * @param string $char The character to repeat.
     * @param int $width The width to fill.
     * @return string The separator line.
     */
    protected function rule(string $char, int $width): string
    {
        return str_repeat($char, $width);
    }

    /**
     * Center a string within a fixed width.
     *
     * @param string $text The text to center.
     * @param int $width The width to center within.
     * @return string The centered text.
     */
    protected function center(string $text, int $width): string
    {
        $text = trim($text);
        $len = strlen($text);
        if ($len >= $width) {
            return substr($text, 0, $width);
        }
        $pad = intdiv($width - $len, 2);

        return str_repeat(' ', $pad) . $text;
    }

    /**
     * Word-wrap text to a fixed width, preserving manual line breaks.
     *
     * @param string $text The text to wrap.
     * @param int $width The width to wrap to.
     * @return array The wrapped lines.
     */
    protected function wrap(string $text, int $width): array
    {
        $text = preg_replace('/\r\n?/', "\n", $text);
        $output = [];
        foreach (explode("\n", $text) as $paragraph) {
            if ($paragraph === '') {
                $output[] = '';
                continue;
            }
            $lines = wordwrap($paragraph, $width, "\n", true);
            foreach (explode("\n", $lines) as $line) {
                $output[] = $line;
            }
        }

        return $output;
    }

    /**
     * Send pre-built receipt text to the printer.
     *
     * @param string $receiptText The receipt text to print.
     * @return bool True on success, false on failure.
     */
    public function printReceiptText(string $receiptText): bool
    {
        try {
            $connector = new UsbConnector($this->printerPath);
            $printer = new Printer($connector);

            $printer->setTextSize(1, 1);
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text($receiptText);

            $printer->cut();
            $printer->close();

            return true;
        } catch (Exception $e) {
            Log::error('Printer error: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Print a message from the database.
     *
     * @param int $messageId The message ID to print.
     * @return bool True on success, false on failure.
     */
    public function printMessage(int $messageId): bool
    {
        $messages = TableRegistry::getTableLocator()->get('Messages');
        $message = $messages->get($messageId);

        $receiptText = $this->buildReceipt($message);

        if (!$this->printReceiptText($receiptText)) {
            // Update message status to failed
            $message->status = 'failed';
            $message->error_message = 'Failed to send receipt to printer.';
            $messages->save($message);

            return false;
        }

        try {
            // Update message status to printed
            $message->status = 'printed';
            $message->printed_at = new Chronos();
            $messages->save($message);

            return true;
        } catch (Exception $e) {
            // Update message status to failed
            $message->status = 'failed';
            $message->error_message = $e->getMessage();
            $messages->save($message);

            return false;
        }
    }

    /**
     * Print a message directly (not from database).
     *
     * @param string $content The message content.
     * @param string|null $ip The IP address of the submitter.
     * @param string|null $txNumber The transaction number.
     * @return bool True on success, false on failure.
     */
    public function printDirect(string $content, ?string $ip = null, ?string $txNumber = null): bool
    {
        $receiptText = $this->buildReceiptFromString($content, $ip, $txNumber);

        return $this->printReceiptText($receiptText);
    }
}
