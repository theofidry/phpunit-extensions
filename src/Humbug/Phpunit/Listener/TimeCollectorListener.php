<?php
/**
 * Humbug.
 *
 * @category   Humbug
 *
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 */

namespace Humbug\Phpunit\Listener;

use Humbug\Phpunit\Logger\JsonLogger;
use PHPUnit\Framework\BaseTestListener;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestSuite;

class TimeCollectorListener extends BaseTestListener
{
    private $logger;

    private $rootSuiteNestingLevel = 0;

    protected $rootSuiteName;

    protected $currentSuiteName;

    protected $currentSuiteTime = 0;

    protected $suiteLevel = 0;

    public function __construct(JsonLogger $logger, $rootSuiteNestingLevel = 0)
    {
        $this->logger = $logger;
        $this->rootSuiteNestingLevel = $rootSuiteNestingLevel;
    }

    public function __destruct()
    {
        $this->rootSuiteNestingLevel = null;
        $this->logger = null;
    }

    public function startTestSuite(TestSuite $suite)
    {
        $this->suiteLevel++;
        if (!isset($this->rootSuiteName)) {
            $this->rootSuiteName = $suite->getName();
        }
        $this->currentSuiteName = $suite->getName();
    }

    /**
     * @param Test $test  It must be TestCase, as getName() does not exist on Test but BaseTestListener requires Test.
     * @param float $time
     */
    public function endTest(Test $test, $time)
    {
        $this->currentSuiteTime += $time;
        $this->logger->logTest(
            $this->currentSuiteName,
            $test->getName(),
            $time
        );
    }

    public function endTestSuite(TestSuite $suite)
    {
        /*
         * Only log Level 2 test suites, i.e. your actual test classes. Level 1
         * is the parent root suite(s) defined in the XML config and Level 3 are
         * those hosting data provider tests.
         */
        if ($this->suiteLevel !== (2 + $this->rootSuiteNestingLevel)) {
            $this->suiteLevel--;

            return;
        }
        $this->suiteLevel--;
        $this->logger->logTestSuite(
            $suite->getName(),
            $this->currentSuiteTime
        );
        $this->currentSuiteTime = 0;
    }
}
