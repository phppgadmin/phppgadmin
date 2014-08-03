<?php
/**
 * Function area: Server
 *
 * @author     Augmentum SpikeSource Team
 * @copyright  2005 by Augmentum, Inc.
 */

// Import the test cases.
require_once 'UsersTest.php';
require_once 'GroupsTest.php';
require_once 'ReportsTest.php';
require_once 'TableSpacesTest.php';

/**
 * This class is to test the whole server function area.
 * It includes users/groups/reports/tablespaces management.
 */
class ServerGroupTest extends GroupTest
{
    public function ServerGroupTest()
    {
        $this->GroupTest('Server management group test.');
        $this->addTestClass(new UsersTest());
        $this->addTestClass(new GroupsTest());
        $this->addTestClass(new ReportsTest());
        $this->addTestClass(new TableSpacesTest());

    }
}
