<?php

/**
 * This file is part of the ramsey/uuid library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) Ben Ramsey <ben@benramsey.com>
 * @license http://opensource.org/licenses/MIT MIT
 */

declare(strict_types=1);

namespace Ramsey\Uuid\Rfc4122;

use DateTimeImmutable;
use DateTimeInterface;
use Ramsey\Uuid\Converter\Number\GenericNumberConverter;
use Ramsey\Uuid\Converter\NumberConverterInterface;
use Ramsey\Uuid\Converter\Time\PhpTimeConverter;
use Ramsey\Uuid\Converter\TimeConverterInterface;
use Ramsey\Uuid\Exception\DateTimeException;
use Ramsey\Uuid\Exception\InvalidArgumentException;
use Ramsey\Uuid\Math\BrickMathCalculator;
use Ramsey\Uuid\Rfc4122\FieldsInterface as Rfc4122FieldsInterface;
use Ramsey\Uuid\Rfc4122\UuidInterface as Rfc4122UuidInterface;
use Ramsey\Uuid\TimeBasedUuidInterface;
use Ramsey\Uuid\Type\Hexadecimal;
use Ramsey\Uuid\Type\Integer as IntegerObject;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Throwable;
use ValueError;

use function sprintf;
use function str_pad;

use const STR_PAD_LEFT;

/**
 * Time-based, or version 1, UUIDs include timestamp, clock sequence, and node
 * values that are combined into a 128-bit unsigned integer
 *
 * @psalm-immutable
 */
final class UuidV1 implements Rfc4122UuidInterface, TimeBasedUuidInterface
{
    private readonly NumberConverterInterface $numberConverter;
    private readonly TimeConverterInterface $timeConverter;
    private ?Rfc4122FieldsInterface $lazyFields = null;

    /**
     * @var non-empty-string|null
     */
    private ?string $lazyUuid = null;

    /**
     * @var non-empty-string|null
     */
    private ?string $lazyBytes = null;

    /**
     * Creates a version 1 (time-based) UUID
     *
     * @param Rfc4122FieldsInterface|non-empty-string $value The fields, bytes,
     *     or string from which to construct a UUID
     */
    public function __construct(Rfc4122FieldsInterface | string $value)
    {
        $this->numberConverter = new GenericNumberConverter(new BrickMathCalculator());
        $this->timeConverter = new PhpTimeConverter();

        $this->setValueForVersion($value, Version::Time);
    }

    /**
     * @return array{bytes: string}
     */
    public function __serialize(): array
    {
        return ['bytes' => $this->getBytes()];
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * @inheritDoc
     */
    public function __unserialize(array $data): void
    {
        if (!isset($data['bytes'])) {
            throw new ValueError(sprintf('%s(): Argument #1 ($data) is invalid', __METHOD__));
        }

        assert(is_string($data['bytes']) && $data['bytes'] !== '');

        $this->__construct($data['bytes']);
    }

    public function compareTo(UuidInterface $other): int
    {
        $compare = strcmp($this->toString(), $other->toString());

        if ($compare < 0) {
            return -1;
        }

        if ($compare > 0) {
            return 1;
        }

        return 0;
    }

    public function equals(?object $other): bool
    {
        if (!$other instanceof UuidInterface) {
            return false;
        }

        return $this->compareTo($other) === 0;
    }

    /**
     * @return non-empty-string
     */
    public function getBytes(): string
    {
        if ($this->lazyBytes === null) {
            if ($this->lazyFields !== null) {
                $this->lazyBytes = $this->lazyFields->getBytes();
            } elseif ($this->lazyUuid !== null) {
                /** @var non-empty-string $uuid */
                $uuid = hex2bin(str_replace(Uuid::REPLACE_TO_HEXADECIMAL, '', $this->lazyUuid));
                $this->lazyBytes = $uuid;
            }
        }

        assert($this->lazyBytes !== null);

        return $this->lazyBytes;
    }

    public function getDateTime(): DateTimeInterface
    {
        $time = $this->timeConverter->convertTime($this->getFields()->getTimestamp());

        try {
            return new DateTimeImmutable(
                '@'
                . $time->getSeconds()->toString()
                . '.'
                . str_pad($time->getMicroseconds()->toString(), 6, '0', STR_PAD_LEFT)
            );
        // @codeCoverageIgnoreStart
        } catch (Throwable $e) {
            throw new DateTimeException($e->getMessage(), (int) $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd
    }

    public function getFields(): FieldsInterface
    {
        if ($this->lazyFields === null) {
            $this->lazyFields = new Fields($this->getBytes());
        }

        assert($this->lazyFields !== null);

        return $this->lazyFields;
    }

    public function getHex(): Hexadecimal
    {
        /** @var non-empty-string $hex */
        $hex = str_replace(Uuid::REPLACE_TO_HEXADECIMAL, '', $this->toString());

        return new Hexadecimal($hex);
    }

    public function getInteger(): IntegerObject
    {
        return new IntegerObject($this->numberConverter->fromHex($this->getHex()->toString()));
    }

    public function getUrn(): string
    {
        return 'urn:uuid:' . $this->toString();
    }

    /**
     * Converts the UUID to a string for JSON serialization
     */
    public function jsonSerialize(): string
    {
        return $this->toString();
    }

    /**
     * @return non-empty-string
     */
    public function toString(): string
    {
        if ($this->lazyUuid === null) {
            $fields = $this->getFields();

            /** @var non-empty-string $uuid */
            $uuid = sprintf(
                '%s-%s-%s-%s%s-%s',
                $fields->getTimeLow()->toString(),
                $fields->getTimeMid()->toString(),
                $fields->getTimeHiAndVersion()->toString(),
                $fields->getClockSeqHiAndReserved()->toString(),
                $fields->getClockSeqLow()->toString(),
                $fields->getNode()->toString(),
            );

            $this->lazyUuid = $uuid;
        }

        assert($this->lazyUuid !== null);

        return $this->lazyUuid;
    }

    /**
     * @param FieldsInterface|non-empty-string $value
     */
    private function setValueForVersion(Rfc4122FieldsInterface | string $value, Version $expectedVersion): void
    {
        $validator = new Validator();
        $version = null;

        if (is_string($value) && strlen($value) === 16) {
            $this->lazyBytes = $value;
            $version = Version::tryFrom(ord(substr($this->lazyBytes, 6, 1)) >> 4);
        } elseif (is_string($value) && $validator->validate($value)) {
            $uuid = str_replace(Uuid::REPLACE_TO_STRING_STANDARD, '', $value);
            assert($uuid !== '');
            $this->lazyUuid = $uuid;
            $version = Version::tryFrom((int) substr($this->lazyUuid, 14, 1));
        } elseif ($value instanceof Rfc4122FieldsInterface) {
            $this->lazyFields = $value;
            $this->lazyBytes = $value->getBytes();
            $version = $this->lazyFields->getVersion();
        }

        if ($version !== $expectedVersion) {
            throw new InvalidArgumentException(sprintf(
                'The value used to create a %s must represent a version %d UUID',
                $this::class,
                $expectedVersion->value,
            ));
        }
    }
}
