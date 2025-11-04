<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;
use Exception;
use Log;

class MailService
{
    /**
     * Send email to single recipient
     *
     * @param string $to
     * @param string $subject
     * @param string $body
     * @param string|null $fromEmail
     * @param string|null $fromName
     * @param array $attachments
     * @return bool
     */
    public function send(string $to, string $subject, string $body, ?string $fromEmail = null, ?string $fromName = null, array $attachments = []): bool
    {
        try {
            $fromEmail = $fromEmail ?? config('mail.from.address');
            $fromName = $fromName ?? config('mail.from.name');

            Mail::send([], [], function (Message $message) use ($to, $subject, $body, $fromEmail, $fromName, $attachments) {
                $message->to($to)
                    ->subject($subject)
                    ->from($fromEmail, $fromName)
                    ->html($body);

                // Add attachments if any
                foreach ($attachments as $attachment) {
                    if (is_array($attachment)) {
                        $message->attach($attachment['path'], [
                            'as' => $attachment['as'] ?? null,
                            'mime' => $attachment['mime'] ?? null,
                        ]);
                    } else {
                        $message->attach($attachment);
                    }
                }
            });

            Log::info('Email sent successfully', [
                'to' => $to,
                'subject' => $subject,
            ]);

            return true;
        } catch (Exception $e) {
            Log::error('Failed to send email', [
                'to' => $to,
                'subject' => $subject,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Send email to multiple recipients
     *
     * @param array $to
     * @param string $subject
     * @param string $body
     * @param string|null $fromEmail
     * @param string|null $fromName
     * @param array $attachments
     * @return bool
     */
    public function sendMultiple(array $to, string $subject, string $body, ?string $fromEmail = null, ?string $fromName = null, array $attachments = []): bool
    {
        try {
            $fromEmail = $fromEmail ?? config('mail.from.address');
            $fromName = $fromName ?? config('mail.from.name');

            Mail::send([], [], function (Message $message) use ($to, $subject, $body, $fromEmail, $fromName, $attachments) {
                $message->to($to)
                    ->subject($subject)
                    ->from($fromEmail, $fromName)
                    ->html($body);

                // Add attachments if any
                foreach ($attachments as $attachment) {
                    if (is_array($attachment)) {
                        $message->attach($attachment['path'], [
                            'as' => $attachment['as'] ?? null,
                            'mime' => $attachment['mime'] ?? null,
                        ]);
                    } else {
                        $message->attach($attachment);
                    }
                }
            });

            Log::info('Email sent successfully to multiple recipients', [
                'to' => $to,
                'subject' => $subject,
            ]);

            return true;
        } catch (Exception $e) {
            Log::error('Failed to send email to multiple recipients', [
                'to' => $to,
                'subject' => $subject,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Send email using a view template
     *
     * @param string $to
     * @param string $subject
     * @param string $view
     * @param array $data
     * @param string|null $fromEmail
     * @param string|null $fromName
     * @param array $attachments
     * @return bool
     */
    public function sendWithView(string $to, string $subject, string $view, array $data = [], ?string $fromEmail = null, ?string $fromName = null, array $attachments = []): bool
    {
        try {
            $fromEmail = $fromEmail ?? config('mail.from.address');
            $fromName = $fromName ?? config('mail.from.name');

            Mail::send($view, $data, function (Message $message) use ($to, $subject, $fromEmail, $fromName, $attachments) {
                $message->to($to)
                    ->subject($subject)
                    ->from($fromEmail, $fromName);

                // Add attachments if any
                foreach ($attachments as $attachment) {
                    if (is_array($attachment)) {
                        $message->attach($attachment['path'], [
                            'as' => $attachment['as'] ?? null,
                            'mime' => $attachment['mime'] ?? null,
                        ]);
                    } else {
                        $message->attach($attachment);
                    }
                }
            });

            Log::info('Email sent successfully with view', [
                'to' => $to,
                'subject' => $subject,
                'view' => $view,
            ]);

            return true;
        } catch (Exception $e) {
            Log::error('Failed to send email with view', [
                'to' => $to,
                'subject' => $subject,
                'view' => $view,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}

