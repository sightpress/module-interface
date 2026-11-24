<?php

declare(strict_types=1);

namespace SightPress\Module\Sms;

use SightPress\Module\Exception\SmsException;

/**
 * A text message (SMS) service a site can send through. Registered with
 * {@see \SightPress\Module\Registrar\SmsRegistrar::addProvider()} and built from the site's container.
 *
 * Make HTTP calls with core's shared HTTP client (see the module reference), which refuses private addresses and
 * redirects, never with a client of your own. Implemented by modules.
 */
interface SmsProvider
{
    /** Lower-case id, for example `twilio`. */
    public function id(): string;

    /** Its name, for example `Twilio`. */
    public function label(): string;

    /**
     * For site admins, in plain language: who runs it, where messages are processed and how long the provider
     * keeps numbers and texts.
     */
    public function description(): string;

    public function capabilities(): SmsCapabilities;

    /**
     * Sends one message.
     *
     * @throws SmsException
     */
    public function send(SmsMessage $message, SmsConnection $connection): SmsResult;

    /**
     * Checks a number before anything is sent to it. Throws {@see SmsFailure::Unsupported} when
     * {@see SmsCapabilities::$lookup} is false.
     *
     * @throws SmsException
     */
    public function lookup(PhoneNumber $number, SmsConnection $connection): PhoneLookupResult;
}
