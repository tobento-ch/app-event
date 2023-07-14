# App Event

Event support for the app using the [**Event Service**](https://github.com/tobento-ch/service-event).

## Table of Contents

- [Getting Started](#getting-started)
    - [Requirements](#requirements)
- [Documentation](#documentation)
    - [App](#app)
    - [Event Boot](#event-boot)
        - [Event Config](#event-config)
        - [Available Event Interfaces](#available-event-interfaces)
        - [Default Events](#default-events)
            - [Create Event](#create-event)
            - [Create Listener](#create-listener)
            - [Add Listeners](#add-listeners)
            - [Dispatch Event](#dispatch-event)
        - [Specific Events](#specific-events)
            - [Create Events](#create-events)
            - [Register Events](#register-events)
            - [Add Specific Listeners](#add-specific-listeners)
            - [Use Events](#use-events)
        - [Queue Listeners](#queue-listeners)
- [Credits](#credits)
___

# Getting Started

Add the latest version of the app event project running this command.

```
composer require tobento/app-event
```

## Requirements

- PHP 8.0 or greater

# Documentation

## App

Check out the [**App Skeleton**](https://github.com/tobento-ch/app-skeleton) if you are using the skeleton.

You may also check out the [**App**](https://github.com/tobento-ch/app) to learn more about the app in general.

## Event Boot

The event boot does the following:

* installs and loads event config file
* implements event interfaces
* adds event listeners from config file

```php
use Tobento\App\AppFactory;

// Create the app
$app = (new AppFactory())->createApp();

// Add directories:
$app->dirs()
    ->dir(realpath(__DIR__.'/../'), 'root')
    ->dir(realpath(__DIR__.'/../app/'), 'app')
    ->dir($app->dir('app').'config', 'config', group: 'config')
    ->dir($app->dir('root').'vendor', 'vendor');
    
// Adding boots
$app->boot(\Tobento\App\Event\Boot\Event::class);

// Run the app
$app->run();
```

### Event Config

The configuration for the event is located in the ```app/config/event.php``` file at the default App Skeleton config location.

### Available Event Interfaces

The following interfaces are available after booting:

```php
use Tobento\App\AppFactory;
use Tobento\Service\Event\EventsFactoryInterface;
use Tobento\Service\Event\EventsInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

// Create the app
$app = (new AppFactory())->createApp();

// Add directories:
$app->dirs()
    ->dir(realpath(__DIR__.'/../'), 'root')
    ->dir(realpath(__DIR__.'/../app/'), 'app')
    ->dir($app->dir('app').'config', 'config', group: 'config')
    ->dir($app->dir('root').'vendor', 'vendor');
    
// Adding boots
$app->boot(\Tobento\App\Event\Boot\Event::class);
$app->booting();

$eventsFactory = $app->get(EventsFactoryInterface::class);
$events = $app->get(EventsInterface::class);
$eventDispatcher = $app->get(EventDispatcherInterface::class);

// var_dump($events === $eventDispatcher);
// bool(true)

// Run the app
$app->run();
```

Check out the [**Event Service**](https://github.com/tobento-ch/service-event#events) to learn more about the interfaces.

### Default Events

You can access the default events by using the ```Tobento\Service\Event\EventsInterface::class``` from within the app or by autowiring.

Furthermore, the events are used as the default ```Psr\EventDispatcher\EventDispatcherInterface::class``` implementation.

#### Create Event

```php
namespace App\Event;

use App\Entity\User;

final class UserRegistered
{
    public function __construct(
        public readonly User $user
    ) {}
}
```

#### Create Listener

```php
namespace App\Listener;

class SendWelcomeMail
{
    public function __invoke(UserRegistered $event): void
    {
        // send welcome mail.
    }
}
```

Check out the [**Defining Listeners**](https://github.com/tobento-ch/service-event#defining-and-add-listener) section to learn more about it.

#### Add Listeners

You can add listeners by the following ways:

**Using event config**

You may define the listener in the ```app/config/event.php``` file:

```php
use Tobento\App\Event\ListenerRegistry;
use App\Event;
use App\Listener;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Event Listeners
    |--------------------------------------------------------------------------
    |
    | Define the event listeners for the default events.
    |
    | As the events class uses reflection to scan listeners for its events named $event,
    | there is no need to define its event(s) for a listener.
    | But you might do so if you have multiple events in your listener and
    | want only to listen for the specific events or just because of better overview.
    |
    */
    
    'listeners' => [
        // Specify events to listeners:
        Event\UserRegistered::class => [
            Listener\SendWelcomeMail::class,
            
            // with build-in parameters:
            [Listener::class, ['number' => 5]],
            
            // with specific priority:
            new ListenerRegistry(
                listener: Listener::class,
                priority: 1,
            ),
        ],
        
        // Sepcify listeners without event:
        'auto' => [
            Listener\SendWelcomeMail::class,
            
            // with build-in parameters:
            [Listener::class, ['number' => 5]],
            
            // with specific priority:
            new ListenerRegistry(
                listener: Listener::class,
                priority: 1,
            ),
        ],
    ],
    
    // ...
];
```

**Manually using events**

You can add listeners manually by using the ```EventsInterface::class```.

```php
use Tobento\App\AppFactory;
use Tobento\Service\Event\EventsInterface;

// Create the app
$app = (new AppFactory())->createApp();

// Add directories:
$app->dirs()
    ->dir(realpath(__DIR__.'/../'), 'root')
    ->dir(realpath(__DIR__.'/../app/'), 'app')
    ->dir($app->dir('app').'config', 'config', group: 'config')
    ->dir($app->dir('root').'vendor', 'vendor');
    
// Adding boots
$app->boot(\Tobento\App\Event\Boot\Event::class);
$app->booting();

$events = $app->get(EventsInterface::class);

// Add listeners
$events->listen(FooListener::class);

$events->listen(AnyListener::class)
       ->event(FooEvent::class)
       ->priority(2000);

// Run the app
$app->run();
```

Check out the [**Add Listeners**](https://github.com/tobento-ch/service-event#add-listeners) to learn more about adding listeners.

#### Dispatch Event

You can dispatch events by the following ways:

**Using the event dispatcher**

The ```EventDispatcherInterface::class``` uses the default events as dispatcher implementation.

```php
namespace App\Service;

use Psr\EventDispatcher\EventDispatcherInterface;
use App\Event\UserRegistered;
use App\Entity\User;

final class UserService
{
    public function __construct(
        private readonly EventDispatcherInterface $dispatcher
    ) {}

    public function register(User $user): void
    {
        // ...
        
        $this->dispatcher->dispatch(new UserRegistered($user));
    }
}
```

**Using the events**

```php
namespace App\Service;

use Tobento\Service\Event\EventsInterface;
use App\Event\UserRegistered;
use App\Entity\User;

final class UserService
{
    public function __construct(
        private readonly EventsInterface $dispatcher
    ) {}

    public function register(User $user): void
    {
        // ...
        
        $this->dispatcher->dispatch(new UserRegistered($user));
    }
}
```

### Specific Events

You may create specific events for certain services, components or bundles.

#### Create Events

To create specific events simply extend the ```Events::class```:

```php
use Tobento\Service\Event\Events;

final class ShopEvents extends Events
{
    //
}
```

#### Register Events

After creating the events class you can register it in the app by the following ways:

**By directly using the app**

```php
use Tobento\App\AppFactory;
use Tobento\Service\Event\EventsFactoryInterface;

// Create the app
$app = (new AppFactory())->createApp();

// Add directories:
$app->dirs()
    ->dir(realpath(__DIR__.'/../'), 'root')
    ->dir(realpath(__DIR__.'/../app/'), 'app')
    ->dir($app->dir('app').'config', 'config', group: 'config')
    ->dir($app->dir('root').'vendor', 'vendor');
    
// Adding boots
$app->boot(\Tobento\App\Event\Boot\Event::class);

// Bind shop events to the app:
$app->set(ShopEvents::class, function() use ($app) {
    return $app->get(EventsFactoryInterface::class)->createEvents();
});

// Run the app
$app->run();
```

**By using a boot**

First, create the boot.

```php
use Tobento\App\Boot;
use Tobento\App\Boot\Config;
use Tobento\App\Migration\Boot\Migration;
use Tobento\Service\Event\EventsFactoryInterface;
use Tobento\App\Event\ConfigEventsRegistry;

class ShopEventsBoot extends Boot
{
    public const BOOT = [
        Config::class,
        Migration::class,
    ];
    
    public function boot(Config $config, Migration $migration): void
    {
        // you may install and load some config
        // for defining listeners.
        $migration->install(ShopEventsMigration::class);
        $config = $config->load('shop_event.php');
        
        $this->app->set(ShopEvents::class, function() use ($config) {
            
            $events = $this->app->get(EventsFactoryInterface::class)->createEvents();
            
            // add listeners from config:
            $events = (new ConfigEventsRegistry(priority: $config['listeners_priority']))
                ->addListenersFromArray($events, $config['listeners']);
            
            return $events;
        });
    }
}
```

Next, just add the boot:

```php
// ...

$app->boot(ShopEventsBoot::class);
$app->boot(\Tobento\App\Event\Boot\Event::class);

// ...
```

You may check out the [**App Migration**](https://github.com/tobento-ch/app-migration) to learn more about it.

You may check out the [**App Config**](https://github.com/tobento-ch/app#config-boot) to learn more about it.

#### Add Specific Listeners

You can add listeners for your specific events by the following ways:

**Using event config**

You may define the listener in the ```app/config/event.php``` file:

```php
use Tobento\App\Event\ListenerRegistry;

return [
    // ...
    
    /*
    |--------------------------------------------------------------------------
    | Specific Events Listeners
    |--------------------------------------------------------------------------
    |
    | Define the event listeners for the specific events.
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
            SomeEvent::class => [
                Listener::class,

                // with build-in parameters:
                [Listener::class, ['number' => 5]],
            ],

            // Sepcify listeners without event:
            'auto' => [
                Listener::class,

                // with build-in parameters:
                [Listener::class, ['number' => 5]],

                // to define specific priority:
                new ListenerRegistry(
                    listener: Listener::class,
                    priority: 1,
                ),
            ],
        ],
    ],
    
    // ...
];
```

**Manually using events**

You can add listeners manually by using the ```ShopEvents::class```.

```php
use Tobento\App\AppFactory;

// Create the app
$app = (new AppFactory())->createApp();

// Add directories:
$app->dirs()
    ->dir(realpath(__DIR__.'/../'), 'root')
    ->dir(realpath(__DIR__.'/../app/'), 'app')
    ->dir($app->dir('app').'config', 'config', group: 'config')
    ->dir($app->dir('root').'vendor', 'vendor');
    
// Adding boots
$app->boot(ShopEventsBoot::class);
$app->boot(\Tobento\App\Event\Boot\Event::class);
$app->booting();

$events = $app->get(ShopEvents::class);

// Add listeners
$events->listen(FooListener::class);

$events->listen(AnyListener::class)
       ->event(FooEvent::class)
       ->priority(2000);

// Run the app
$app->run();
```

Check out the [**Add Listeners**](https://github.com/tobento-ch/service-event#add-listeners) to learn more about adding listeners.

#### Use Events

After setting up your specific events, you can use it in your service.

```php
namespace App\Service;

use Psr\EventDispatcher\EventDispatcherInterface;

final class ShopService
{
    public function __construct(
        private readonly EventDispatcherInterface $dispatcher
    ) {}
}

// or:
final class AnotherShopService
{
    public function __construct(
        private readonly ShopEvents $dispatcher
    ) {}
}
```

There are several ways to inject your events into your ```ShopService::class```:

```php
// using a closure:
$app->set(ShopService::class, function() {
    return new ShopService(
        dispatcher: $app->get(ShopEvents::class),
    );
});

// using the construct method:
$app->set(ShopService::class)->construct($app->get(ShopEvents::class));

// using the on method:
$app->on(ShopService::class, ['dispatcher' => ShopEvents::class]);
```

The ```AnotherShopService::class``` requires no action for injection as ```ShopEvents::class``` is defined as dispatcher which gets autowired.

You may check out the [**App Definitions**](https://github.com/tobento-ch/app#definitions) and the [**App On**](https://github.com/tobento-ch/app#on) method for more information.

### Queue Listeners

In progress ...

# Credits

- [Tobias Strub](https://www.tobento.ch)
- [All Contributors](../../contributors)