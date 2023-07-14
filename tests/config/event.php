<?php

/**
 * TOBENTO
 *
 * @copyright   Tobias Strub, TOBENTO
 * @license     MIT License, see LICENSE file distributed with this source code.
 * @author      Tobias Strub
 * @link        https://www.tobento.ch
 */

use Tobento\App\Event\ListenerRegistry;
use Tobento\App\Event\Test\ShopEvents;
use Tobento\Service\Event\Test\Mock;

return [
    
    /*
    |--------------------------------------------------------------------------
    | Default Event Listeners
    |--------------------------------------------------------------------------
    |
    | Define the event listeners for the default events.
    |
    | See: https://github.com/tobento-ch/app-event#add-listeners
    |
    | As the events class uses reflection to scan listeners for its events named $event,
    | there is no need to define its event(s) for a listener.
    | But you might do so if you have multiple events in your listener and
    | want only to listen for the specific events or just because of better overview.
    |
    */
    
    'listeners' => [
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
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Specific Events Listeners
    |--------------------------------------------------------------------------
    |
    | Define the event listeners for the specific events.
    |
    | See: https://github.com/tobento-ch/app-event#add-specific-listeners
    |
    | As the events class uses reflection to scan listeners for its events named $event,
    | there is no need to define its event(s) for a listener.
    | But you might do so if you have multiple events in your listener and
    | want only to listen for the specific events or just because of better overview.
    |
    */
    
    'events' => [
        ShopEvents::class => [
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
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Default Priority For Default Event Listeners
    |--------------------------------------------------------------------------
    */
    
    'listeners_priority' => 1000,
    
    /*
    |--------------------------------------------------------------------------
    | Default Priority For Specific Events Listeners
    |--------------------------------------------------------------------------
    */
    
    'events_priority' => 1000,

];