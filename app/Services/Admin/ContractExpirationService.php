<?php

namespace App\Services\Admin;

use App\Models\Admin\Contract;
use App\Models\NotificationLog;
use App\Services\MailService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ContractExpirationService
{
    protected $mailService;

    public function __construct(MailService $mailService)
    {
        $this->mailService = $mailService;
    }

    /**
     * Check and send notification for expired contracts on first login of the day
     *
     * @return array
     */
    public function checkAndSendExpiredContracts(): array
    {
        $today = Carbon::today()->toDateString();
        
        // Get expired contracts (expiration_date <= today) that haven't been notified yet
        $expiredContracts = Contract::whereNotNull('expiration_date')
            ->whereDate('expiration_date', '<=', $today)
            ->whereNull('notification_sent_at')
            ->get();

        if ($expiredContracts->isEmpty()) {
            return [
                'success' => true,
                'message' => 'Không có hợp đồng hết hạn hoặc đã gửi thông báo tất cả',
                'count' => 0,
            ];
        }

        // Prepare email content
        $contractsData = $expiredContracts->map(function ($contract) {
            return [
                'id' => $contract->id,
                'company_name' => $contract->company_name,
                'contract_number' => $contract->contract_number,
                'expiration_date' => $contract->expiration_date->format('d/m/Y'),
            ];
        })->toArray();

        $emailSent = $this->sendExpiredContractsEmail($contractsData);

        if ($emailSent) {
            // Mark contracts as notified
            $contractIds = $expiredContracts->pluck('id')->toArray();
            Contract::whereIn('id', $contractIds)
                ->update(['notification_sent_at' => now()]);
            
            // Log notification
            $this->logNotification($today, 'sent', 0, null, $contractsData);
            
            return [
                'success' => true,
                'message' => 'Đã gửi thông báo thành công',
                'count' => count($contractsData),
                'contracts' => $contractsData,
            ];
        } else {
            // Log failed notification
            $this->logNotification($today, 'failed', 1, 'Gửi email thất bại', $contractsData);
            
            return [
                'success' => false,
                'message' => 'Gửi email thất bại',
            ];
        }
    }

    /**
     * Send email for expired contracts
     *
     * @param array $contractsData
     * @return bool
     */
    protected function sendExpiredContractsEmail(array $contractsData): bool
    {
        try {
            // Get admin email from config or use default
            $adminEmail = config('mail.admin_email', config('mail.from.address'));
            
            $subject = 'Thông báo: Có ' . count($contractsData) . ' hợp đồng đã hết hạn';
            
            $body = $this->buildEmailBody($contractsData);

            return $this->mailService->send(
                $adminEmail,
                $subject,
                $body
            );
        } catch (\Exception $e) {
            Log::error('Failed to send expired contracts email', [
                'error' => $e->getMessage(),
                'contracts' => $contractsData,
            ]);
            
            return false;
        }
    }

    /**
     * Build email body HTML
     *
     * @param array $contractsData
     * @return string
     */
    protected function buildEmailBody(array $contractsData): string
    {
        $html = '<html><body style="font-family: Arial, sans-serif;">';
        $html .= '<h2>Thông báo hợp đồng hết hạn</h2>';
        $html .= '<p>Có <strong>' . count($contractsData) . '</strong> hợp đồng đã hết hạn:</p>';
        $html .= '<table border="1" cellpadding="10" cellspacing="0" style="border-collapse: collapse; width: 100%;">';
        $html .= '<thead><tr style="background-color: #f0f0f0;">';
        $html .= '<th>ID</th><th>Tên đơn vị</th><th>Số hợp đồng</th><th>Ngày hết hạn</th>';
        $html .= '</tr></thead><tbody>';
        
        foreach ($contractsData as $contract) {
            $html .= '<tr>';
            $html .= '<td>' . $contract['id'] . '</td>';
            $html .= '<td>' . htmlspecialchars($contract['company_name']) . '</td>';
            $html .= '<td>' . htmlspecialchars($contract['contract_number']) . '</td>';
            $html .= '<td>' . $contract['expiration_date'] . '</td>';
            $html .= '</tr>';
        }
        
        $html .= '</tbody></table>';
        $html .= '<p style="margin-top: 20px;">Vui lòng kiểm tra và xử lý các hợp đồng này.</p>';
        $html .= '</body></html>';
        
        return $html;
    }

    /**
     * Log notification status
     *
     * @param string $date
     * @param string $status
     * @param int $retryCount
     * @param string|null $errorMessage
     * @param array|null $contractsData
     * @return void
     */
    protected function logNotification(string $date, string $status, int $retryCount = 0, ?string $errorMessage = null, ?array $contractsData = null): void
    {
        NotificationLog::updateOrCreate(
            [
                'notification_date' => $date,
                'type' => 'contract_expiration',
            ],
            [
                'status' => $status,
                'retry_count' => $retryCount,
                'error_message' => $errorMessage,
                'data' => $contractsData ? json_encode($contractsData) : null,
            ]
        );
    }

    /**
     * Retry sending failed notifications
     *
     * @return array
     */
    public function retryFailedNotifications(): array
    {
        $today = Carbon::today()->toDateString();
        
        $failedLog = NotificationLog::where('notification_date', $today)
            ->where('type', 'contract_expiration')
            ->where('status', 'failed')
            ->first();

        if (!$failedLog) {
            return [
                'success' => true,
                'message' => 'Không có thông báo thất bại cần gửi lại',
            ];
        }

        // Get the contracts data from log (already cast as array)
        $contractsData = $failedLog->data;
        
        if (!$contractsData) {
            // Re-fetch expired contracts
            $expiredContracts = Contract::whereNotNull('expiration_date')
                ->whereDate('expiration_date', '<=', $today)
                ->get();

            $contractsData = $expiredContracts->map(function ($contract) {
                return [
                    'id' => $contract->id,
                    'company_name' => $contract->company_name,
                    'contract_number' => $contract->contract_number,
                    'expiration_date' => $contract->expiration_date->format('d/m/Y'),
                ];
            })->toArray();
        }

        $maxRetries = 2;
        if ($failedLog->retry_count >= $maxRetries) {
            return [
                'success' => false,
                'message' => 'Đã vượt quá số lần thử lại',
                'retry_count' => $failedLog->retry_count,
            ];
        }

        $emailSent = $this->sendExpiredContractsEmail($contractsData);

        if ($emailSent) {
            $failedLog->update([
                'status' => 'sent',
                'retry_count' => 0,
                'error_message' => null,
            ]);

            return [
                'success' => true,
                'message' => 'Đã gửi lại thông báo thành công',
                'count' => count($contractsData),
            ];
        } else {
            $failedLog->update([
                'status' => 'failed',
                'retry_count' => $failedLog->retry_count + 1,
                'error_message' => 'Gửi email thất bại',
            ]);

            return [
                'success' => false,
                'message' => 'Gửi lại email thất bại',
                'retry_count' => $failedLog->retry_count + 1,
            ];
        }
    }
}

