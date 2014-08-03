<?php
 /**
  * Function area:       Table
  * Subfunction area:    Trigger
  * @author     Augmentum SpikeSource Team
  * @copyright  2005 by Augmentum, Inc.
  */

// Import the precondition class.
if (is_dir('../Public')) {
    require_once '../Public/SetPrecondition.php';
}

/**
 * A test case suite for testing TRIGGER feature in phpPgAdmin, including cases
 * for creating, altering and dropping triggers.
 */
class TriggersTest extends PreconditionSet
{
    /**
     * Set up the preconditon.
     */
    public function setUp()
    {
        global $webUrl;
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
     * TestCaseID: TCT01
     * Add a trigger to the table
     */
    public function testAddTrigger()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;

        // Go to the Triggers page
        $this->assertTrue($this->get("$webUrl/triggers.php", array(
                        'server' => $SERVER,
                        'action' => 'create',
                        'database' => $DATABASE,
                        'schema' => 'public',
                        'table' => 'student'))
                    );

        // Set properties for the new trigger
        $this->assertTrue($this->setField('formTriggerName',
                                          'insert_stu_trigger'));
        $this->assertTrue($this->setField('formExecTime', 'AFTER'));
        $this->assertTrue($this->setField('formEvent', 'INSERT'));
        $this->assertTrue($this->setField('formFunction',
                                          'RI_FKey_check_ins'));
        $this->assertTrue($this->setField('formTriggerArgs', ''));

        $this->assertTrue($this->clickSubmit($lang['strcreate']));
        // Verify if the trigger is created correctly.
        $this->assertTrue($this->assertWantedText($lang['strtriggercreated']));

        return TRUE;
    }

    /**
     * TestCaseID: TCT02
     * Cancel adding a trigger to the table
     */
    public function testCancelAddTrigger()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;

        // Go to the Triggers page
        $this->assertTrue($this->get("$webUrl/triggers.php", array(
                        'server' => $SERVER,
                        'action' => 'create',
                        'database' => $DATABASE,
                        'schema' => 'public',
                        'table' => 'student'))
                    );

        // Set properties for the new trigger
        $this->assertTrue($this->setField('formTriggerName',
                                          'insert_stu_trigger'));
        $this->assertTrue($this->setField('formExecTime', 'AFTER'));
        $this->assertTrue($this->setField('formEvent', 'INSERT'));
        $this->assertTrue($this->setField('formFunction',
                                          'RI_FKey_check_ins'));
        $this->assertTrue($this->setField('formTriggerArgs', ''));
        $this->assertTrue($this->clickSubmit($lang['strcancel']));

        return TRUE;
    }

    /**
     * TestCaseID: TAT02
     * Alter a trigger of the table
     */
    public function testAlterTrigger()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;

        // Alter the trigger
        $this->assertTrue($this->get("$webUrl/triggers.php" , array(
                        'server' => $SERVER,
                        'action' => 'confirm_alter',
                        'database' => $DATABASE,
                        'schema' => 'public',
                        'table' => 'student',
                        'trigger' => 'insert_stu_trigger'))
                    );
        $this->assertTrue($this->setField('name', 'changed_trigger'));
        $this->assertTrue($this->clickSubmit($lang['strok']));
        // Verify if the trigger is altered correctly.
        $this->assertTrue($this->assertWantedText($lang['strtriggeraltered']));

        return TRUE;
    }

    /**
     * TestCaseID: TAT01
     * Cancel altering a trigger of the table
     */
    public function testCancelAlterTrigger()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;

        // Alter the trigger
        $this->assertTrue($this->get("$webUrl/triggers.php", array(
                        'server' => $SERVER,
                        'action' => 'confirm_alter',
                        'database' => $DATABASE,
                        'schema' => 'public',
                        'table' => 'student',
                        'trigger' => 'changed_trigger'))
                    );
        $this->assertTrue($this->setField('name', 'changed_trigger_changed'));
        $this->assertTrue($this->clickSubmit($lang['strcancel']));

        return TRUE;
    }

    /**
     * TestCaseID: TDT01
     * Cancel dropping a trigger from the table
     */
    public function testCancelDropTrigger()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;

        // Drop the trigger
        $this->assertTrue($this->get("$webUrl/triggers.php", array(
                       'server' => $SERVER,
                       'action' => 'confirm_drop',
                       'database' => $DATABASE,
                       'schema' => 'public',
                       'table' => 'student',
                       'trigger' => 'changed_trigger'))
                   );
        $this->assertTrue($this->clickSubmit($lang['strno']));

        return TRUE;
    }

    /**
     * TestCaseID: TDT02
     * Drop a trigger from the table
     */
    public function testDropTrigger()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;

        // Drop the trigger
        $this->assertTrue($this->get("$webUrl/triggers.php", array(
                        'server' => $SERVER,
                        'action' => 'confirm_drop',
                        'database' => $DATABASE,
                        'schema' => 'public',
                        'table' => 'student',
                        'trigger' => 'changed_trigger'))
                    );
        $this->assertTrue($this->setField('cascade', true));
        $this->assertTrue($this->clickSubmit($lang['stryes']));
        // Verify if the trigger is dropped correctly.
        $this->assertTrue($this->assertWantedText($lang['strtriggerdropped']));

        return TRUE;
    }
}
