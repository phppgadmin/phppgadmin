<?php
 /**
  * Function area:       Table
  * Subfunction area:    Rule
  * @author     Augmentum SpikeSource Team
  * @copyright  2005 by Augmentum, Inc.
  */

// Import the precondition class.
if (is_dir('../Public')) {
    require_once '../Public/SetPrecondition.php';
}

/**
 * A test case suite for testing RULE feature in phpPgAdmin
 */
class RulesTest extends PreconditionSet
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
     * TestCaseID: TCR01
     * Create a rule for a table
     */
    public function testCreateRule()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;

        // Go to the Rules page
        $this->assertTrue($this->get("$webUrl/rules.php", array(
                        'server' => $SERVER,
                        'action' => 'create_rule',
                        'database' => $DATABASE,
                        'schema' => 'public',
                        'table' => 'student',
                        'subject' => 'table'))
                    );

        // Set properties for the new rule
        $this->assertTrue($this->setField('name', 'insert_stu_rule'));
        $this->assertTrue($this->setField('event', 'INSERT'));
        $this->assertTrue($this->setField('where', ''));
        $this->assertTrue($this->setField('type', 'NOTHING'));
        $this->assertTrue($this->clickSubmit($lang['strcreate']));

        // Verify if the rule is created correctly.
        $this->assertTrue($this->assertWantedText($lang['strrulecreated']));

        return TRUE;
    }

    /**
     * TestCaseID: TCR02
     * Cancel creating a rule for a table
     */
    public function testCancelCreateRule()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;

        // Go to the Rules page
        $this->assertTrue($this->get("$webUrl/rules.php", array(
                        'server' => $SERVER,
                        'action' => 'create_rule',
                        'database' => $DATABASE,
                        'schema' => 'public',
                        'table' => 'student',
                        'subject' => 'table'))
                    );

        // Set properties for the new rule
        $this->assertTrue($this->setField('name', 'insert_stu_rule'));
        $this->assertTrue($this->setField('event', 'INSERT'));
        $this->assertTrue($this->setField('where', ''));
        $this->assertTrue($this->setField('instead', true));
        $this->assertTrue($this->clickSubmit($lang['strcancel']));

        return TRUE;
    }

    /**
     * TestCaseID: TDR03
     * Cancel the drop rule operation
     */
    public function testCancelDropRule()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;

        // Drop the rule
        $this->assertTrue($this->get("$webUrl/rules.php", array(
            'server' => $SERVER,
            'action' => 'confirm_drop',
            'database' => $DATABASE,
            'schema' => 'public',
            'reltype' => 'table',
            'table' => 'student',
            'subject' => 'rule',
            'rule' => 'insert_stu_rule'))
        );
        $this->assertTrue($this->clickSubmit($lang['strno']));

        return TRUE;
    }

    /**
     * TestCaseID: TDR01
     * Drop a rule from the table
     */
    public function testDropRule()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;

        // Drop the rule
        $this->assertTrue($this->get("$webUrl/rules.php", array(
            'server' => $SERVER,
            'action' => 'confirm_drop',
            'database' => $DATABASE,
            'schema' => 'public',
            'reltype' => 'table',
            'table' => 'student',
            'subject' => 'rule',
            'rule' => 'insert_stu_rule'))
        );
        $this->assertTrue($this->clickSubmit($lang['stryes']));
        // Verify if the rule is dropped correctly.
        $this->assertTrue($this->assertWantedText($lang['strruledropped']));

        return TRUE;
    }

    /**
     * TestCaseID: TDR02
     * Drop a rule from the table witch CASCADE checked
     */
    public function testDropRuleWithCascade()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;
        global $POWER_USER_NAME;
        global $POWER_USER_PASSWORD;

        // Go to the Rules page
        $this->assertTrue($this->get("$webUrl/rules.php", array(
                        'server' => $SERVER,
                        'action' => 'create_rule',
                        'database' => $DATABASE,
                        'schema' => 'public',
                        'table' => 'student',
                        'subject' => 'table'))
                    );

        // Set properties for the new rule
        $this->assertTrue($this->setField('name', 'insert_stu_rule'));
        $this->assertTrue($this->setField('event', 'INSERT'));
        $this->assertTrue($this->setField('where', ''));
        $this->assertTrue($this->setField('type', 'SOMETHING'));
        $this->assertTrue($this->clickSubmit($lang['strcreate']));

        // Verify if the rule is created correctly.
        $this->assertTrue($this->assertWantedText($lang['strrulecreated']));

        $this->logout();
        $this->login($POWER_USER_NAME, $POWER_USER_PASSWORD,
                     "$webUrl/login.php");

        // Drop the rule
        $this->assertTrue($this->get("$webUrl/rules.php", array(
            'server' => $SERVER,
            'action' => 'confirm_drop',
            'database' => $DATABASE,
            'schema' => 'public',
            'reltype' => 'table',
            'table' => 'student',
            'subject' => 'rule',
            'rule' => 'insert_stu_rule'))
        );
        $this->assertTrue($this->setField('cascade', true));
        $this->assertTrue($this->clickSubmit($lang['stryes']));
        // Verify if the rule is dropped correctly.
        $this->assertTrue($this->assertWantedText($lang['strruledropped']));

        return TRUE;
    }
}
