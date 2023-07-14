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
 
namespace Tobento\App\Event\Boot;

use Tobento\App\Boot;
use Tobento\App\Boot\Config;
use Tobento\App\Migration\Boot\Migration;
use Tobento\Service\Event\EventsFactoryInterface;
use Tobento\Service\Event\AutowiringEventsFactory;
use Tobento\Service\Event\EventsInterface;
use Tobento\App\Event\ConfigEventsRegistry;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Event
 */
class Event extends Boot
{
    public const INFO = [
        'boot' => [
            'installs and loads event config file',
            'implements event interfaces',
            'adds event listeners from config file',
        ],
    ];

    public const BOOT = [
        Config::class,
        Migration::class,
    ];
    
    /**
     * Boot application services.
     *
     * @param Config $config
     * @param Migration $migration
     * @return void
     */
    public function boot(Config $config, Migration $migration): void
    {
        // install event config:
        $migration->install(\Tobento\App\Event\Migration\Event::class);
        
        // load the event config:
        $config = $config->load('event.php');
        
        $this->app->set(EventsFactoryInterface::class, function() {
            return new AutowiringEventsFactory(
                container: $this->app->container(),
            );
        });
        
        // Default events:
        $this->app->set(EventsInterface::class, function() use ($config) {
            
            $events = $this->app->get(EventsFactoryInterface::class)->createEvents();
            
            $events = (new ConfigEventsRegistry(priority: $config['listeners_priority'] ?? null))
                ->addListenersFromArray($events, $config['listeners'] ?? []);
            
            return $events;
        });
        
        // Default event dispatcher using default events:
        $this->app->set(EventDispatcherInterface::class, function(): EventsInterface {
            return $this->app->get(EventsInterface::class);
        });
        
        // Specific events:
        foreach($config['events'] ?? [] as $events => $listeners) {
            $this->app->on($events, function(EventsInterface $events) use ($config, $listeners) {
                $events = (new ConfigEventsRegistry(priority: $config['events_priority'] ?? null))
                    ->addListenersFromArray($events, $listeners);
                
                return $events;
            });
        }
    }
}