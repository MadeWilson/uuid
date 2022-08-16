<?php

declare(strict_types=1);

namespace Ramsey\Uuid\Test\Rfc4122;

use DateTimeImmutable;
use Ramsey\Uuid\Exception\InvalidArgumentException;
use Ramsey\Uuid\Rfc4122\FieldsInterface;
use Ramsey\Uuid\Rfc4122\UuidV1;
use Ramsey\Uuid\Rfc4122\Version;
use Ramsey\Uuid\Test\TestCase;
use Ramsey\Uuid\Uuid;

class UuidV1Test extends TestCase
{
    /**
     * @dataProvider provideTestVersions
     */
    public function testConstructorThrowsExceptionWhenFieldsAreNotValidForType(Version $version): void
    {
        $fields = $this->mockery(FieldsInterface::class, [
            'getVersion' => $version,
            'getBytes' => 'foobar',
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'The value used to create a Ramsey\\Uuid\\Rfc4122\\UuidV1 must represent a version 1 UUID'
        );

        new UuidV1($fields);
    }

    /**
     * @return array<array{version: Version}>
     */
    public function provideTestVersions(): array
    {
        return [
            ['version' => Version::DceSecurity],
            ['version' => Version::HashMd5],
            ['version' => Version::Random],
            ['version' => Version::HashSha1],
            ['version' => Version::ReorderedTime],
            ['version' => Version::UnixTime],
            ['version' => Version::Custom],
        ];
    }

    /**
     * @param non-empty-string $uuid
     * @param non-empty-string $expected
     *
     * @dataProvider provideUuidV1WithOddMicroseconds
     */
    public function testGetDateTimeProperlyHandlesLongMicroseconds(string $uuid, string $expected): void
    {
        /** @var UuidV1 $object */
        $object = Uuid::fromString($uuid);

        $date = $object->getDateTime();

        $this->assertInstanceOf(DateTimeImmutable::class, $date);
        $this->assertSame($expected, $date->format('U.u'));
    }

    /**
     * @return array<array{uuid: non-empty-string, expected: non-empty-string}>
     */
    public function provideUuidV1WithOddMicroseconds(): array
    {
        return [
            [
                'uuid' => '14814000-1dd2-11b2-9669-00007ffffffe',
                'expected' => '1.677722',
            ],
            [
                'uuid' => '13714000-1dd2-11b2-9669-00007ffffffe',
                'expected' => '0.104858',
            ],
            [
                'uuid' => '13713000-1dd2-11b2-9669-00007ffffffe',
                'expected' => '0.105267',
            ],
            [
                'uuid' => '12e8a980-1dd2-11b2-8d4f-acde48001122',
                'expected' => '-1.000000',
            ],
        ];
    }
}
