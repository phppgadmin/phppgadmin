<?php
/**
 * Function area     : Database.
 * Sub Function area : Processes.
 *
 * @author     Augmentum SpikeSource Team
 * @copyright  Copyright (c) 2005 by Augmentum, Inc.
 */

// Import the precondition class.
if (is_dir('../Public')) {
    require_once '../Public/SetPrecondition.php';
}

/**
 * This class is to test the Processes about PostgreSql implementation.
 */
class ProcessesTest extends PreconditionSet
{
    /**
     * Set up the preconditon.
     */
    public function setUp()
    {
        global $webUrl;
        global $SUPER_USER_NAME;
        global $SUPER_USER_PASSWORD;

        $this->login($SUPER_USER_NAME, $SUPER_USER_PASSWORD,
            "$webUrl/login.php");

        return TRUE;
    }

    /**
     * Release the relational resource.
     */
    public function tearDown()
    {
        // Logout this system.
        $this->logout();

        return TRUE;
    }

    /**
     * TestCaseId: DPS001
     * This test is used to test Processes.
     *
     * Note: This sub function is dynamic during the run time.
     */
    public function testProcesses()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;

        $this->assertTrue($this->get("$webUrl/database.php", array(
                       'server' => $SERVER,
                       'database' => $DATABASE,
                       'subject' => 'database',
                       'action' => 'processes'))
                   );

        $this->assertWantedText($lang['strnodata']);

        return TRUE;
    }
}
