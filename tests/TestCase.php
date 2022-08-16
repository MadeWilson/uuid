<?php

declare(strict_types=1);

namespace Ramsey\Uuid\Test;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase as PhpUnitTestCase;

use function current;
use function pack;
use function unpack;

abstract class TestCase extends PhpUnitTestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * Configures and returns a mock object
     *
     * @param class-string<T> $class
     * @param mixed ...$arguments
     *
     * @return T & MockInterface
     *
     * @template T
     *
     * phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function mockery(string $class, ...$arguments)
    {
        /** @var T & MockInterface $mock */
        $mock = Mockery::mock($class, ...$arguments);

        return $mock;
    }

    public static function isLittleEndianSystem(): bool
    {
        /** @var array $unpacked */
        $unpacked = unpack('v', pack('S', 0x00FF));

        return current($unpacked) === 0x00FF;
    }
}
