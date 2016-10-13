<?php
namespace PetrKotek\NaughtyTestDetector\Tests\PHPUnit\Listeners\Dummy;

use PetrKotek\NaughtyTestDetector\MetricFetcher;

/**
 * Dummy metric fetcher, which always returns one metric called 'metric' with constant value.
 */
class StableFetcher implements MetricFetcher
{
    const ANSWER_TO_LIFE_THE_UNIVERSE_AND_EVERYTHING = 42;

    /**
     * @return array
     */
    public function fetchMetrics()
    {
        return ['metric' => self::ANSWER_TO_LIFE_THE_UNIVERSE_AND_EVERYTHING];
    }
}
