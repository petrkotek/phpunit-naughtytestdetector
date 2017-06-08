<?php
namespace PetrKotek\NaughtyTestDetector\Tests\PHPUnit\Listeners;

use PetrKotek\NaughtyTestDetector\PHPUnit\Listeners\NaughtyTestListener;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestSuite;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class NaughtyTestListenerTest extends TestCase
{
    public function testNoMetricFetcherConfigured()
    {
        $testListener = new NaughtyTestListener();

        $this->startOutputCapture();
        $this->runTestSuites($testListener, [1]);
        $output = $this->finishOutputCapture();

        static::assertSame('', $output);
    }

    public function testOneTestSuiteNoChange()
    {
        $testListener = new NaughtyTestListener(Dummy\StableFetcher::class);

        $this->startOutputCapture();
        $this->runTestSuites($testListener, [1]);
        $output = $this->finishOutputCapture();

        static::assertSame('', $output);
    }

    public function testOneTestSuiteWithChange()
    {
        $testListener = new NaughtyTestListener(Dummy\CountingFetcher::class);

        $this->startOutputCapture();
        $this->runTestSuites($testListener, [1]);
        $output = $this->finishOutputCapture();

        $this->assertSameLines([
            'TestSuite DummyTestSuite1 is naughty (as per CountingFetcher)!',
            ' - counter: 0 -> 1 (+1)',
        ], $output);
    }

    public function testOneTestSuiteWithMultipleChanges()
    {
        $testListener = new NaughtyTestListener(Dummy\MultiCountingFetcher::class);

        $this->startOutputCapture();
        $this->runTestSuites($testListener, [1]);
        $output = $this->finishOutputCapture();

        $this->assertSameLines([
            'TestSuite DummyTestSuite1 is naughty (as per MultiCountingFetcher)!',
            ' - counter1: 0 -> 1 (+1)',
            ' - counter2: 0 -> 2 (+2)',
        ], $output);
    }

    public function testOneTestSuiteWithVaryingMetrics()
    {
        $testListener = new NaughtyTestListener(Dummy\VaryingMetricFetcher::class);

        $this->startOutputCapture();
        $this->runTestSuites($testListener, [1]);
        $output = $this->finishOutputCapture();

        $this->assertSameLines([
            'TestSuite DummyTestSuite1 is naughty (as per VaryingMetricFetcher)!',
            ' - counter0: 42 -> n/a',
            ' - counter1: n/a -> 42',
        ], $output);
    }

    public function testTwoTestSuitesNoChange()
    {
        $testListener = new NaughtyTestListener(Dummy\StableFetcher::class);

        $this->startOutputCapture();
        $this->runTestSuites($testListener, [1, 1]);
        $output = $this->finishOutputCapture();

        static::assertSame('', $output);
    }

    public function testTwoTestSuitesWithChange()
    {
        $testListener = new NaughtyTestListener(Dummy\CountingFetcher::class);

        $this->startOutputCapture();
        $this->runTestSuites($testListener, [1, 1]);
        $output = $this->finishOutputCapture();

        $this->assertSameLines([
            'TestSuite DummyTestSuite1 is naughty (as per CountingFetcher)!',
            ' - counter: 0 -> 1 (+1)',
            '',
            'TestSuite DummyTestSuite2 is naughty (as per CountingFetcher)!',
            ' - counter: 1 -> 2 (+1)',
        ], $output);
    }

    public function testGlobalTestSuiteLevelOnly()
    {
        $testListener = new NaughtyTestListener(Dummy\CountingFetcher::class, [], [
            NaughtyTestListener::CONFIG_KEY_LEVEL_GLOBAL => true,
            NaughtyTestListener::CONFIG_KEY_LEVEL_SUITE => false,
        ]);

        $this->startOutputCapture();
        $this->runTestSuites($testListener, [2, 1]);
        $output = $this->finishOutputCapture();

        $this->assertSameLines([
            'Global TestSuite is naughty (as per CountingFetcher)!',
            ' - counter: 0 -> 1 (+1)',
        ], $output);
    }

    public function testTestLevelOnly()
    {
        $testListener = new NaughtyTestListener(Dummy\CountingFetcher::class, [], [
            NaughtyTestListener::CONFIG_KEY_LEVEL_TEST => true,
            NaughtyTestListener::CONFIG_KEY_LEVEL_SUITE => false,
        ]);

        $this->startOutputCapture();
        $this->runTestSuites($testListener, [2, 1]);
        $output = $this->finishOutputCapture();

        $this->assertSameLines([
            'Test DummyTestSuite1::testFoo1 is naughty (as per CountingFetcher)!',
            ' - counter: 0 -> 1 (+1)',
            '',

            'Test DummyTestSuite1::testFoo2 is naughty (as per CountingFetcher)!',
            ' - counter: 1 -> 2 (+1)',
            '',
            'Test DummyTestSuite2::testFoo1 is naughty (as per CountingFetcher)!',
            ' - counter: 2 -> 3 (+1)',
        ], $output);
    }

    public function testTestAndTestSuiteLevel()
    {
        $testListener = new NaughtyTestListener(Dummy\CountingFetcher::class, [], [
            NaughtyTestListener::CONFIG_KEY_LEVEL_SUITE => true,
            NaughtyTestListener::CONFIG_KEY_LEVEL_TEST => true,
        ]);

        $this->startOutputCapture();
        $this->runTestSuites($testListener, [1, 2]);
        $output = $this->finishOutputCapture();

        $this->assertSameLines([
            'Test DummyTestSuite1::testFoo1 is naughty (as per CountingFetcher)!',
            ' - counter: 0 -> 1 (+1)',
            '',
            'TestSuite DummyTestSuite1 is naughty (as per CountingFetcher)!',
            ' - counter: 0 -> 1 (+1)',
            '',

            'Test DummyTestSuite2::testFoo1 is naughty (as per CountingFetcher)!',
            ' - counter: 1 -> 2 (+1)',
            '',
            'Test DummyTestSuite2::testFoo2 is naughty (as per CountingFetcher)!',
            ' - counter: 2 -> 3 (+1)',
            '',
            'TestSuite DummyTestSuite2 is naughty (as per CountingFetcher)!',
            ' - counter: 1 -> 3 (+2)',
        ], $output);
    }

    public function testAllLevels()
    {
        $testListener = new NaughtyTestListener(Dummy\CountingFetcher::class, [], [
            NaughtyTestListener::CONFIG_KEY_LEVEL_GLOBAL => true,
            NaughtyTestListener::CONFIG_KEY_LEVEL_SUITE => true,
            NaughtyTestListener::CONFIG_KEY_LEVEL_TEST => true,
        ]);

        $this->startOutputCapture();
        $this->runTestSuites($testListener, [1, 1]);
        $output = $this->finishOutputCapture();

        $this->assertSameLines([
            'Test DummyTestSuite1::testFoo1 is naughty (as per CountingFetcher)!',
            ' - counter: 0 -> 1 (+1)',
            '',
            'TestSuite DummyTestSuite1 is naughty (as per CountingFetcher)!',
            ' - counter: 0 -> 1 (+1)',
            '',

            'Test DummyTestSuite2::testFoo1 is naughty (as per CountingFetcher)!',
            ' - counter: 1 -> 2 (+1)',
            '',
            'TestSuite DummyTestSuite2 is naughty (as per CountingFetcher)!',
            ' - counter: 1 -> 2 (+1)',
            '',
            'Global TestSuite is naughty (as per CountingFetcher)!',
            ' - counter: 0 -> 2 (+2)',
        ], $output);
    }

    public function testDisabledByEnv()
    {
        // setup env
        putenv('DISABLE_NAUGHTY_TEST_DETECTOR=1');

        $testListener = new NaughtyTestListener('NonExistingClassName', [], [
            NaughtyTestListener::CONFIG_KEY_LEVEL_GLOBAL => true,
            NaughtyTestListener::CONFIG_KEY_LEVEL_SUITE => true,
            NaughtyTestListener::CONFIG_KEY_LEVEL_TEST => true,
        ]);

        $this->startOutputCapture();
        $this->runTestSuites($testListener, [1, 1]);
        $output = $this->finishOutputCapture();

        static::assertSame('', $output);

        // revert env
        putenv('DISABLE_NAUGHTY_TEST_DETECTOR=');
    }

    public function testLongStringOutput()
    {
        $testListener = new NaughtyTestListener(Dummy\LongStringFetcher::class);

        $this->startOutputCapture();
        $this->runTestSuites($testListener, [1]);
        $output = $this->finishOutputCapture();

        $this->assertSameLines([
            'TestSuite DummyTestSuite1 is naughty (as per LongStringFetcher)!',
            ' - counter: aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa... -> aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa...',
        ], $output);
    }

    public function testJsonEncodedOutput()
    {
        $testListener = new NaughtyTestListener(Dummy\ArrayCounterFetcher::class);

        $this->startOutputCapture();
        $this->runTestSuites($testListener, [1]);
        $output = $this->finishOutputCapture();

        $this->assertSameLines([
            'TestSuite DummyTestSuite1 is naughty (as per ArrayCounterFetcher)!',
            ' - counter: [0] -> [1]',
        ], $output);
    }

    public function testNonserializableObjectOutput()
    {
        $testListener = new NaughtyTestListener(Dummy\NonserializableObjectFetcher::class);

        $this->startOutputCapture();
        $this->runTestSuites($testListener, [1]);
        $output = $this->finishOutputCapture();

        $this->assertSameLines([
            'TestSuite DummyTestSuite1 is naughty (as per NonserializableObjectFetcher)!',
            ' - counter: Object<stdClass> -> Object<stdClass>',
        ], $output);
    }

    private function createTestSuiteMock($name)
    {
        $mock = $this->createMock(TestSuite::class);
        $mock->expects(static::any())
            ->method('getName')
            ->willReturn($name);

        return $mock;
    }

    /**
     * @param string $name
     *
     * @return MockObject|TestCase
     */
    private function createTestCaseMock($name)
    {
        $mock = $this->createMock(TestCase::class);
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

    /**
     * Simulates run of a PHPUnit:
     *   1. starts "global test suite"
     *   2. executes all the "test suites"
     *   3. within the test suites, executes given count tests
     *
     * @param TestListener $testListener
     * @param array $testCounts An array with counts of tests per test suite. E.g. [5, 2, 7] means execute 3 test suites
     *                          with 5, 2 and 7 tests.
     */
    private function runTestSuites(TestListener $testListener, array $testCounts)
    {
        $testSuiteRoot = $this->createTestSuiteMock('');
        $testListener->startTestSuite($testSuiteRoot);

        foreach ($testCounts as $i => $testCount) {
            $testSuiteName = 'DummyTestSuite' . ($i + 1);
            $testSuite = $this->createTestSuiteMock($testSuiteName);
            $testListener->startTestSuite($testSuite);
            foreach (range(1, $testCount) as $testNumber) {
                $test = $this->createTestCaseMock($testSuiteName . '::testFoo' . $testNumber);
                $testListener->startTest($test);
                $testListener->endTest($test, mt_rand(10, 100));
            }
            $testListener->endTestSuite($testSuite);
        }

        $testListener->endTestSuite($testSuiteRoot);
    }
}
