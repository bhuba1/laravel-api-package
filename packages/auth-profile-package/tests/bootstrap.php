<?php

declare(strict_types=1);

require dirname(__DIR__).'/vendor/autoload.php';

if (! in_array('sqlite', PDO::getAvailableDrivers(), true)) {
    fwrite(
        STDERR,
        "Skipping database tests: the pdo_sqlite PHP extension is not installed.\n".
        "Install it to run the full test suite (Arch/CachyOS: sudo pacman -S php-sqlite).\n".
        "Alternatively run: composer test:docker\n\n",
    );
}
