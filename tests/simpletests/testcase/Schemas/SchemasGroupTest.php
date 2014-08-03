<?php
 /**
  * Function area:		Schema
  * @author     Augmentum SpikeSource Team
  * @copyright  2005 by Augmentum, Inc.
  */

// Import the test case files.
require_once 'TableTest.php';
require_once 'ViewTest.php';
require_once 'SequenceTest.php';
require_once 'FunctionTest.php';
require_once 'DomainTest.php';
require_once 'AggregateTest.php';
require_once 'TypeTest.php';
require_once 'OperatorTest.php';
require_once 'OpClassTest.php';
require_once 'ConversionTest.php';

/**
 *  Group test class to run all test cases in the schema function area automatically.
 */
class SchemasGroupTest extends GroupTest
{
    public function SchemasGroupTest()
    {
        $this->GroupTest('Schema management group test.');

        $this->addTestClass(new TableTest());
        $this->addTestClass(new ViewTest());
        $this->addTestClass(new SequenceTest());
        $this->addTestClass(new FunctionTest());
        $this->addTestClass(new TypeTest());
        $this->addTestClass(new DomainTest());
        $this->addTestClass(new AggregateTest());
        $this->addTestClass(new OperatorTest());
        $this->addTestClass(new OpClassTest());
        $this->addTestClass(new ConversionTest());
    }
}
