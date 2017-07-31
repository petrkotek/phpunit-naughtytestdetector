# phpunit-naughtytestdetector
[![Build Status](https://travis-ci.org/petrkotek/phpunit-naughtytestdetector.svg?branch=master)](https://travis-ci.org/petrkotek/phpunit-naughtytestdetector)
[![Coverage Status](https://coveralls.io/repos/github/petrkotek/phpunit-naughtytestdetector/badge.svg?branch=master)](https://coveralls.io/github/petrkotek/phpunit-naughtytestdetector?branch=master)

"Naughty test detector" for PHPUnit. Identifies tests leaving garbage after themselves.

Many of us have been there - your integration test works in isolation, but breaks when running in a sequence of tests.
This can be very difficult to troubleshoot, but luckily `phpunit-naughtytestdetector` is here to help.  

## Requirements

- PHPUnit 6.0+
- Supported PHP versions: 7.0 and 7.1

Note: for older PHPUnit or PHP 5.6, use `v0.2.0`.

## Installation

NaughtyTestDetector is installable via [Composer](http://getcomposer.org) and should be added as a `require-dev` dependency:

    composer require --dev petrkotek/phpunit-naughtytestdetector

## Usage

#### 1. Enable `NaughtyTestListener` by adding the following to your test suite's `phpunit.xml` file:

```xml
<phpunit bootstrap="vendor/autoload.php">
    ...
    <listeners>
        <listener class="PetrKotek\NaughtyTestDetector\PHPUnit\Listeners\NaughtyTestListener">
            <arguments>
                <!-- Class name of your own MetricFetcher -->
                <string>MyProject\TestUtils\MyMetricFetcher</string>
                <!-- Optional constructor arguments for the metric fetcher -->
                <array>
                    <element>
                        <string>hello world</string>
                    </element>
                </array>
                <!-- Optionally specify levels on which MetricFetcher should be executed -->
                <!-- Note: values below are the default ones -->
                <array>
                    <element key="executeOnTestLevel">
                        <boolean>false</boolean>
                    </element>
                    <element key="executeOnTestSuiteLevel">
                        <boolean>true</boolean>
                    </element>                
                    <element key="executeOnGlobalLevel">
                        <boolean>false</boolean>
                    </element>
                </array>
            </arguments>
        </listener>
    </listeners>
</phpunit>
```

#### 2. Implement the `MetricFetcher` interface, e.g.:
```php
namespace MyProject\TestUtils\MyMetricFetcher;

use PetrKotek\NaughtyTestDetector\MetricFetcher;

class MyMetricFetcher implements MetricFetcher
{
    private $db;
    
    public function __construct()
    {
        $this->db = mysqli_connect("127.0.0.1", "my_user", "my_password", "my_db");
    }

    /**
     * @return array
     */
    public function fetchMetrics()
    {
        $result = mysqli_query($this->db, 'SELECT COUNT * FROM my_table');
        $row = mysqli_fetch_row($result);
        
        return ['records' => $row[0];
    }
}

```

Tip: You can also use built-in metric fetcher, e.g. `PetrKotek\NaughtyTestDetector\MetricFetchers\GlobalsMetricFetcher`.

#### 3. Run your test suite.

E.g. `phpunit --configuration integration.xml`

`NaughtyTestListener` will fetch metrics before & after each TestSuite (aka "test class") and if there is a difference
between before & after, it prints out like message like this:
```
MyProject\Integration\MyNamespace\BadTest is naughty!
 - my_table: 0 -> 5 (+5)
```

This means, that before the test, there was `0` records in the `my_table` and after executing all the tests, there were
`5` records.

Note: If you want to temporarily disable Naughty Test Detector, use `DISABLE_NAUGHTY_TEST_DETECTOR` enviromental variable, e.g. `DISABLE_NAUGHTY_TEST_DETECTOR=1 phpunit --configuration integration.xml`.
