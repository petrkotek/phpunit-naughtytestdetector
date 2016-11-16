<?php
namespace PetrKotek\NaughtyTestDetector\Tests\PHPUnit\Listeners\Dummy;

use PetrKotek\NaughtyTestDetector\MetricFetcher;

/**
 * Dummy metric fetcher, which works as a counter - every time returns incremented value.
 *
 * Also adds an long string prefix.
 */
class ArrayCounterFetcher implements MetricFetcher
{
    private $counter = 0;

    /**
     * @return array
     */
    public function fetchMetrics()
    {
        return ['counter' => [$this->counter++]];
    }
}
