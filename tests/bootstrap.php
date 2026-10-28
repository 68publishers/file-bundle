<?php

declare(strict_types=1);

if (@!include __DIR__ . '/../vendor/autoload.php') {
	echo 'Install Nette Tester using `composer install`';
	exit(1);
}

\Tester\Environment::setup();
\Tester\Environment::setupFunctions();
date_default_timezone_set('Europe/Prague');

if (!defined('TEMP_PATH')) {
	define('TEMP_PATH', __DIR__ . '/temp/' . getmypid());
}

@mkdir(TEMP_PATH, 0777, TRUE);

# The lowest versions of some third-party dependencies trigger deprecations on newer PHP versions (used by CI only)
if (getenv('TESTS_IGNORE_DEPRECATIONS')) {
	error_reporting(E_ALL & ~E_DEPRECATED);
}
