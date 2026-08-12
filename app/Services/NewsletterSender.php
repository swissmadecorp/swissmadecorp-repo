<?php

namespace App\Services;

use App\Libs\MassMail as InventoryEmail;
use App\Mail\GMailer;
use App\Models\MailMass;
use App\Models\Newsletter;
use Illuminate\Support\Facades\Log;
use Throwable;

class NewsletterSender
{
    public function send(?int $campaignId = null): int
    {
        $campaign = $campaignId
            ? MailMass::findOrFail($campaignId)
            : MailMass::query()->where('is_active', true)->latest('updated_at')->first();

        $body = InventoryEmail::process([
            'category' => $campaign?->categoryIds() ?? [],
            'template' => $campaign?->content,
            'loadWithTemplate' => $campaign === null,
        ]);

        $subject = $campaign?->title ?: 'Swiss Made Corp. - Newsletter';
        $sent = 0;

        Newsletter::query()
            ->where('subscribed', 1)
            ->whereNotNull('email')
            ->eachById(function (Newsletter $newsletter) use ($body, $subject, &$sent) {
                try {
                    (new GMailer([
                        'template' => 'emails.html',
                        'from' => 'info@swissmadecorp.com',
                        'to' => $newsletter->email,
                        'subject' => $subject,
                        'body' => $body.$this->footer($newsletter->email),
                    ]))->send();

                    $sent++;
                } catch (Throwable $exception) {
                    $this->reportFailure($newsletter, $exception);
                }
            });

        return $sent;
    }

    private function reportFailure(Newsletter $newsletter, Throwable $exception): void
    {
        $message = sprintf(
            'Newsletter delivery failed for newsletter ID %d (%s): %s',
            $newsletter->id,
            $newsletter->email,
            $exception->getMessage(),
        );

        try {
            Log::error($message, [
                'newsletter_id' => $newsletter->id,
                'email' => $newsletter->email,
                'exception_class' => $exception::class,
                'exception_code' => $exception->getCode(),
            ]);
        } catch (Throwable) {
            // Keep processing recipients even when Laravel's log is not writable.
            error_log($message);
        }
    }

    private function footer(string $email): string
    {
        $unsubscribeUrl = url('/unsubscribe/'.rawurlencode($email));

        return '<div style="text-align:center;background-color:#444;color:#fff;padding:12px">'
            .'<p>Copyright &copy; '.date('Y').' Swiss Made Corp. All rights reserved.</p>'
            .'<p>NYC | 15 W 47th St | Room 503, 5th Floor | New York, NY 10036 | 212-840-8463</p>'
            .'<p>Want to unsubscribe? <a style="color:#8ab5ff;text-decoration:none" href="'
            .e($unsubscribeUrl).'">Click here</a>.</p></div>';
    }
}
