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

1. Enable with all defaults by adding the following to your test suite's `phpunit.xml` file:

```xml
<phpunit bootstrap="vendor/autoload.php">
...
    <listeners>
        <listener class="PetrKotek\NaughtyTestDetector\PHPUnit\Listeners\NaughtyTestListener">
            <arguments>
                <string>MyProject\TestUtils\MyMetricFetcher</string>
                <!-- Optional constructor arguments for the metric fetcher -->
                <array>
                    <element>
                        <string>hello world</string>
                    </element>
                </array>
            </arguments>
        </listener>
    </listeners>
</phpunit>
```

2. Implement `MyProject\TestUtils\MyMetricFetcher` class, e.g.:
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

3. Now run your test suite as normal. If some of the tests leaves new records in the `my_table`, `NaughtyTestListener`
outputs something like this:
```
Integration\Model\Content\PagesTest is naughty!
 - my_table: 0 -> 5 (+5)
```
