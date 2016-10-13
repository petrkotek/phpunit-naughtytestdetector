<?php
namespace PetrKotek\NaughtyTestDetector\PHPUnit\Listeners;

use PetrKotek\NaughtyTestDetector\MetricFetcher;
use PHPUnit_Framework_BaseTestListener as BaseTestListener;
use PHPUnit_Framework_TestSuite as TestSuite;

/**
 * NaughtyTestListener is PHPUnit TestListener, which identifies tests, which don't clean up after themselves.
 */
class NaughtyTestListener extends BaseTestListener
{
    /** @var int[] Key is a metric name, value is a metric value before the run */
    private $metricsBeforeRun = [];

    /** @var bool Flag indicate if this is the very first executed test suite */
    private $firstTestSuite = true;

    /** @var MetricFetcher|null */
    private $metricFetcher;

    /**
     * @param string $metricFetcherClass
     * @param array $constructorArgs
     */
    public function __construct($metricFetcherClass = null, array $constructorArgs = [])
    {
        if ($metricFetcherClass !== null) {
            $this->metricFetcher = new $metricFetcherClass(...$constructorArgs);
        }
    }

    /**
     * @param TestSuite $suite
     */
    public function startTestSuite(TestSuite $suite)
    {
        // We need to fetch counts before the very first test suite.
        // Next test suites will store counts in endTestSuite() method call
        if ($this->firstTestSuite) {
            $this->metricsBeforeRun = $this->fetchMetrics();
            $this->firstTestSuite = false;
        }
    }

    /**
     * @param TestSuite $suite
     */
    public function endTestSuite(TestSuite $suite)
    {
        $currentMetrics = $this->fetchMetrics();

        $modifications = [];

        // evaluate metrics, which we had before the run
        foreach ($this->metricsBeforeRun as $metric => $previousValue) {
            $currentValue = array_key_exists($metric, $currentMetrics) ? $currentMetrics[$metric] : null;
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
        $newMetrics = array_diff_key($currentMetrics, $this->metricsBeforeRun);
        foreach ($newMetrics as $metric => $currentValue) {
            $modifications[$metric] = $this->formatValue(null) . ' -> ' . $this->formatValue($currentValue);
        }

        if (count($modifications) > 0) {
            $this->printNaughtyTest($suite->getName(), $modifications);
        }

        $this->metricsBeforeRun = $currentMetrics;
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
}
