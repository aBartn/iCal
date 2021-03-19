<?php

/*
 * This file is part of the eluceo/iCal package.
 *
 * (c) Markus Poerschke <markus@eluceo.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eluceo\iCal\Component;

use DateInterval;
use DateTimeImmutable;
use DateTimeZone;
use Eluceo\iCal\Component;
use Eluceo\iCal\PropertyBag;

/**
 * Implementation of the TIMEZONE component
 */
class Timezone extends Component
{
    /**
     * @var string
     */
    protected array $transitions = [];
    protected $timezone;

    public function __construct($timezone)
    {
        $this->timezone = $timezone;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return 'VTIMEZONE';
    }

    public function defineDaylightSaving(string $beginTimestamp, string $endTimestamp)
    {
        $phpDateTimeZone = new DateTimeZone($this->timezone);
        $transitions = $phpDateTimeZone->getTransitions($beginTimestamp, $endTimestamp);

        /** @var array{ts: int, time: string, offset: int, isdst: bool, abbr: string} $transitionArray */
        foreach ($transitions as $transitionArray) {
            $fromDateTime = DateTimeImmutable::createFromFormat(DateTimeImmutable::ISO8601, $transitionArray['time']);
            $localFromDateTime = $fromDateTime->setTimezone($phpDateTimeZone);

            $this->transitions[] = new Transition([
                'type' => $transitionArray['isdst'] ? 'DAYLIGHT' : 'STANDARD',
                'start_dt' => str_replace('C', 'T', $localFromDateTime->format('YmdCHis')),
                'tz_name' => $transitionArray['abbr'], // abbreviation
                'tz_offset_from' => "+" . str_pad($phpDateTimeZone->getOffset($fromDateTime->sub(new DateInterval('PT1S'))) / 36, 4, "0", STR_PAD_LEFT),
                'tz_offset_to' => "+" . str_pad($transitionArray['offset'] / 36, 4, "0", STR_PAD_LEFT),
            ]);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function buildPropertyBag()
    {
        $this->properties = new PropertyBag;

        $this->properties->set('TZID', $this->timezone);
        $this->properties->set('X-LIC-LOCATION', $this->timezone);
        foreach ($this->transitions as $transition) {
            $this->addComponent($transition);
        }

        return $this->properties;
    }
}
