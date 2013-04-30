<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Unittest;

use Scabbia\Extensions\Helpers\String;

/**
 * Unittest Extension: TestFixture Class
 *
 * @package Scabbia
 * @subpackage Unittest
 * @version 1.1.0
 */
abstract class TestFixture
{
    /**
     * @ignore
     */
    public $testStack = array();
    /**
     * @ignore
     */
    public $testReport = array();
    /**
     * @ignore
     */
    public $testExpectations;

    /**
     * @ignore
     */
    public function test()
    {
        $tMe = new \ReflectionClass($this);
        $tMethods = $tMe->getMethods(\ReflectionMethod::IS_PUBLIC);

        $tReservedMethods = array('setUp', 'tearDown');

        foreach ($tMethods as $tMethod) {
            if ($tMethod->class != $tMe->name || in_array($tMethod->name, $tReservedMethods)) {
                continue;
            }

            $this->testUnit($tMe->name . '->' . $tMethod->name . '()', array(&$this, $tMethod->name));
        }
    }

    /**
     * @ignore
     */
    public function testUnit($uName, /* callable */ $uCallback)
    {
        array_push($this->testStack, array('name' => $uName, 'callback' => $uCallback));

        $tException = null;

        $this->testExpectations = array(
            'ignore' => array(),
            'expect' => array()
        );
        $this->setUp();
        try {
            call_user_func($uCallback);
        } catch(\Exception $ex) {
            $tException = $ex;
        }
        $this->tearDown();

        if (!is_null($tException)) {
            foreach ($this->testExpectations['ignore'] as $tExpectation) {
                if (!is_a($tException, $tExpectation)) {
                    continue;
                }

                $this->testAddReport('ignoreException', false, get_class($tException) . ': ' . $tException->getMessage());
                $tException = null;
                break;
            }
        }

        $tExpectations = $this->testExpectations['expect'];
        foreach ($tExpectations as $tExpectationKey => $tExpectation) {
            if (!is_null($tException) && is_a($tException, $tExpectation)) {
                unset($tExpectations[$tExpectationKey]);
                $this->testAddReport('expectException', false, get_class($tException) . ': ' . $tException->getMessage());
                $tException = null;
            }
        }

        foreach ($tExpectations as $tExpectation) {
            $this->testAddReport('expectException', true, $tExpectation);
        }

        if (!is_null($tException)) {
            $this->testAddReport('exception', true, get_class($tException) . ': ' . $tException->getMessage());
        }

        array_pop($this->testStack);
    }

    /**
     * @ignore
     */
    public function testAddReport($uOperation, $uIsFailed, $uMessage = null)
    {
        $tScope = end($this->testStack);

        if (!isset($this->testReport[$tScope['name']])) {
            $this->testReport[$tScope['name']] = array();
        }

        $this->testReport[$tScope['name']][] = array(
            'operation' => $uOperation,
            'failed' => $uIsFailed,
            'message' => $uMessage
        );
    }

    /**
     * @ignore
     */
    public function setUp()
    {

    }

    /**
     * @ignore
     */
    public function tearDown()
    {

    }

    /**
     * @ignore
     */
    public function assertTrue($uCondition, $uMessage = null)
    {
        $this->testAddReport('assertTrue', $uCondition, $uMessage);
    }

    /**
     * @ignore
     */
    public function assertFalse($uCondition, $uMessage = null)
    {
        $this->testAddReport('assertFalse', !$uCondition, $uMessage);
    }

    /**
     * @ignore
     */
    public function assertNull($uVariable, $uMessage = null)
    {
        $this->testAddReport('assertNull', is_null($uVariable), $uMessage);
    }

    /**
     * @ignore
     */
    public function assertNotNull($uVariable, $uMessage = null)
    {
        $this->testAddReport('assertNotNull', !is_null($uVariable), $uMessage);
    }

    /**
     * @ignore
     */
    public function expectException($uExceptionName)
    {
        $this->testExpectations['expect'][] = $uExceptionName;
    }

    /**
     * @ignore
     */
    public function ignoreException($uExceptionName)
    {
        $this->testExpectations['ignore'][] = $uExceptionName;
    }

    /**
     * @ignore
     */
    public function export($tOutput = true)
    {
        return String::vardump($this->testReport, $tOutput);
    }

    /**
     * @ignore
     */
    public function exportHtml()
    {
        foreach ($this->testReport as $tEntryKey => $tEntry) {
            echo '<p>';
            echo '<b>', $tEntryKey, ':</b><br />';
            echo '<ul>';

            $tPassed = true;
            foreach ($tEntry as $tTest) {
                if ($tTest['failed']) {
                    $tPassed = false;
                    echo '<li>';
                    echo '<font color="red">', $tTest['operation'], '</font>';
                    if (!is_null($tTest['message'])) {
                        echo ': ', $tTest['message'];
                    }
                    echo '</li>';
                } else {
                    echo '<li>';
                    echo '<font color="green">', $tTest['operation'], '</font>';
                    if (!is_null($tTest['message'])) {
                        echo ': ', $tTest['message'];
                    }
                    echo '</li>';
                }
            }

            echo '</ul>';

            if (!$tPassed) {
                echo '<font color="red">FAILED</font>';
            } else {
                echo '<font color="green">PASSED</font>';
            }

            echo '</p>';
        }
    }
}
