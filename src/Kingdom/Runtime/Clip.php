<?php

/**
 * @package Clip
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Kingdom\Runtime;

use DecodeLabs\Clip as ClipService;
use DecodeLabs\Coercion;
use DecodeLabs\Kingdom\Runtime;
use DecodeLabs\Kingdom\RuntimeMode;
use DecodeLabs\Monarch;
use DecodeLabs\Terminus\Session;
use Throwable;

class Clip implements Runtime
{
    public RuntimeMode $mode {
        get => RuntimeMode::Cli;
    }

    protected bool|int|null $result = null;

    public function __construct(
        protected Session $io,
        protected ClipService $clip
    ) {
    }

    public function initialize(): void
    {
        // Signals
        if (function_exists('pcntl_signal')) {
            pcntl_async_signals(true);
            $signals = [SIGTERM, SIGINT, SIGQUIT];

            foreach ($signals as $signal) {
                pcntl_signal($signal, function (int $signal) {
                    $this->io->newLine();

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
            $this->io->newLine();
            $this->io->writeError('Command failed: ');
            $this->io->error('No action specified');
            $this->io->newLine();

            $this->shutdown();
        }

        try {
            $this->result = $this->clip->run(...$args);
        } catch (Throwable $e) {
            Monarch::logException($e);

            $this->io->newLine();
            $this->io->writeError('Command failed: ');
            $this->io->error($e->getMessage());
            $this->io->newLine();
            $this->io->{'!.white|dim'}($e->getFile() . ':' . $e->getLine());
            $this->io->newLine();
            $this->io->newLine();
            $this->shutdown();
        }
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
