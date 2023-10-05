<?php

/**
 * @package Clip
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Clip\Task;

use DecodeLabs\Atlas\File;
use DecodeLabs\Genesis\FileTemplate;
use DecodeLabs\Terminus as Cli;

trait GenerateFileTrait
{
    public function execute(): bool
    {
        Cli::$command
            ->addArgument('-check|c', 'Check if file exists');

        $target = $this->getTargetFile();

        if (
            $target->exists() &&
            (
                Cli::$command['check'] ||
                !Cli::confirm($target->getName() . ' exists - overwrite?')
            )
        ) {
            Cli::operative($target->getName() . ' exists, skipping');
            return true;
        }

        $template = $this->getTemplate();
        $template->saveTo($target);
        Cli::success($target->getName() . ' created');

        if (!$this->afterFileSave($target)) {
            return false;
        }

        return true;
    }

    abstract protected function getTargetFile(): File;
    abstract protected function getTemplate(): FileTemplate;

    protected function afterFileSave(File $file): bool
    {
        return true;
    }
}
