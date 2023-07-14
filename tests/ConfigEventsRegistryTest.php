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
use Tobento\App\Event\ConfigEventsRegistry;
use Tobento\App\Event\ListenerRegistry;
use Tobento\Service\Event\Events;
use Tobento\Service\Event\Test\Mock;

/**
 * ConfigEventsRegistryTest
 */
class ConfigEventsRegistryTest extends TestCase
{
    public function testAddListenersFromArray()
    {
        $listeners = [
            // Specify events to listeners:
            Mock\FooEvent::class => [
                Mock\FooListener::class,

                // with build-in parameters:
                [Mock\ListenerWithBuildInParameter::class, ['number' => 5]],

                // with specific priority:
                new ListenerRegistry(
                    listener: Mock\FooBarListener::class,
                    priority: 1500,
                ),
            ],

            // Sepcify listeners without event:
            'auto' => [
                Mock\InvokableFooListener::class,

                // with build-in parameters:
                [Mock\ListenerWithBuildInParameter::class, ['number' => 5]],

                // with specific priority:
                new ListenerRegistry(
                    listener: Mock\FooBarListener::class,
                    priority: 1500,
                ),
            ],
        ];
        
        $events = new Events();
        
        $events = (new ConfigEventsRegistry(priority: 1000))
            ->addListenersFromArray($events, $listeners);
        
        $this->assertSame(6, count($events->listeners()->all()));
        $this->assertSame(1000, $events->listeners()->all()[0]->getPriority());
        $this->assertSame(1500, $events->listeners()->all()[5]->getPriority());
    }
}