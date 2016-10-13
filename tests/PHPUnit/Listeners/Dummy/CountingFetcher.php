<?php
namespace PetrKotek\NaughtyTestDetector\Tests\PHPUnit\Listeners\Dummy;

use PetrKotek\NaughtyTestDetector\MetricFetcher;

/**
 * Dummy metric fetcher, which works as a counter - every time returns incremented value.
 */
class CountingFetcher implements MetricFetcher
{
    private $counter = 0;

    /**
     * @return array
     */
    public function fetchMetrics()
    {
        return ['counter' => $this->counter++];
    }
}
