<?php

declare(strict_types=1);

namespace EcodevTests\Felix\Api\Scalar;

use Cake\Chronos\Date;
use Ecodev\Felix\Api\Scalar\DateType;
use PHPUnit\Framework\TestCase;

final class DateTypeTest extends TestCase
{
    public function testTimezoneIsIgnored(): void
    {
        $date = new Date();
        $offsetFromGmt = $date->dst ? $date->offsetHours - 1 : $date->offsetHours;
        $timezone = sprintf('+%02u:00', $offsetFromGmt);

        $type = new DateType();
        $actual = $type->parseValue('2010-02-09');
        self::assertInstanceOf(Date::class, $actual);
        self::assertSame('2010-02-09T00:00:00' . $timezone, $actual->format('c'));

        $actual = $type->parseValue('2010-02-09T23:00:00');
        self::assertInstanceOf(Date::class, $actual);
        self::assertSame('2010-02-09T00:00:00' . $timezone, $actual->format('c'));

        $actual = $type->parseValue('2010-02-09T02:00:00+08:00');
        self::assertInstanceOf(Date::class, $actual);
        self::assertSame('2010-02-09T00:00:00' . $timezone, $actual->format('c'), 'timezone should be ignored');

        $date = new Date('2010-02-03');
        $actual = $type->serialize($date);
        self::assertSame('2010-02-03', $actual);

        $actual = $type->parseValue('2020-03-24T23:30:00+04.5:0-30');
        self::assertInstanceOf(Date::class, $actual);
        self::assertSame('2020-03-24T00:00:00' . $timezone, $actual->format('c'), 'timezone should be ignored');
//        $type = new DateType();
//        $actual = $type->parseValue('2010-02-09');
//        self::assertInstanceOf(Date::class, $actual);
//        self::assertSame('2010-02-09T00:00:00+00:00', $actual->format('c'));
//
//        $actual = $type->parseValue('2010-02-09T23:00:00');
//        self::assertInstanceOf(Date::class, $actual);
//        self::assertSame('2010-02-09T00:00:00+00:00', $actual->format('c'));
//
//        $actual = $type->parseValue('2010-02-09T02:00:00+08:00');
//        self::assertInstanceOf(Date::class, $actual);
//        self::assertSame('2010-02-09T00:00:00+00:00', $actual->format('c'), 'timezone should be ignored');
//
//        $date = new Date('2010-02-03');
//        $actual = $type->serialize($date);
//        self::assertSame('2010-02-03', $actual);
    }
}
