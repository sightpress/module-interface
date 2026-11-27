<?php

declare(strict_types=1);

namespace SightPress\Module\Privacy;

use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Internal\Guard;
use SightPress\Module\Text\Message;

/**
 * A third party that gets personal data from the site while a feature is configured: an email, SMS, storage or AI
 * provider (since 0.6). A {@see ProcessorSource} returns it only while the feature is set up. The Privacy Policy and
 * the record of processing name it, countries outside the UK and EEA show up as international transfers, and core's
 * outgoing HTTP client reaches only the hosts it lists.
 */
final readonly class ProcessorDeclaration
{
    /** @var non-empty-string */
    public string $id;

    /** @var non-empty-string */
    public string $name;

    /** @var non-empty-list<non-empty-string> */
    public array $countries;

    /** @var list<non-empty-string> */
    public array $hosts;

    /** @var non-empty-string|null */
    public ?string $privacyPolicyUrl;

    /**
     * @param string       $id               unique dotted name, for example `acme_sms.twilio`
     * @param string       $name             who it is, for example "Twilio Inc."
     * @param Message      $dataSent         what is sent to them, in plain words
     * @param list<string> $countries        where they process it, as ISO 3166-1 alpha-2 codes (`GB`, `IE`, `US`)
     * @param list<string> $hosts            host names the module calls (`api.twilio.com`, or `*.example.org` for every
     *                                       subdomain); empty when the site doesn't call them (such as a CDN)
     * @param string|null  $privacyPolicyUrl their privacy notice (https)
     * @param Message|null $safeguards       how transfers outside the UK and EEA are protected, for example "UK IDTA and
     *                                       EU standard contractual clauses"
     */
    public function __construct(
        string $id,
        string $name,
        public Message $dataSent,
        array $countries,
        array $hosts = [],
        ?string $privacyPolicyUrl = null,
        public ?Message $safeguards = null,
    ) {
        $this->id = Guard::matches($id, Guard::DOTTED_NAME, 'Processor id', 'use a dotted name such as "acme_sms.twilio"');
        $this->name = Guard::notBlank($name, 'Processor name');

        $checked = array_map(Guard::country(...), Guard::listOfStrings($countries, 'Processor countries'));
        if ($checked === []) {
            throw new InvalidValueException(sprintf('Processor "%s" needs at least one country where it processes data.', $id));
        }
        Guard::unique($checked, 'Processor countries');
        $this->countries = $checked;

        $hostList = [];
        foreach (Guard::listOfStrings($hosts, 'Processor hosts') as $host) {
            $hostList[] = Guard::matches(
                strtolower($host),
                '/^(?:\*\.)?(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}$/',
                'Processor host',
                'use a host name such as "api.example.org" or "*.example.org", without a scheme or path',
            );
        }
        Guard::unique($hostList, 'Processor hosts');
        $this->hosts = $hostList;

        $this->privacyPolicyUrl = $privacyPolicyUrl === null ? null : Guard::matches(
            $privacyPolicyUrl,
            '#^https://[^\s/?\#]+(?:[/?\#]\S*)?$#',
            'Processor privacy policy address',
            'use an https address',
        );
    }
}
