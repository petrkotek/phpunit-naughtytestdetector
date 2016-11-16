<?php
namespace PetrKotek\NaughtyTestDetector\Tests\PHPUnit\Listeners\Dummy;

use PetrKotek\NaughtyTestDetector\MetricFetcher;
use stdClass;

/**
 * Dummy metric fetcher, which works as a counter - every time returns incremented value.
 *
 * Also adds an long string prefix.
 */
class NonserializableObjectFetcher implements MetricFetcher
{
    private $counter = 0;

    /**
     * @return array
     */
    public function fetchMetrics()
    {
        $value = new stdClass();
        $value->counter = $this->counter++;
        $value->me = $value;

        return ['counter' => $value];
    }
}
