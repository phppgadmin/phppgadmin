<?php
 /**
  * Function area:       Schemas
  * Subfunction area:    Op Classes.
  * @author     Augmentum SpikeSource Team
  * @copyright  2005 by Augmentum, Inc.
  */

// Import the precondition class.
if (is_dir('../Public')) {
    require_once '../Public/SetPrecondition.php';
}

/**
 * A test case suite for testing OP Class feature in phpPgAdmin, including
 * cases for browsing op class.
 */
class OpClassTest extends PreconditionSet
{
    /**
     * Set up the precondition.
     */
    public function setUp()
    {
        global $webUrl;
        global $SUPER_USER_NAME;
        global $SUPER_USER_PASSWORD;

        // Login the system.
        $this->login($SUPER_USER_NAME, $SUPER_USER_PASSWORD,
                     "$webUrl/login.php");

        return TRUE;
    }

    /**
     * Clean up all the result.
     */
    public function tearDown()
    {
        // Logout from the system.
        $this->logout();

        return TRUE;
    }

    /**
     * TestCaseID: HBC01
     * Browse all the op classes.
     */
    public function testBrowseOpClass()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;

        // Turn to schema "pg_catalog" page.
        $this->assertTrue($this->get("$webUrl/opclasses.php", array(
                        'server' => $SERVER,
                        'database' => $DATABASE,
                        'schema' => 'pg_catalog',
                        'subject' => 'schema'))
                    );

        // Verify whether all the op classes are displayed.
        $this->assertTrue($this->assertWantedText($lang['straccessmethod']));

        return TRUE;
    }
}
