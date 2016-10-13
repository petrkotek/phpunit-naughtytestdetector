<?php
namespace PetrKotek\NaughtyTestDetector\Tests\PHPUnit\Listeners\Dummy;

use PetrKotek\NaughtyTestDetector\MetricFetcher;

/**
 * Dummy metric fetcher, which has two counters - every time returns incremented value:
 * - counter1 increments by 1,
 * - counter2 increments by 2.
 */
class MultiCountingFetcher implements MetricFetcher
{
    private $counter1 = 0;
    private $counter2 = 0;

    /**
     * @return array
     */
    public function fetchMetrics()
    {
        $metrics = [
            'counter1' => $this->counter1,
            'counter2' => $this->counter2,
        ];

        $this->counter1++;
        $this->counter2 += 2;

        return $metrics;
    }
}
