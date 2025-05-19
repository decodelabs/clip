<?php

/**
 * @package Clip
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Clip;

use DecodeLabs\Clip;
use DecodeLabs\Clip\Controller\Commandment as CommandmentController;
use DecodeLabs\Genesis\Context;
use DecodeLabs\Genesis\Kernel as KernelInterface;
use DecodeLabs\Monarch;
use DecodeLabs\Pandora\Container;
use DecodeLabs\Terminus;

class Kernel implements KernelInterface
{
    public string $mode {
        get => 'Cli';
    }

    protected Context $context;
    protected ?bool $result = null;

    public function __construct(
        Context $context
    ) {
        $this->context = $context;
    }

    public function initialize(): void
    {
        // Controller
        if (
            Monarch::$container instanceof Container &&
            !Monarch::$container->has(Controller::class)
        ) {
            Monarch::$container->bindShared(
                Controller::class,
                CommandmentController::class
            );
        }

        // Signals
        if (function_exists('pcntl_signal')) {
            pcntl_async_signals(true);
            $signals = [SIGTERM, SIGINT, SIGQUIT];

            foreach ($signals as $signal) {
                pcntl_signal($signal, function() {
                    Terminus::newLine();
                    $this->shutdown();
                }, false);
            }
        }
    }

    public function run(): void
    {
        set_time_limit(0);

        /** @var array<string> */
        $args = array_values(Terminus::getRequest()->getArguments());

        if(empty($args)) {
            Terminus::newLine();
            Terminus::write('Command failed: ');
            Terminus::error('No action specified');
            Terminus::newLine();
            $this->shutdown();
        }

        $this->result = Clip::run(...$args);
    }

    public function shutdown(): never
    {
        match ($this->result) {
            true => exit(0),
            false => exit(1),
            null => exit(1)
        };
    }
}
