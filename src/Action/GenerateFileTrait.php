<?php

/**
 * @package Clip
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Clip\Action;

use DecodeLabs\Atlas\File;
use DecodeLabs\Commandment\Argument;
use DecodeLabs\Commandment\Request;
use DecodeLabs\Genesis\FileTemplate;
use DecodeLabs\Terminus\Session;

trait GenerateFileTrait
{
    #[Argument\Flag(
        name: 'check',
        shortcut: 'c',
        description: 'Check if file exists',
    )]
    public function __construct(
        protected Session $io,
        protected Request $request
    ) {
    }

    public function execute(
        Request $request
    ): bool {
        $target = $this->getTargetFile();

        if (
            $target->exists() &&
            (
                $request->parameters->getAsBool('check') ||
                !$this->io->confirm($target->getName() . ' exists - overwrite?')
            )
        ) {
            $this->io->operative($target->getName() . ' exists, skipping');
            return true;
        }

        $template = $this->getTemplate();
        $template->saveTo($target);
        $this->io->success($target->getName() . ' created');

        if (!$this->afterFileSave($target)) {
            return false;
        }

        return true;
    }

    abstract protected function getTargetFile(): File;
    abstract protected function getTemplate(): FileTemplate;

    protected function afterFileSave(
        File $file
    ): bool {
        return true;
    }
}
