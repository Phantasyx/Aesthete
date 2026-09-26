<?php
/**
 * Site settings for the PHP match server.
 * Values come from the environment, or from a gitignored .env file.
 * Nothing in this file is a credential.
 *
 * @param Steampunk\Site $site
 */
$envFile = dirname(__DIR__) . '/.env';
if (is_readable($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES);
    if ($lines !== false) {
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') {
                continue;
            }
            $eq = strpos($line, '=');
            if ($eq === false) {
                continue;
            }
            $key = trim(substr($line, 0, $eq));
            $value = trim(substr($line, $eq + 1));
            $length = strlen($value);
            if ($length >= 2) {
                $quote = $value[0];
                if (($quote === '"' || $quote === "'") && $value[$length - 1] === $quote) {
                    $value = substr($value, 1, -1);
                }
            }
            if ($key !== '' && getenv($key) === false) {
                putenv($key . '=' . $value);
                $_ENV[$key] = $value;
            }
        }
    }
}

return function(Steampunk\Site $site) {
    $timezone = getenv('AESTHETE_TIMEZONE');
    date_default_timezone_set($timezone !== false && $timezone !== '' ? $timezone : 'UTC');

    $email = getenv('AESTHETE_EMAIL');
    $site->setEmail($email !== false ? $email : '');

    $root = getenv('AESTHETE_ROOT');
    $site->setRoot($root !== false ? $root : '');

    $origin = getenv('AESTHETE_PUBLIC_ORIGIN');
    $site->setPublicOrigin($origin !== false ? $origin : '');

    $prefix = getenv('AESTHETE_TABLE_PREFIX');
    $dsn = getenv('AESTHETE_DB_DSN');
    $user = getenv('AESTHETE_DB_USER');
    $password = getenv('AESTHETE_DB_PASSWORD');
    $site->dbConfigure(
        $dsn !== false ? $dsn : '',
        $user !== false ? $user : '',
        $password !== false ? $password : '',
        $prefix !== false && $prefix !== '' ? $prefix : 'aesthete_'
    );
};
