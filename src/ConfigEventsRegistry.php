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
 * ConfigEventsRegistry
 */
final class ConfigEventsRegistry
{
    /**
     * Create a new ConfigEventsRegistry.
     *
     * @param null|int $priority
     */
    public function __construct(
        private null|int $priority = null,
    ) {}

    /**
     * Adds listeners from array.
     *
     * @param EventsInterface $events
     * @param array $eventListeners
     * @return EventsInterface
     */
    public function addListenersFromArray(EventsInterface $events, array $eventListeners): EventsInterface
    {
        foreach($eventListeners as $event => $listeners) {
            
            foreach($listeners as $listener) {
                
                $priority = $this->priority;
                
                if ($listener instanceof ListenerRegistry) {
                    $priority = $listener->priority();
                    $listener = $listener->listener();
                }
                
                $eventListener = $events->listen($listener);
                
                if ($event !== 'auto') {
                    $eventListener->event($event);
                }
                
                if (is_int($priority)) {
                    $eventListener->priority($priority);
                }
            }
        }
        
        return $events;
    }
}