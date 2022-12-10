<?php

namespace SixtyNine\Timesheep\Helper;

use PHPUnit\Framework\TestCase;
use SixtyNine\Timesheep\Helper\Numbers;

class NumbersTest extends TestCase
{
    public function testHumanFileSize(): void
    {
        $this->assertEquals('1B', Numbers::humanFileSize(1, 0));
        $this->assertEquals('1kB', Numbers::humanFileSize(1024, 0));
        $this->assertEquals('1MB', Numbers::humanFileSize(1048576, 0));
        $this->assertEquals('1GB', Numbers::humanFileSize(1073741824, 0));
        $this->assertEquals('1TB', Numbers::humanFileSize(1099511627776, 0));
        $this->assertEquals('1PB', Numbers::humanFileSize(1125899906842624, 0));
        $this->assertEquals('1EB', Numbers::humanFileSize(1152921504606846976, 0));
    }

    public function testPrecision(): void
    {
        $this->assertEquals('1.00kB', Numbers::humanFileSize(1024, 2));
        $this->assertEquals('1.21kB', Numbers::humanFileSize(1234, 2));
        $this->assertEquals('1.000MB', Numbers::humanFileSize(1048576, 3));
        $this->assertEquals('1.0GB', Numbers::humanFileSize(1073741824, 1));
        $this->assertEquals('1TB', Numbers::humanFileSize(1099511627776, 0));
    }
}
