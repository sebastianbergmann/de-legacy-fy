# de-legacy-fy

`de-legacy-fy` ...

## Installation

### PHP Archive (PHAR)

The easiest way to obtain de-legacy-fy is to download a [PHP Archive (PHAR)](http://php.net/phar) that has all required dependencies of de-legacy-fy bundled in a single file:

    wget https://phar.phpunit.de/de-legacy-fy.phar
    chmod +x de-legacy-fy.phar
    mv de-legacy-fy.phar /usr/local/bin/de-legacy-fy

You can also immediately use the PHAR after you have downloaded it, of course:

    wget https://phar.phpunit.de/de-legacy-fy.phar
    php de-legacy-fy.phar

### Composer

Simply add a dependency on `sebastian/de-legacy-fy` to your project's `composer.json` file if you use [Composer](http://getcomposer.org/) to manage the dependencies of your project. Here is a minimal example of a `composer.json` file that just defines a development-time dependency on de-legacy-fy:

    {
        "require-dev": {
            "sebastian/de-legacy-fy": "*"
        }
    }

For a system-wide installation via Composer, you can run:

    composer global require 'sebastian/de-legacy-fy=*'

Make sure you have `~/.composer/vendor/bin/` in your path.

