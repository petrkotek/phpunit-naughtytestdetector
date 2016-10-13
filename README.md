# phpunit-naughtytestdetector
"Naughty test detector" for PHPUnit. Identifies tests leaving garbage after themselves.

## Requirements

- PHPUnit 4.8 or 5.6
- Supported PHP versions: 5.3, 5.4, 5.5, 5.6 and PHP 7.0

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
