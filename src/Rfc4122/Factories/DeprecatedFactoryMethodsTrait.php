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

use Ramsey\Uuid\Exception\UnsupportedOperationException;
use Ramsey\Uuid\Type\Hexadecimal;
use Ramsey\Uuid\Type\Integer as IntegerObject;
use Ramsey\Uuid\UuidInterface;
use Ramsey\Uuid\Validator\ValidatorInterface;

/**
 * @internal
 *
 * @psalm-immutable
 */
trait DeprecatedFactoryMethodsTrait
{
    public function getValidator(): ValidatorInterface
    {
        throw new UnsupportedOperationException(
            __METHOD__ . ' is deprecated and will go away in ramsey/uuid '
            . 'version 5; use the validator classes directly',
        );
    }

    public function uuid1(Hexadecimal | int | string | null $node = null, ?int $clockSeq = null): UuidInterface
    {
        throw new UnsupportedOperationException(
            __METHOD__ . ' is deprecated and will go away in ramsey/uuid '
            . 'version 5; use the create() method instead',
        );
    }

    public function uuid2(
        int $localDomain,
        ?IntegerObject $localIdentifier = null,
        ?Hexadecimal $node = null,
        ?int $clockSeq = null
    ): UuidInterface {
        throw new UnsupportedOperationException(
            __METHOD__ . ' is deprecated and will go away in ramsey/uuid '
            . 'version 5; use the create() method instead',
        );
    }

    public function uuid3(UuidInterface | string $ns, string $name): UuidInterface
    {
        throw new UnsupportedOperationException(
            __METHOD__ . ' is deprecated and will go away in ramsey/uuid '
            . 'version 5; use the create() method instead',
        );
    }

    public function uuid4(): UuidInterface
    {
        throw new UnsupportedOperationException(
            __METHOD__ . ' is deprecated and will go away in ramsey/uuid '
            . 'version 5; use the create() method instead',
        );
    }

    public function uuid5(UuidInterface | string $ns, string $name): UuidInterface
    {
        throw new UnsupportedOperationException(
            __METHOD__ . ' is deprecated and will go away in ramsey/uuid '
            . 'version 5; use the create() method instead',
        );
    }

    public function uuid6(?Hexadecimal $node = null, ?int $clockSeq = null): UuidInterface
    {
        throw new UnsupportedOperationException(
            __METHOD__ . ' is deprecated and will go away in ramsey/uuid '
            . 'version 5; use the create() method instead',
        );
    }
}
