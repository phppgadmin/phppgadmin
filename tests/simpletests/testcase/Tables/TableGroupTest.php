<?php
 /**
  * Function area:        Table
  * @author     Augmentum SpikeSource Team
  * @copyright  2005 by Augmentum, Inc.
  */

require_once 'ColumnTest.php';
require_once 'IndexesTest.php';
require_once 'ConstraintsTest.php';
require_once 'TriggersTest.php';
require_once 'RulesTest.php';
require_once 'InfoTest.php';
require_once 'DeadlockTest.php';

/**
 *  Group test class to run all test cases in the table function area automatically.
 */
class TableGroupTest extends GroupTest
{
    public function TableGroupTest()
    {
        $this->GroupTest('Table management group test.');
        $this->addTestClass(new ColumnTest());
        $this->addTestClass(new TriggersTest());
        $this->addTestClass(new RulesTest());
        $this->addTestClass(new IndexesTest());
        $this->addTestClass(new InfoTest());
        $this->addTestClass(new ConstraintsTest());
        $this->addTestClass(new DeadlockTest());
    }
}
