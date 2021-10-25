<?php

declare(strict_types = 1);

use App\DB\DB;
use Doctrine\ORM\Tools\Console\ConsoleRunner;

// Include here to make sure cli tools work
require_once 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
return ConsoleRunner::createHelperSet(DB::getEm());