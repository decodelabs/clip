<?php

/**
 * @package Clip
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Clip;

use DecodeLabs\Archetype;
use DecodeLabs\Clip\Controller\Generic as GenericController;
use DecodeLabs\Dictum;
use DecodeLabs\Genesis\Context;
use DecodeLabs\Genesis\Kernel as KernelInterface;
use DecodeLabs\Terminus;

class Kernel implements KernelInterface
{
    protected Context $context;

    public function __construct(
        Context $context
    ) {
        $this->context = $context;
    }

    /**
     * Initialize platform systems
     */
    public function initialize(): void
    {
        // Task name
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

        // Controller
        if (!$this->context->container->has(Controller::class)) {
            $this->context->container->bindShared(
                Controller::class,
                GenericController::class
            );
        }
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

        $controller = $this->context->container->get(Controller::class);

        /** @var array<string> */
        $args = array_values(Terminus::getRequest()->getArguments());
        $controller->run(...$args);
    }

    /**
     * Shutdown app
     */
    public function shutdown(): void
    {
    }
}
