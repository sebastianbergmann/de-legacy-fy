# de-legacy-fy

[Legacy code is code without tests.](http://c2.com/cgi/wiki?WorkingEffectivelyWithLegacyCode)
Over the years I have helped many a team introduce (unit) testing into a legacy
code base, making it "less legacy" step by step.

The `de-legacy-fy` command-line tool is an attempt to put concepts and ideas
that proved to be effective at dealing with legacy PHP applications into code
and make them as reusable as possible.

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

## Usage Examples

### Wrapping a static API class

[Static methods are death to testability.](http://misko.hevery.com/2008/12/15/static-methods-are-death-to-testability/)
It is characteristic for a legacy code base to use global state and static
methods. Sometimes there are "library classes" that only contain static
methods:

```php
<?php
class Library
{
    public static function doSomething($a, $b)
    {
        // ...
    }
}
```

The problem with a static method is not that the static method itself is hard
to test. The problem is that the code that uses the static method is tightly
coupled to the static method, making it impossible to test without also
executing the code of the static method.

In the code below, the `Library` class is an implicit dependency of the
`Processor` class' `process()` method. It is implicit because it is not
obvious from the method's API that it depends on `Library`. Furthermore,
we can not test the `process()` method in isolation from the `doSomething()`
method.

```php
<?php
class Processor
{
    public function process()
    {
        // ...

        Library::doSomething('...', '...');

        // ...
    }
}
```

The `wrap-static-api` command of `de-legacy-fy` can automatically generate a
wrapper class for a static API class such as `Library`:

    $ de-legacy-fy wrap-static-api Library Library.php
    de-legacy-fy 1.0.0 by Sebastian Bergmann.

    Generated class "LibraryWrapper" in file "LibraryWrapper.php"

```php
<?php
/**
 * Automatically generated wrapper class for Library
 * @see Library
 */
class LibraryWrapper
{
    /**
     * @see Library::doSomething
     */
    public function doSomething($a, $b)
    {
        return Library::doSomething($a, $b);
    }
}
```

We can now make `LibraryWrapper` a dependency of `Processor`:

```php
<?php
class Processor
{
    private $library;

    public function __construct(LibraryWrapper $library)
    {
        $this->library = $library;
    }

    public function process()
    {
        // ...

        $this->library->doSomething('...', '...');

        // ...
    }
}
```

The `Processor` class does not use the legacy `Library` class directly anymore
and can be tested in isolation from it (as we can now stub or mock the
`LibraryWrapper` class).

Using the concept of [branch-by-abstraction](http://martinfowler.com/bliki/BranchByAbstraction.html)
we can now write new code that uses the `LibraryWrapper` class and migrate old
code from `Library` to `LibraryWrapper`. Eventually we can reimplement the
functionality of `Library` inside the `LibraryWrapper` class. Once
`LibraryWrapper` does not rely on `Library` anymore we can delete `Library`
and rename `LibraryWrapper` to `Library`.
