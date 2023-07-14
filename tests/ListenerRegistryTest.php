<?php

/**
 * TOBENTO
 *
 * @copyright   Tobias Strub, TOBENTO
 * @license     MIT License, see LICENSE file distributed with this source code.
 * @author      Tobias Strub
 * @link        https://www.tobento.ch
 */

declare(strict_types=1);

namespace Tobento\App\Event\Test\Boot;

use PHPUnit\Framework\TestCase;
use Tobento\App\Event\ListenerRegistry;
use Tobento\Service\Event\Test\Mock;

/**
 * ListenerRegistryTest
 */
class ListenerRegistryTest extends TestCase
{
    public function testMethods()
    {
        $registry = new ListenerRegistry(
            listener: Mock\FooBarListener::class,
            priority: 1500,
        );
        
        $this->assertSame(Mock\FooBarListener::class, $registry->listener());
        $this->assertSame(1500, $registry->priority());
    }
}