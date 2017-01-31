<?php

use Sli\ConfigsCompiler\Application;

require_once __DIR__.'/../vendor/autoload.php';

(new Application('configs-generator', 'dev'))->run();