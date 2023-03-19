<?php

# See https://phpunit.de/supported-versions.html

$currDir = dirname($argv[0]);
$phpVersion = PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;

$phpUnitVersions = [
    '5.6' => '4.8',
    '7.0' => '6.5',
    '7.1' => '7.5',
    '7.2' => '8.5',
    '7.3' => '8.5',
    '7.4' => '8.5',
    '8.0' => '9.6',
    '8.1' => '9.6',
    '8.2' => '9.6',
];

$phpUnitVersion = $phpUnitVersions[$phpVersion];
$projectRoot = realpath($currDir . '/../..');
$phpUnitCacheDir = $projectRoot . '/php/phpunit';
$phpUnitPharPath = $phpUnitCacheDir . '/phpunit-' . $phpUnitVersion . '.phar';
$phpUnitPharURL = 'https://phar.phpunit.de/phpunit-' . $phpUnitVersion . '.phar';
$phpUnitXML = $projectRoot . '/php/phpunit/phpunit-' . $phpUnitVersion . '.xml';
$phpUnitXMLCache = $phpUnitCacheDir . '/phpunit.php-' . $phpVersion . '.xml';

file_put_contents($phpUnitXMLCache,
    preg_replace(
        '/\$\{PHP_UNIT_TESTSUITE_NAME}/',
        'test ' . $phpVersion,
        file_get_contents($phpUnitXML)
    )
);

if (!file_exists($phpUnitPharPath)) {
    file_put_contents($phpUnitPharPath, file_get_contents($phpUnitPharURL));
}

array_shift($argv);

$cmd = ['php', escapeshellcmd($phpUnitPharPath)];
foreach ($argv as $k => $v) {
    if ($k > 0 and $argv[$k - 1] == '--configuration') {
        $cmd[] = $phpUnitXMLCache;
        continue;
    }
    
    // PHPStorm fix for PHP 5.6
    if ($phpVersion == '5.6' and $v == '--teamcity') {
        continue;
    }
    $cmd[] = escapeshellarg($v);
}

passthru(join(' ', $cmd), $exitCode);

exit($exitCode);


