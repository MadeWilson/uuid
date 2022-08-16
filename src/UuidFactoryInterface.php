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

namespace Ramsey\Uuid;

/**
 * UuidFactoryInterface defines common functionality all `UuidFactory` instances
 * must implement
 */
interface UuidFactoryInterface extends DeprecatedUuidFactoryInterface
{
    /**
     * Creates a new instance of a UUID
     *
     * @return UuidInterface A UuidInterface instance created according to the
     *     rules of the factory
     */
    public function create(): UuidInterface;

    /**
     * Creates a UUID from a byte string
     *
     * @param non-empty-string $bytes A binary string
     *
     * @return UuidInterface A UuidInterface instance created from a binary
     *     string representation
     *
     * @psalm-mutation-free
     */
    public function fromBytes(string $bytes): UuidInterface;

    /**
     * Creates a UUID from a 128-bit integer string
     *
     * @param numeric-string $integer String representation of 128-bit integer
     *
     * @return UuidInterface A UuidInterface instance created from the string
     *     representation of a 128-bit integer
     *
     * @psalm-mutation-free
     */
    public function fromInteger(string $integer): UuidInterface;

    /**
     * Creates a UUID from the string standard representation
     *
     * @param non-empty-string $uuid A hexadecimal string
     *
     * @return UuidInterface A UuidInterface instance created from a hexadecimal
     *     string representation
     *
     * @psalm-mutation-free
     */
    public function fromString(string $uuid): UuidInterface;
}
