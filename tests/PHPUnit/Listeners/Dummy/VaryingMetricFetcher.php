<?php
namespace PetrKotek\NaughtyTestDetector\Tests\PHPUnit\Listeners\Dummy;

use PetrKotek\NaughtyTestDetector\MetricFetcher;

/**
 * Dummy metric fetcher, which always returns one metric called 'metric' with constant value.
 */
class VaryingMetricFetcher implements MetricFetcher
{
    const ANSWER_TO_LIFE_THE_UNIVERSE_AND_EVERYTHING = 42;

    private $counter = 0;

    /**
     * @return array
     */
    public function fetchMetrics()
    {
        return [
            'stable' => 1,
            'counter' . $this->counter++ => self::ANSWER_TO_LIFE_THE_UNIVERSE_AND_EVERYTHING,
        ];
    }
}
