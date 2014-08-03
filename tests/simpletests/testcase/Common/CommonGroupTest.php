<?php
/**
 * Function area: Common manipulation
 *
 * @author     Augmentum SpikeSource Team
 * @copyright  2005 by Augmentum, Inc.
 */

// Import the test cases.
require_once 'SecurityTest.php';
require_once 'ExportTest.php';
require_once 'ImportTest.php';

/**
 * This class is to test the whole common manipulation function area.
 * It includes security/export/import manipulation.
 */
class CommonGroupTest extends GroupTest
{
    public function CommonGroupTest()
    {
        $this->GroupTest('Common manipulation group test.');
        $this->addTestClass(new SecurityTest());
        $this->addTestClass(new ExportTest());
        $this->addTestClass(new ImportTest());
    }
}
