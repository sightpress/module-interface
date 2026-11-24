<?php

declare(strict_types=1);

namespace SightPress\Module\Sms;

use Psr\Http\Message\ServerRequestInterface;
use SightPress\Module\Exception\SmsException;

/**
 * A provider that tells the site about delivery and replies by calling a webhook. Optional: implement it next to
 * {@see SmsProvider} when the provider has webhooks.
 *
 * Core answers the webhook address, refuses replays of the same report and records what it gets. Implemented by
 * modules.
 */
interface SmsWebhooks
{
    /**
     * Checks the request's signature with the site's connection and reads the reports in it.
     *
     * @return list<SmsReceipt|SmsOptChange>
     *
     * @throws SmsException with {@see SmsFailure::InvalidSignature} when the signature is missing or wrong
     */
    public function readWebhook(ServerRequestInterface $request, SmsConnection $connection): array;
}
