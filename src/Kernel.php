<?php

/**
 * @package Clip
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Clip;

use DecodeLabs\Clip\Controller\Generic as GenericController;
use DecodeLabs\Genesis\Context;
use DecodeLabs\Genesis\Kernel as KernelInterface;
use DecodeLabs\Terminus;

class Kernel implements KernelInterface
{
    public string $mode {
        get => 'Clip';
    }

    protected Context $context;
    protected ?bool $result;

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
        // Normalizer
        Normalizer::ensureRegistered();

        // Controller
        if (!$this->context->container->has(Controller::class)) {
            $this->context->container->bindShared(
                Controller::class,
                GenericController::class
            );
        }
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
        $this->result = $controller->run(...$args);
    }

    /**
     * Shutdown app
     */
    public function shutdown(): void
    {
        match ($this->result) {
            true => exit(0),
            false => exit(1),
            null => exit(1)
        };
    }
}
