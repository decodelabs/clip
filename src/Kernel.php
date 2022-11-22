<?php

/**
 * @package Clip
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Clip;

use DecodeLabs\Archetype;
use DecodeLabs\Coercion;
use DecodeLabs\Dictum;
use DecodeLabs\Genesis\Kernel as KernelInterface;
use DecodeLabs\Terminus;

class Kernel implements KernelInterface
{
    /**
     * Initialize platform systems
     */
    public function initialize(): void
    {
        Archetype::registerCustomNormalizer(
            Task::class,
            function (string $name): string {
                $parts = explode('/', $name);

                foreach ($parts as $i => $part) {
                    $parts[$i] = Dictum::id($part);
                }

                return implode('\\', $parts);
            }
        );

        // Cli args
        Terminus::getCommandDefinition()
            ->addArgument('task', 'Task path');
    }

    /**
     * Get run mode
     */
    public function getMode(): string
    {
        return 'Clip';
    }

    /**
     * Run app
     */
    public function run(): void
    {
        set_time_limit(0);

        $args = Terminus::prepareArguments();
        $task = Coercion::toString($args['task']);
        $class = Archetype::resolve(Task::class, $task);

        $request = Terminus::getRequest();
        $requestArgs = $request->getArguments();
        array_shift($requestArgs);

        Terminus::setRequest(
            $request
                ->withScript($task)
                ->withArguments($requestArgs)
        );

        $task = new $class();
        $task->execute();
    }

    /**
     * Shutdown app
     */
    public function shutdown(): void
    {
    }
}
