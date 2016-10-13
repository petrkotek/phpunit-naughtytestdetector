<?php
namespace PetrKotek\NaughtyTestDetector\Tests\PHPUnit\Listeners;

use PetrKotek\NaughtyTestDetector\PHPUnit\Listeners\NaughtyTestListener;
use PetrKotek\NaughtyTestDetector\Tests\PHPUnit\Listeners\Dummy;
use PHPUnit_Framework_TestCase as TestCase;
use PHPUnit_Framework_TestSuite as TestSuite;

class NaughtyTestListenerTest extends TestCase
{
    public function testNoMetricFetcherConfigured()
    {
        $testSuite = $this->createTestSuiteMock('DummyTestSuite');
        $testListener = new NaughtyTestListener();

        $this->startOutputCapture();
        $testListener->startTestSuite($testSuite);
        $testListener->endTestSuite($testSuite);
        $output = $this->finishOutputCapture();

        static::assertSame('', $output);
    }

    public function testOneTestSuiteNoChange()
    {
        $testSuite = $this->createTestSuiteMock('DummyTestSuite');
        $testListener = new NaughtyTestListener(Dummy\StableFetcher::class);

        $this->startOutputCapture();
        $testListener->startTestSuite($testSuite);
        $testListener->endTestSuite($testSuite);
        $output = $this->finishOutputCapture();

        static::assertSame('', $output);
    }

    public function testOneTestSuiteWithChange()
    {
        $testSuite = $this->createTestSuiteMock('DummyTestSuite');
        $testListener = new NaughtyTestListener(Dummy\CountingFetcher::class);

        $this->startOutputCapture();
        $testListener->startTestSuite($testSuite);
        $testListener->endTestSuite($testSuite);
        $output = $this->finishOutputCapture();

        $this->assertSameLines([
            'DummyTestSuite is naughty!',
            ' - counter: 0 -> 1 (+1)',
        ], $output);
    }

    public function testOneTestSuiteWithMultipleChanges()
    {
        $testSuite = $this->createTestSuiteMock('DummyTestSuite');
        $testListener = new NaughtyTestListener(Dummy\MultiCountingFetcher::class);

        $this->startOutputCapture();
        $testListener->startTestSuite($testSuite);
        $testListener->endTestSuite($testSuite);
        $output = $this->finishOutputCapture();

        $this->assertSameLines([
            'DummyTestSuite is naughty!',
            ' - counter1: 0 -> 1 (+1)',
            ' - counter2: 0 -> 2 (+2)',
        ], $output);
    }

    public function testOneTestSuiteWithVaryingMetrics()
    {
        $testSuite = $this->createTestSuiteMock('DummyTestSuite');
        $testListener = new NaughtyTestListener(Dummy\VaryingMetricFetcher::class);

        $this->startOutputCapture();
        $testListener->startTestSuite($testSuite);
        $testListener->endTestSuite($testSuite);
        $output = $this->finishOutputCapture();

        $this->assertSameLines([
            'DummyTestSuite is naughty!',
            ' - counter0: 42 -> n/a',
            ' - counter1: n/a -> 42',
        ], $output);
    }

    public function testTwoTestSuitesNoChange()
    {
        $testSuite1 = $this->createTestSuiteMock('DummyTestSuite1');
        $testSuite2 = $this->createTestSuiteMock('DummyTestSuite2');
        $testListener = new NaughtyTestListener(Dummy\StableFetcher::class);

        $this->startOutputCapture();
        $testListener->startTestSuite($testSuite1);
        $testListener->endTestSuite($testSuite1);
        $testListener->startTestSuite($testSuite2);
        $testListener->endTestSuite($testSuite2);
        $output = $this->finishOutputCapture();

        static::assertSame('', $output);
    }

    public function testTwoTestSuitesWithChange()
    {
        $testSuite1 = $this->createTestSuiteMock('DummyTestSuite1');
        $testSuite2 = $this->createTestSuiteMock('DummyTestSuite2');

        $testListener = new NaughtyTestListener(Dummy\CountingFetcher::class);

        $this->startOutputCapture();
        $testListener->startTestSuite($testSuite1);
        $testListener->endTestSuite($testSuite1);
        $testListener->startTestSuite($testSuite2);
        $testListener->endTestSuite($testSuite2);
        $output = $this->finishOutputCapture();

        $this->assertSameLines([
            'DummyTestSuite1 is naughty!',
            ' - counter: 0 -> 1 (+1)',
            '',
            'DummyTestSuite2 is naughty!',
            ' - counter: 1 -> 2 (+1)',
        ], $output);
    }

    private function createTestSuiteMock($name)
    {
        $mock = $this->getMock(TestSuite::class);
        $mock->expects(static::any())
            ->method('getName')
            ->willReturn($name);
        return $mock;
    }

    /**
     * Starts capturing output (using `ob_start()`)
     *
     * @return void
     */
    private function startOutputCapture()
    {
        ob_start();
    }

    /**
     * Stops capturing output and returns the content.
     *
     * @return string
     */
    private function finishOutputCapture()
    {
        return ob_get_clean();
    }

    /**
     * @param string[] $expected
     * @param string $actual
     */
    private function assertSameLines(array $expected, $actual)
    {
        static::assertSame(implode(PHP_EOL, $expected), trim($actual, PHP_EOL));
    }
}
