# Clip

[![PHP from Packagist](https://img.shields.io/packagist/php-v/decodelabs/clip?style=flat)](https://packagist.org/packages/decodelabs/clip)
[![Latest Version](https://img.shields.io/packagist/v/decodelabs/clip.svg?style=flat)](https://packagist.org/packages/decodelabs/clip)
[![Total Downloads](https://img.shields.io/packagist/dt/decodelabs/clip.svg?style=flat)](https://packagist.org/packages/decodelabs/clip)
[![GitHub Workflow Status](https://img.shields.io/github/actions/workflow/status/decodelabs/clip/integrate.yml?branch=develop)](https://github.com/decodelabs/clip/actions/workflows/integrate.yml)
[![PHPStan](https://img.shields.io/badge/PHPStan-enabled-44CC11.svg?longCache=true&style=flat)](https://github.com/phpstan/phpstan)
[![License](https://img.shields.io/packagist/l/decodelabs/clip?style=flat)](https://packagist.org/packages/decodelabs/clip)

### CLI kernel integration for DecodeLabs Genesis

Clip provides a framework for building comprehensive CLI based applications on top of [Genesis](https://github.com/decodelabs/genesis).

---

## Installation

Install via Composer:

```bash
composer require decodelabs/clip
```

## Usage

Clip is a middleware library that provides an out-of-the-box setup for implementing a Genesis based CLI task runner. This means you don't really interact with it much directly, except when setting up the core of your task running infrastructure.

### Create your Hub

Define your Genesis Hub by extending Clip's abstract implementation:

```php
namespace MyThing;

use DecodeLabs\Archetype;
use DecodeLabs\Clip\Hub as ClipHub;
use DecodeLabs\Commandment\Action as ActionInterface;
use MyThing\Action;

class Hub extends ClipHub
{
    public function initializePlatform(): void
    {
        parent::initializePlatform();

        // Load tasks from local namespace
        $archetype = $this->container->get(Archetype::class);
        $archetype->map(ActionInterface::class, Action::class);
    }
}
```

With this hub in place, you can run tasks defined in your nominated namespace from the terminal via a bin defined in composer:

```php
namespace MyThing;

use DecodeLabs\Genesis\Bootstrap\Bin as BinBootstrap;
use MyThing\Hub;

require_once $_composer_autoload_path ?? __DIR__ . '/../vendor/autoload.php';
new BinBootstrap(Hub::class)->run();
```

```json
{
    "bin": [
        "bin/thing"
    ]
}
```


Define your task:

```php
namespace MyThing\Action;

use DecodeLabs\Commandment\Action;
use DecodeLabs\Commandment\Request;

class MyAction implements Action
{
    public function execute(
        Request $request
    ): bool {
        // Do the thing
        return true;
    }
}
```

```bash
composer exec thing my-action

# or with effigy
effigy thing my-action
```

### IO

When writing back to the terminal, you _can_ use a `Terminus\Session`:

```php
use DecodeLabs\Monarch;
use DecodeLabs\Terminus\Session;

$io = Monarch::getService(Session::class);
$io->$writeLine('Hello world');
```

To make your Actions as portable as possible, you should import the Terminus `Session` in your Action constructor using Commandment's dependency injection:

```php
namespace MyThing\Action;

use DecodeLabs\Commandment\Action;
use DecodeLabs\Commandment\Request;
use DecodeLabs\Terminus\Session;

class MyAction implements Action
{
    public function __construct(
        private Session $io
    ) {
    }

    public function execute(
        Request $request
    ): bool {
        $this->io->writeLine('Hello world');

        return true;
    }
}
```

Then, any calling code can provide an alternative `Session` instance to the `Dispatcher` to allow your Action to run in a different context.

## Licensing

Clip is licensed under the MIT License. See [LICENSE](./LICENSE) for the full license text.
