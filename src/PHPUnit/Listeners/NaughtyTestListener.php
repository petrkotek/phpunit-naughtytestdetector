<?php
namespace PetrKotek\NaughtyTestDetector\PHPUnit\Listeners;

use PetrKotek\NaughtyTestDetector\MetricFetcher;
use PHPUnit_Framework_BaseTestListener as BaseTestListener;
use PHPUnit_Framework_Test as Test;
use PHPUnit_Framework_TestCase as TestCase;
use PHPUnit_Framework_TestSuite as TestSuite;

/**
 * NaughtyTestListener is PHPUnit TestListener, which identifies tests, which don't clean up after themselves.
 */
class NaughtyTestListener extends BaseTestListener
{
    /** @var string Config key enabling execution of MetricFetcher on test-level (bool) */
    const CONFIG_KEY_LEVEL_TEST = 'executeOnTestLevel';

    /** @var string Config key enabling execution of MetricFetcher on test suite-level (bool) */
    const CONFIG_KEY_LEVEL_SUITE = 'executeOnTestSuiteLevel';

    /** @var string Config key enabling execution of MetricFetcher before & after running all tests (bool) */
    const CONFIG_KEY_LEVEL_GLOBAL = 'executeOnGlobalLevel';

    private $defaultOptions = [
        self::CONFIG_KEY_LEVEL_TEST => false,
        self::CONFIG_KEY_LEVEL_SUITE => true,
        self::CONFIG_KEY_LEVEL_GLOBAL => false,
    ];

    /** @var mixed[] Key is a metric name, value is a metric value before the globals test suite execution */
    private $metricsBeforeGlobalTestSuite;

    /** @var mixed[] Key is a metric name, value is a metric value before the test suite execution */
    private $metricsBeforeTestSuite;

    /** @var mixed[] Key is a metric name, value is a metric value before the test execution */
    private $metricsBeforeTest;

    /**
     * Counts test suites. Since there is one "overall" test suite encapsulating all test suits, once this counter is 0,
     * it's over or the beginning.
     *
     * @var int
     */
    private $testSuiteCounter = 0;

    /** @var MetricFetcher|null */
    private $metricFetcher;

    /** @var array */
    private $flags;

    /**
     * @param string $metricFetcherClass
     * @param array $constructorArgs
     * @param array $options Array with self::CONFIG_KEY_* as keys and value as noted in constant's comment
     */
    public function __construct(
        $metricFetcherClass = null,
        array $constructorArgs = [],
        array $options = []
    ) {
        if ($metricFetcherClass !== null) {
            $this->metricFetcher = new $metricFetcherClass(...$constructorArgs);
        }
        $this->flags = array_merge($this->defaultOptions, $options);
    }

    /**
     * @param TestSuite $suite
     */
    public function startTestSuite(TestSuite $suite)
    {
        if ($this->testSuiteCounter === 0 &&
            ($this->isLevelEnabled(self::CONFIG_KEY_LEVEL_SUITE) || $this->isLevelEnabled(self::CONFIG_KEY_LEVEL_GLOBAL))
        ) {
            $currentMetrics = $this->fetchMetrics();
            $this->metricsBeforeGlobalTestSuite = $currentMetrics;
            $this->metricsBeforeTestSuite = $currentMetrics;
            $this->metricsBeforeTest = $currentMetrics;
        }

        $this->testSuiteCounter++;
    }

    /**
     * @param TestSuite $suite
     */
    public function endTestSuite(TestSuite $suite)
    {
        $this->testSuiteCounter--;
        $currentMetrics = $this->isLevelEnabled(self::CONFIG_KEY_LEVEL_TEST) ? $this->metricsBeforeTest : null;

        if ($suite->getName() !== '' && $this->isLevelEnabled(self::CONFIG_KEY_LEVEL_SUITE)) {
            // finished test suite
            $currentMetrics = $currentMetrics ?: $this->fetchMetrics();
            $modifications = $this->evaluateModifications($this->metricsBeforeTestSuite, $currentMetrics);

            if (count($modifications) > 0) {
                $this->printNaughtyTest('TestSuite ' . $suite->getName(), $modifications);
            }

            $this->metricsBeforeTestSuite = $currentMetrics;
        }

        if ($this->testSuiteCounter === 0 && $this->isLevelEnabled(self::CONFIG_KEY_LEVEL_GLOBAL)) {
            // finished the global test suite
            $currentMetrics = $currentMetrics ?: $this->fetchMetrics();
            $modifications = $this->evaluateModifications($this->metricsBeforeGlobalTestSuite, $currentMetrics);

            if (count($modifications) > 0) {
                $this->printNaughtyTest('Global TestSuite', $modifications);
            }
        }
    }

    /**
     * @param Test $test
     */
    public function startTest(Test $test)
    {
        if ($this->metricsBeforeTest === null && $this->isLevelEnabled(self::CONFIG_KEY_LEVEL_TEST)) {
            $this->metricsBeforeTest = $this->fetchMetrics();
        }
    }

    /**
     * @param Test $test
     * @param float $time
     */
    public function endTest(Test $test, $time)
    {
        if (!($test instanceof TestCase)) {
            return;
        }
        if ($this->isLevelEnabled(self::CONFIG_KEY_LEVEL_TEST)) {
            $currentMetrics = $this->fetchMetrics();

            $modifications = $this->evaluateModifications($this->metricsBeforeTest, $currentMetrics);

            if (count($modifications) > 0) {
                $this->printNaughtyTest('Test ' . $test->getName(), $modifications);
            }

            $this->metricsBeforeTest = $currentMetrics;
        }
    }

    /**
     * @param string $name
     * @param array[] $modifications
     */
    private function printNaughtyTest($name, array $modifications)
    {
        echo PHP_EOL . "$name is naughty!";
        foreach ($modifications as $metric => $diffString) {
            echo PHP_EOL . " - $metric: $diffString";
        }
        echo PHP_EOL;
    }

    /**
     * @return array
     */
    private function fetchMetrics()
    {
        if ($this->metricFetcher === null) {
            return [];
        }
        return $this->metricFetcher->fetchMetrics();
    }

    private function formatValue($value)
    {
        return $value !== null ? $value : 'n/a';
    }

    /**
     * @param array $metricsBefore
     * @param array $metricsAfter
     * @return array
     */
    private function evaluateModifications(array $metricsBefore, array $metricsAfter)
    {
        $modifications = [];

        // evaluate metrics, which we had before the run
        foreach ($metricsBefore as $metric => $previousValue) {
            $currentValue = array_key_exists($metric, $metricsAfter) ? $metricsAfter[$metric] : null;
            if ($currentValue !== $previousValue) {
                $diffString = '';
                $currentValue = $this->formatValue($currentValue);
                if (is_numeric($previousValue) && is_numeric($currentValue)) {
                    $diffNum = $currentValue - $previousValue;
                    $diffString = sprintf(' (%s%d)', $diffNum > 0 ? '+' : '-', abs($diffNum));
                }
                $modifications[$metric] = $this->formatValue($previousValue) . ' -> ' . $this->formatValue($currentValue) . $diffString;
            }
        }

        // evaluate metrics, which we didn't have before the run
        $newMetrics = array_diff_key($metricsAfter, $metricsBefore);
        foreach ($newMetrics as $metric => $currentValue) {
            $modifications[$metric] = $this->formatValue(null) . ' -> ' . $this->formatValue($currentValue);
        }

        return $modifications;
    }

    private function isLevelEnabled($level)
    {
        return array_key_exists($level, $this->flags) && $this->flags[$level];
    }
}
