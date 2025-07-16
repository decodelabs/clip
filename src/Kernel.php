<?php

/**
 * @package Clip
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Clip;

use DecodeLabs\Clip;
use DecodeLabs\Clip\Controller\Commandment as CommandmentController;
use DecodeLabs\Coercion;
use DecodeLabs\Genesis\Context;
use DecodeLabs\Genesis\Kernel as KernelInterface;
use DecodeLabs\Monarch;
use DecodeLabs\Pandora\Container;

class Kernel implements KernelInterface
{
    public string $mode {
        get => 'Cli';
    }

    protected Context $context;
    protected bool|int|null $result = null;

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
                pcntl_signal($signal, function (int $signal) {
                    $io = Clip::getIoSession();
                    $io->newLine();

                    $this->result = 128 + $signal;
                    $this->shutdown();
                });
            }
        }
    }

    public function run(): void
    {
        set_time_limit(0);

        /** @var array<string> */
        $args = Coercion::toArray($_SERVER['argv']);
        array_shift($args);

        if (empty($args)) {
            $io = Clip::getIoSession();

            $io->newLine();
            $io->write('Command failed: ');
            $io->error('No action specified');
            $io->newLine();

            $this->shutdown();
        }

        $this->result = Clip::run(...$args);
    }

    public function shutdown(): never
    {
        match ($this->result) {
            true => exit(0),
            false => exit(1),
            null => exit(1),
            default => exit($this->result)
        };
    }
}
