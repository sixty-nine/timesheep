<?php

namespace SixtyNine\Timesheep\Tests;

use PHPUnit\Framework\TestCase;
use SixtyNine\Timesheep\Helper\DateTime;
use SixtyNine\Timesheep\Helper\DateTime as DateTimeHelper;

class DateTimeTest extends TestCase
{
    public function testRoundTime()
    {
        $midnight = (new \DateTime(date('Y-m-d 23:59:59')))->modify('+1 second');
        var_dump($midnight);
        $helper = new DateTimeHelper();
        $now = new \DateTime();
        var_dump($now->format('r'));
        $helper->roundTime($now, 5);
        var_dump($now->format('r'));
    }
}
