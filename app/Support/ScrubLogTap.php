<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Log\Logger;
use Monolog\LogRecord;

/** Tap kanal log: menyaring pesan dan konteks sebelum ditulis (didaftarkan di config/logging.php). */
final class ScrubLogTap
{
    public function __invoke(Logger $logger): void
    {
        $monolog = $logger->getLogger();
        if (! method_exists($monolog, 'pushProcessor')) {
            return;
        }
        $monolog->pushProcessor(function (LogRecord $record): LogRecord {
            return $record->with(
                message: LogScrubber::scrubString($record->message),
                context: LogScrubber::scrubArray($record->context),
            );
        });
    }
}
