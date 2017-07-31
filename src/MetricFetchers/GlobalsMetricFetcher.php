<?php
namespace PetrKotek\NaughtyTestDetector\MetricFetchers;

use PetrKotek\NaughtyTestDetector\MetricFetcher;

/**
 * GlobalsMetricFetcher helps you to identify tests which modify superglobal $GLOBALS array.
 */
class GlobalsMetricFetcher implements MetricFetcher
{
    /**
     * Simply returns `$GLOBALS`.
     *
     * @return mixed[]
     */
    public function fetchMetrics()
    {
        return $GLOBALS;
    }
}
