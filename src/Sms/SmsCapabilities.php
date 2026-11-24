<?php

declare(strict_types=1);

namespace SightPress\Module\Sms;

use SightPress\Module\Internal\Guard;

/**
 * What a provider can do, so core only offers what works.
 */
final readonly class SmsCapabilities
{
    /** @var list<non-empty-string> */
    public array $countries;

    /**
     * @param bool         $lookup             {@see SmsProvider::lookup()} checks numbers; without it, core only
     *                                         checks their format
     * @param bool         $deliveryReceipts   the provider reports delivery through {@see SmsWebhooks}
     * @param bool         $optOutReplies      the provider reports STOP and START replies through {@see SmsWebhooks}
     * @param bool         $alphanumericSender messages can come from a name such as `SightPress` instead of a number
     * @param list<string> $countries          the countries it delivers to (ISO 3166-1 alpha-2); empty for every
     *                                         country it covers
     */
    public function __construct(
        public bool $lookup = false,
        public bool $deliveryReceipts = false,
        public bool $optOutReplies = false,
        public bool $alphanumericSender = false,
        array $countries = [],
    ) {
        $checked = [];
        foreach (Guard::listOfStrings($countries, 'Provider countries') as $country) {
            $checked[] = Guard::country($country);
        }
        Guard::unique($checked, 'Provider countries');
        $this->countries = $checked;
    }

    public function delivers(string $country): bool
    {
        return $this->countries === [] || in_array(strtoupper($country), $this->countries, true);
    }
}
