<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TBankPaymentService
{
    protected ?string $terminalKey = null;
    protected ?string $password = null;
    protected ?string $apiUrl = null;

    public function __construct()
    {
        $this->terminalKey = (string) config('services.tbank.terminal_key', '');
        $this->password = (string) config('services.tbank.password', '');
        $this->apiUrl = (string) config('services.tbank.payment_url', 'https://securepay.tinkoff.ru/v2/');
    }

    public function initiatePayment(array $paymentData): array
    {
        if (empty($this->terminalKey) || empty($this->password)) {
            throw new \Exception('Т-Банк не настроен: проверьте .env');
        }

        $postData = [
            'TerminalKey' => $this->terminalKey,
            'Amount' => (int) $paymentData['amount'],
            'OrderId' => $paymentData['order_id'],
            'Description' => $paymentData['description'] ?? 'Оплата услуг Нейро-юрист',
            'DATA' => [
                'Email' => $paymentData['email'] ?? '',
                'UserId' => (string) ($paymentData['user_id'] ?? ''),
            ],
        ];

        $postData['Token'] = $this->generateSignature($postData);

        Log::info('Tinkoff Init request', $postData);

        $response = Http::timeout(30)
            ->withoutVerifying()
            ->asJson()
            ->post($this->apiUrl . 'Init', $postData);

        $data = $response->json();

        Log::info('Tinkoff Init response', $data ?? []);

        if ($response->failed()) {
            throw new \Exception('Ошибка при инициализации платежа: ' . $response->body());
        }

        if (!($data['Success'] ?? false)) {
            throw new \Exception('Платёж не инициирован: ' . ($data['Message'] ?? 'Неизвестная ошибка') . ' | ErrorCode: ' . ($data['ErrorCode'] ?? 'N/A'));
        }

        return [
            'payment_id' => $data['PaymentId'] ?? null,
            'payment_url' => $data['PaymentURL'] ?? null,
            'status' => $data['Status'] ?? 'NEW',
        ];
    }

    public function checkPaymentStatus(string $paymentId): array
    {
        $postData = [
            'TerminalKey' => $this->terminalKey,
            'PaymentId' => $paymentId,
        ];

        $postData['Token'] = $this->generateSignature($postData);

        $response = Http::timeout(30)
            ->withoutVerifying()
            ->asJson()
            ->post($this->apiUrl . 'GetState', $postData);

        if ($response->failed()) {
            throw new \Exception('Ошибка при проверке статуса: ' . $response->body());
        }

        return $response->json() ?? ['Status' => 'UNKNOWN'];
    }

    /**
     * Правильный алгоритм подписи Т-Банка v2:
     * 1. Добавить Password как параметр
     * 2. Убрать Token
     * 3. Убрать DATA (не участвует в подписи)
     * 4. Отсортировать по ключам
     * 5. Склеить значения
     * 6. SHA256
     */
    protected function generateSignature(array $data): string
    {
        unset($data['Token']);
        unset($data['DATA']);

        $data['Password'] = $this->password;

        ksort($data);

        $concatenated = '';
        foreach ($data as $value) {
            if (is_bool($value)) {
                $concatenated .= $value ? 'true' : 'false';
            } else {
                $concatenated .= $value;
            }
        }

        return hash('sha256', $concatenated);
    }

    public function refundPayment(string $paymentId, int $amount): array
    {
        $postData = [
            'TerminalKey' => $this->terminalKey,
            'PaymentId' => $paymentId,
            'Amount' => $amount,
        ];

        $postData['Token'] = $this->generateSignature($postData);

        $response = Http::timeout(30)
            ->withoutVerifying()
            ->asJson()
            ->post($this->apiUrl . 'Cancel', $postData);

        if ($response->failed()) {
            throw new \Exception('Ошибка при возврате: ' . $response->body());
        }

        return $response->json() ?? ['Status' => 'ERROR'];
    }
}
