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

namespace Ramsey\Uuid\Rfc4122\Factories;

use DateTimeInterface;
use Ramsey\Uuid\BinaryUtils;
use Ramsey\Uuid\Converter\Number\GenericNumberConverter;
use Ramsey\Uuid\Converter\NumberConverterInterface;
use Ramsey\Uuid\Converter\Time\PhpTimeConverter;
use Ramsey\Uuid\Converter\TimeConverterInterface;
use Ramsey\Uuid\Generator\DefaultTimeGenerator;
use Ramsey\Uuid\Generator\TimeGeneratorFactory;
use Ramsey\Uuid\Generator\TimeGeneratorInterface;
use Ramsey\Uuid\Math\BrickMathCalculator;
use Ramsey\Uuid\Provider\Node\FallbackNodeProvider;
use Ramsey\Uuid\Provider\Node\RandomNodeProvider;
use Ramsey\Uuid\Provider\Node\SystemNodeProvider;
use Ramsey\Uuid\Provider\NodeProviderInterface;
use Ramsey\Uuid\Provider\Time\FixedTimeProvider;
use Ramsey\Uuid\Provider\Time\SystemTimeProvider;
use Ramsey\Uuid\Rfc4122\UuidV1;
use Ramsey\Uuid\Rfc4122\Version;
use Ramsey\Uuid\TimeBasedUuidFactoryInterface;
use Ramsey\Uuid\Type\Hexadecimal;
use Ramsey\Uuid\Type\Time;
use Ramsey\Uuid\Uuid;

use function str_pad;

use const STR_PAD_LEFT;

/**
 * A factory for creating RFC 4122, version 1, time-based UUID instances
 */
class UuidV1Factory implements TimeBasedUuidFactoryInterface
{
    use DeprecatedFactoryMethodsTrait;

    private readonly NodeProviderInterface $nodeProvider;
    private readonly NumberConverterInterface $numberConverter;
    private readonly TimeConverterInterface $timeConverter;
    private readonly TimeGeneratorInterface $timeGenerator;

    public function __construct(
        ?TimeGeneratorInterface $timeGenerator = null,
        ?NodeProviderInterface $nodeProvider = null,
    ) {
        $this->timeConverter = new PhpTimeConverter();
        $this->numberConverter = new GenericNumberConverter(new BrickMathCalculator());

        $this->nodeProvider = $nodeProvider ?? new FallbackNodeProvider([
            new SystemNodeProvider(),
            new RandomNodeProvider(),
        ]);

        $this->timeGenerator = $timeGenerator ?? (
                new TimeGeneratorFactory($this->nodeProvider, $this->timeConverter, new SystemTimeProvider())
            )->getGenerator();
    }

    /**
     * @param Hexadecimal|positive-int|non-empty-string|null $node
     * @param int<0, 16383>|null $clockSeq
     */
    public function create(Hexadecimal | int | string | null $node = null, ?int $clockSeq = null): UuidV1
    {
        if ($node instanceof Hexadecimal) {
            $node = $node->toString();
        }

        $bytes = BinaryUtils::applyVersionAndVariant(
            $this->timeGenerator->generate($node, $clockSeq),
            Version::Time,
        );

        return $this->fromBytes($bytes);
    }

    /**
     * @psalm-mutation-free
     */
    public function fromBytes(string $bytes): UuidV1
    {
        return new UuidV1($bytes);
    }

    public function fromDateTime(
        DateTimeInterface $dateTime,
        ?Hexadecimal $node = null,
        ?int $clockSeq = null
    ): UuidV1 {
        $timeProvider = new FixedTimeProvider(
            new Time($dateTime->format('U'), $dateTime->format('u'))
        );

        $timeGenerator = new DefaultTimeGenerator(
            $this->nodeProvider,
            $this->timeConverter,
            $timeProvider
        );

        $bytes = BinaryUtils::applyVersionAndVariant(
            $timeGenerator->generate($node?->toString(), $clockSeq),
            Version::Time,
        );

        return $this->fromBytes($bytes);
    }

    /**
     * @psalm-mutation-free
     */
    public function fromInteger(string $integer): UuidV1
    {
        $hex = $this->numberConverter->toHex($integer);
        $hex = str_pad($hex, 32, '0', STR_PAD_LEFT);

        return $this->fromBytes($this->getBytes($hex));
    }

    /**
     * @psalm-mutation-free
     */
    public function fromString(string $uuid): UuidV1
    {
        return new UuidV1($uuid);
    }

    /**
     * Returns a byte string of the UUID
     *
     * @return non-empty-string
     *
     * @psalm-pure
     */
    private function getBytes(string $encodedUuid): string
    {
        $parsedUuid = str_replace(Uuid::REPLACE_TO_HEXADECIMAL, '', $encodedUuid);

        /** @var non-empty-string */
        return (string) hex2bin($parsedUuid);
    }
}
