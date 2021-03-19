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

use Eluceo\iCal\Component;
use Eluceo\iCal\PropertyBag;

class Transition extends Component
{
    protected $transition = [];

    public function __construct($transition)
    {
        $this->transition = $transition;
    }

    public function getType()
    {
        return $this->transition['type'];
    }

    /**
    BEGIN:DAYLIGHT
    DTSTART:2020-03-29 T030000
    TZNAME:CEST
    TZOFFSETTO:+0200
    TZOFFSETFROM:+0100
    END:DAYLIGHT
     */
    public function buildPropertyBag()
    {
        $this->properties = new PropertyBag;
        $this->properties->set('DTSTART', [$this->transition['start_dt']]);
        $this->properties->set('TZNAME', [$this->transition['tz_name']]);
        $this->properties->set('TZOFFSETTO', [$this->transition['tz_offset_from']]);
        $this->properties->set('TZOFFSETFROM', [$this->transition['tz_offset_to']]);

        return $this->properties;
    }
}
