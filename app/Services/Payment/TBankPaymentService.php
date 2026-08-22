<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;

class TBankPaymentService
{
    protected string $terminalKey;
    protected string $secretKey;
    protected string $apiUrl;

    public function __construct()
    {
        $this->terminalKey = config('services.tbank.terminal_key');
        $this->secretKey = config('services.tbank.secret_key');
        $this->apiUrl = config('services.tbank.payment_url', 'https://rest-api-test.tinkoff.ru/api/v2');
    }

    /**
     * Инициализация платежа
     */
    public function initiatePayment(array $paymentData): array
    {
        $postData = [
            'TerminalKey' => $this->terminalKey,
            'Amount' => $paymentData['amount'], // сумма в копейках
            'OrderId' => $paymentData['order_id'],
            'Description' => $paymentData['description'] ?? 'Оплата услуг Нейро-юрист',
            'PaymentType' => $paymentData['payment_type'] ?? 'O',
            'PayType' => $paymentData['pay_type'] ?? 'O',
            'IP' => $paymentData['ip'] ?? request()->ip(),
        ];

        $signature = $this->generateSignature($postData);
        $postData['Token'] = $signature;

        $response = Http::asJson()->post("{$this->apiUrl}/Init", $postData);

        if ($response->failed()) {
            throw new \Exception('Ошибка при инициализации платежа: ' . $response->body());
        }

        $data = $response->json();

        if ($data['Status'] !== 'OK') {
            throw new \Exception('Платеж не инициирован: ' . ($data['Message'] ?? 'Неизвестная ошибка'));
        }

        return [
            'payment_id' => $data['PaymentId'] ?? null,
            'payment_url' => $data['PaymentURL'] ?? null,
            'status' => $data['Status'],
        ];
    }

    /**
     * Проверка статуса платежа
     */
    public function checkPaymentStatus(string $paymentId): array
    {
        $postData = [
            'TerminalKey' => $this->terminalKey,
            'PaymentId' => $paymentId,
        ];

        $signature = $this->generateSignature($postData);
        $postData['Token'] = $signature;

        $response = Http::asJson()->post("{$this->apiUrl}/GetState", $postData);

        if ($response->failed()) {
            throw new \Exception('Ошибка при проверке статуса платежа: ' . $response->body());
        }

        $data = $response->json();

        return [
            'status' => $data['Status'] ?? 'UNKNOWN',
            'payment_id' => $data['PaymentId'] ?? null,
            'order_id' => $data['OrderId'] ?? null,
            'amount' => $data['Amount'] ?? null,
        ];
    }

    /**
     * Генерация подписи для запроса
     */
    protected function generateSignature(array $data): string
    {
        $values = [];
        
        foreach ($data as $key => $value) {
            $values[] = $value;
        }

        $concatenated = implode('', $values);
        $signature = hash('sha256', $this->secretKey . $concatenated);

        return $signature;
    }

    /**
     * Возврат платежа (Refund)
     */
    public function refundPayment(string $paymentId, int $amount): array
    {
        $postData = [
            'TerminalKey' => $this->terminalKey,
            'PaymentId' => $paymentId,
            'Amount' => $amount,
        ];

        $signature = $this->generateSignature($postData);
        $postData['Token'] = $signature;

        $response = Http::asJson()->post("{$this->apiUrl}/Cancel", $postData);

        if ($response->failed()) {
            throw new \Exception('Ошибка при возврате платежа: ' . $response->body());
        }

        $data = $response->json();

        return [
            'status' => $data['Status'] ?? 'ERROR',
            'payment_id' => $data['PaymentId'] ?? null,
        ];
    }
}
