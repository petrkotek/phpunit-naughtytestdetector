# phpunit-naughtytestdetector
[![Build Status](https://travis-ci.org/petrkotek/phpunit-naughtytestdetector.svg?branch=master)](https://travis-ci.org/petrkotek/phpunit-naughtytestdetector)
[![Coverage Status](https://coveralls.io/repos/github/petrkotek/phpunit-naughtytestdetector/badge.svg?branch=master)](https://coveralls.io/github/petrkotek/phpunit-naughtytestdetector?branch=master)

"Naughty test detector" for PHPUnit. Identifies tests leaving garbage after themselves.

## Requirements

- PHPUnit 4.8 or 5.6
- Supported PHP versions: 5.6 and 7.0

## Installation

NaughtyTestDetector is installable via [Composer](http://getcomposer.org) and should be added as a `require-dev` dependency:

    composer require --dev petrkotek/phpunit-naughtytestdetector

## Usage

Enable with all defaults by adding the following to your test suite's `phpunit.xml` file:

```xml
<phpunit bootstrap="vendor/autoload.php">
...
    <listeners>
        <listener class="PetrKotek\PHPUnit\Listeners\NaughtyTestListener" />
        <arguments>
            <string>MyProject\TestUtils\MyMetricFetcher</string>
            <!-- Optional constructor arguments for the metric fetcher -->
            <array>
                <element>
                    <string>hello world</string>
                </element>
            </array>
        </arguments>
    </listeners>
</phpunit>
```

Now run your test suite as normal.
