<?php
namespace PetrKotek\NaughtyTestDetector;

interface MetricFetcher
{
    /**
     * Fetches snapshot of metrics to be measured before & after the test run.
     *
     * @return array Hashmap - key is a metric name, value is a metric value.
     */
    public function fetchMetrics();
}
