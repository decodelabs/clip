<?php

/**
 * @package Clip
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Clip;

require_once 'vendor/autoload.php';

use DecodeLabs\Genesis\Bootstrap\Analysis;

new Analysis(
    hubClass: Hub::class
)->initializeOnly();
