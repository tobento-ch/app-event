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

namespace Tobento\App\Event;

use Tobento\Service\Event\EventsInterface;

/**
 * ListenerRegistry
 */
final class ListenerRegistry
{
    /**
     * Create a new ListenerRegistry.
     *
     * @param mixed $listener
     * @param int $priority
     */
    public function __construct(
        private mixed $listener,
        private int $priority = 1000,
    ) {}

    /**
     * Returns the listener.
     *
     * @return mixed
     */
    public function listener(): mixed
    {
        return $this->listener;
    }
    
    /**
     * Returns the priority.
     *
     * @return int
     */
    public function priority(): int
    {
        return $this->priority;
    }
}