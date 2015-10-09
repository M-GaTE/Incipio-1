<?php

require_once __DIR__.'/vendor/autoload.php';

use SLLH\StyleCIBridge\ConfigBridge;
use Symfony\CS\Fixer\Contrib\HeaderCommentFixer;

$header = <<<EOF
This file is part of the Incipio package.

(c) Théo FIDRY <theo.fidry@gmail.com>

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
EOF;

$config = ConfigBridge::create();

return $config
    ->setRules(array_merge($config->getRules(), array(
        'header_comment' => array('header' => $header)
    )))
;
