<?php

/**
 * @package Clip
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs;

use DecodeLabs\Archetype;
use DecodeLabs\Commandment\Dispatcher;
use DecodeLabs\Commandment\Exception as CommandmentException;
use DecodeLabs\Commandment\NotFoundException as CommandNotFoundException;
use DecodeLabs\Kingdom\Service;
use DecodeLabs\Kingdom\ServiceTrait;
use DecodeLabs\Terminus\Session;

class Clip extends Dispatcher implements Service
{
    use ServiceTrait;

    public function __construct(
        protected Archetype $archetype,
        protected Session $io
    ) {
        parent::__construct($archetype);
    }

    public function run(
        string $action,
        string ...$args
    ): bool {
        /**
         * @var list<string> $args
         */
        return $this->runAction($action, $args);
    }

    /**
     * @param list<string> $args
     */
    public function runAction(
        string $name,
        array $args = []
    ): bool {
        $request = $this->newRequest(
            command: $name,
            arguments: $args
        );

        try {
            return $this->dispatch($request);
        } catch (CommandNotFoundException $e) {
            $session = $this->io;

            $session->newLine();
            $session->writeError('Command not found: ');
            $session->error($name);
            $session->newLine();
            return false;
        } catch (CommandmentException $e) {
            $session = $this->io;

            $session->newLine();
            $session->writeError('Command failed: ');
            $session->error($e->getMessage());
            $session->newLine();
            return false;
        }
    }
}
