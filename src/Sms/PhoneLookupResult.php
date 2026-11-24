<?php

declare(strict_types=1);

namespace SightPress\Module\Sms;

use SightPress\Module\Internal\Guard;

/**
 * What a provider knows about a number before anything is sent to it.
 */
final readonly class PhoneLookupResult
{
    public ?string $country;

    /**
     * @param bool          $valid    the number exists and is in service, as far as the provider knows
     * @param PhoneLineType $lineType what kind of line it is
     * @param string|null   $country  its country (ISO 3166-1 alpha-2, for example `GB`), when known
     */
    public function __construct(
        public bool $valid,
        public PhoneLineType $lineType = PhoneLineType::Unknown,
        ?string $country = null,
    ) {
        $this->country = $country === null ? null : Guard::country($country);
    }

    /**
     * True for a valid mobile number: the only kind core sends codes to.
     */
    public function isMobile(): bool
    {
        return $this->valid && $this->lineType === PhoneLineType::Mobile;
    }
}
