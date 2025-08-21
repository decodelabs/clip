<?php

/**
 * @package Clip
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Clip\Tests;

use DecodeLabs\Atlas;
use DecodeLabs\Atlas\File;
use DecodeLabs\Clip\Action\GenerateFileTrait;
use DecodeLabs\Hatch\FileTemplate;

class AnalyzeGenerateFileTrait
{
    use GenerateFileTrait;

    protected function getTargetFile(): File
    {
        return Atlas::getFile('/path/to/target');
    }

    protected function getTemplate(): FileTemplate
    {
        return new FileTemplate('/path/to/template');
    }
}
