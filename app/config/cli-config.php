<?php
use Doctrine\ORM\Tools\Console\ConsoleRunner;

// replace with file to your own project bootstrap
require 'bootstrap.php';

return ConsoleRunner::createHelperSet($app->objectManager);
