<?php
 /**
  * Function area:       Schemas
  * Subfunction area:    Sequence
  * @author     Augmentum SpikeSource Team
  * @copyright  2005 by Augmentum, Inc.
  */

// Import the precondition class.
if (is_dir('../Public')) {
    require_once '../Public/SetPrecondition.php';
}

/**
 * A test case suite for testing SEQUENCE feature in phpPgAdmin, including
 * cases for creating, resetting and dropping sequences.
 */
class SequenceTest extends PreconditionSet
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
     * TestCaseID: HCS01
     * Create a sequence.
     */
    public function testCreateSequence()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;

        // Turn to the "Create sequence" page.
        $this->assertTrue($this->get("$webUrl/sequences.php", array(
                        'server' => $SERVER,
                        'action' => 'create',
                        'database' => $DATABASE,
                        'schema' => 'public'))
                    );

        // Enter the detail information of a sequence.
        $this->assertTrue($this->setField('formSequenceName', 'createsequence'));
        $this->assertTrue($this->setField('formIncrement', '1'));
        $this->assertTrue($this->setField('formMinValue', '1000'));
        $this->assertTrue($this->setField('formMaxValue', '10000'));
        $this->assertTrue($this->setField('formStartValue', '1000'));
        $this->assertTrue($this->setField('formCacheValue', '5'));
        $this->assertTrue($this->setField('formCycledValue', true));

        // Click the "Create" button to create a sequence.
        $this->assertTrue($this->clickSubmit($lang['strcreate']));

        // Verify whether the sequence is created successfully.
        $this->assertTrue($this->assertWantedText($lang['strsequencecreated']));

        return TRUE;
    }

    /**
     * TestCaseID: HRS01
     * Reset an existing sequence.
     */
    public function testResetSequence()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;

        // Turn to the sequence-display page.
        $this->assertTrue($this->get("$webUrl/sequences.php", array(
                        'server' => $SERVER,
                        'database' => $DATABASE,
                        'schema' => 'public',
                        'subject' => 'schema'))
                    );
        // Browse the specified sequence.
        $this->assertTrue($this->clickLink('createsequence'));
        // Reset the sequence.
        $this->assertTrue($this->clickLink($lang['strreset']));

        // Verify whether the sequence is reset successfully.
        $this->assertTrue($this->assertWantedText($lang['strsequencereset']));
        // Display all the sequence.
        $this->assertTrue($this->clickLink($lang['strshowallsequences']));

        return TRUE;
    }

    /**
     * TestCaseID: HDS01
     * Drop a sequence.
     */
    public function testDropSequence()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;

        $this->assertTrue($this->get("$webUrl/sequences.php", array(
                        'server' => $SERVER,
                        'action' => 'confirm_drop',
                        'database' => $DATABASE,
                        'schema' => 'public',
                        'sequence' => 'createsequence'))
                    );

        $this->assertTrue($this->setField('cascade', true));
        $this->assertTrue($this->clickSubmit($lang['strdrop']));

        // Verify if the sequence dropped successful.
        $this->assertTrue($this->assertWantedText($lang['strsequencedropped']));

        return TRUE;
    }
}
