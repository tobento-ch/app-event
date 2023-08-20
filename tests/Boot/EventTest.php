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
use Tobento\App\AppInterface;
use Tobento\App\AppFactory;
use Tobento\App\Event\Boot\Event;
use Tobento\Service\Event\ListenersInterface;
use Tobento\Service\Event\EventsFactoryInterface;
use Tobento\Service\Event\EventsInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Tobento\Service\Filesystem\Dir;
use Tobento\Service\Event\Test\Mock;
use Tobento\App\Event\Test\ShopEvents;

/**
 * EventTest
 */
class EventTest extends TestCase
{
    protected function createApp(bool $deleteDir = true): AppInterface
    {
        if ($deleteDir) {
            (new Dir())->delete(__DIR__.'/../app/');
        }
        
        (new Dir())->create(__DIR__.'/../app/');
        (new Dir())->create(__DIR__.'/../app/config/');
        
        $app = (new AppFactory())->createApp();
        
        $app->dirs()
            ->dir(realpath(__DIR__.'/../../'), 'root')
            ->dir(realpath(__DIR__.'/../app/'), 'app')
            ->dir($app->dir('app').'config', 'config', group: 'config', priority: 10)
            ->dir($app->dir('root').'vendor', 'vendor');
        
        return $app;
    }
    
    public static function tearDownAfterClass(): void
    {
        (new Dir())->delete(__DIR__.'/../app/');
    }
    
    public function testInterfacesAreAvailable()
    {
        $app = $this->createApp();
        $app->boot(Event::class);
        $app->booting();
        
        $this->assertInstanceof(ListenersInterface::class, $app->get(ListenersInterface::class));
        $this->assertInstanceof(EventsFactoryInterface::class, $app->get(EventsFactoryInterface::class));
        $this->assertInstanceof(EventsInterface::class, $app->get(EventsInterface::class));
        $this->assertInstanceof(EventDispatcherInterface::class, $app->get(EventDispatcherInterface::class));
        $this->assertSame($app->get(EventsInterface::class), $app->get(EventDispatcherInterface::class));
    }
    
    public function testListenersInterfaceIsDefinedAsPrototypeInApp()
    {
        $app = $this->createApp();
        $app->boot(Event::class);
        $app->booting();
        $this->assertFalse($app->get(ListenersInterface::class) === $app->get(ListenersInterface::class));
    }    
    
    public function testListenersAreAddedFromConfigWithDispatch()
    {
        $app = $this->createApp();
        $app->dirs()->dir(realpath(__DIR__.'/../config/'), 'config-test', group: 'config', priority: 20);
        $app->boot(Event::class);
        $app->booting();
                
        $events = $app->get(EventsInterface::class);
        $this->assertSame(6, count($events->listeners()->all()));
        
        // only FooListener as others add not message to event:
        $this->assertSame(
            [Mock\FooListener::class],
            $events->dispatch(new Mock\FooEvent())->messages()
        );
    }
    
    public function testEventsAreAddedFromConfigWithDispatch()
    {
        $app = $this->createApp();
        $app->dirs()->dir(realpath(__DIR__.'/../config/'), 'config-test', group: 'config', priority: 20);
        $app->boot(Event::class);        
        $app->booting();
                
        $events = $app->get(ShopEvents::class);
        $this->assertInstanceof(ShopEvents::class, $events);
        $this->assertSame(6, count($events->listeners()->all()));
        
        // only FooListener as others add not message to event:
        $this->assertSame(
            [Mock\FooListener::class],
            $events->dispatch(new Mock\FooEvent())->messages()
        );
    }
}