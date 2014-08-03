<?php
 /**
  * Function area:       Table
  * Subfunction area:    Info
  * @author     Augmentum SpikeSource Team
  * @copyright  2005 by Augmentum, Inc.
  */

// Import the precondition class.
if (is_dir('../Public')) {
    require_once '../Public/SetPrecondition.php';
}

/**
 * A test case suite for testing INFO feature in phpPgAdmin
 */
class InfoTest extends PreconditionSet
{
    /**
     * Set up the preconditon.
     */
    public function setUp()
    {
        global $webUrl;
        global $lang;
        global $POWER_USER_NAME;
        global $POWER_USER_PASSWORD;

        $this->login($POWER_USER_NAME, $POWER_USER_PASSWORD,
                     "$webUrl/login.php");

        return TRUE;
    }

    /**
     * Clean up all the result.
     */
    public function tearDown()
    {
        $this->logout();

        return TRUE;
    }

    /**
     * TestCaseID: TSI01
     * List the performance info of the parent table -- student
     */
    public function testListParentTableInfo()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;

        // Go to the Rules page
        $this->assertTrue($this->get("$webUrl/info.php", array(
                        'server' => $SERVER,
                        'database' => $DATABASE,
                        'schema' => 'public',
                        'table' => 'student',
                        'subject' => 'table'))
                    );

        return TRUE;
    }

    /**
     * TestCaseID: TSI02
     * List the performance info of the children table -- college_student
     */
    public function testListChildrenTableInfo()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;

        // Go to the Rules page
        $this->assertTrue($this->get("$webUrl/info.php", array(
                        'server' => $SERVER,
                        'database' => $DATABASE,
                        'schema' => 'public',
                        'table' => 'college_student',
                        'subject' => 'table'))
                    );

        return TRUE;
    }

    /**
     * TestCaseID: TSI03
     * List the performance info of the foreign table -- department
     */
    public function testListForeignTableInfo()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;

        // Go to the Rules page
        $this->assertTrue($this->get("$webUrl/info.php", array(
                        'server' => $SERVER,
                        'database' => $DATABASE,
                        'schema' => 'public',
                        'table' => 'department',
                        'subject' => 'table'))
                    );

        return TRUE;
    }

    /**
     * TestCaseID: TSP01
     * Show the properties of the foreign key constraint
     */
    public function testShowForeignKeyProperties()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;

        // Go to the Rules page
        $this->assertTrue($this->get("$webUrl/info.php?", array(
            'server' => $SERVER,
            'database' => $DATABASE,
            'schema' => 'public',
            'table' => 'department',
            'subject' => 'table'))
        );

        $this->assertTrue($this->clickLink($lang['strproperties']));
        $this->assertWantedText('FOREIGN KEY (dep_id) REFERENCES department(id) ' .
                                'ON UPDATE RESTRICT ON DELETE RESTRICT');

        return TRUE;
    }
}
