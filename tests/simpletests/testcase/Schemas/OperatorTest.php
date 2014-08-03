<?php
 /**
  * Function area:       Schemas
  * Subfunction area:    Operator
  * @author     Augmentum SpikeSource Team
  * @copyright  2005 by Augmentum, Inc.
  */

// Import the precondition class.
if (is_dir('../Public')) {
    require_once '../Public/SetPrecondition.php';
}

/**
 * A test case suite for testing OPERATOR feature in phpPgAdmin, including
 * cases for creating, dropping operators and showing operator's properties.
 */
class OperatorTest extends PreconditionSet
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
     * TestCaseID: HCO01
     * Create a operator.
     */
    public function testCreateOperator()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;

        // Turn to "sql" page.
        $this->assertTrue($this->get("$webUrl/database.php", array(
                        'server' => $SERVER,
                        'database' => $DATABASE,
                        'subject' => 'database',
                        'action' => 'sql'))
                    );
        // Enter the definition of the new operator.
        $this->assertTrue($this->setField('query', 'CREATE OPERATOR === (' .
                                          'LEFTARG = box, RIGHTARG = box, PROCEDURE = box_above, ' .
                                          'COMMUTATOR = ==, NEGATOR = !==, RESTRICT = areasel, JOIN ' .
                                          '= areajoinsel);'));

        // Click the button "Go" to create a new operator.
        $this->assertTrue($this->clickSubmit($lang['strgo']));
        // Verify if the operator is created correctly.
        $this->assertTrue($this->assertWantedText($lang['strsqlexecuted']));

        return TRUE;
    }


    /**
     * TestCaseID: HSP01
     * Show the properties of the specified operator.
     */
    public function testShowProperty()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;

        // Turn to "Operators" page.
        $this->assertTrue($this->get("$webUrl/operators.php", array(
                       'server' => $SERVER,
                       'database' => $DATABASE,
                       'schema' => 'public',
                       'subject' => 'schema'))
                   );
        // Show the properties of the operator "===".
        $this->assertTrue($this->clickLink('==='));
        // Check the properties.
        $this->assertTrue($this->assertWantedText('areasel'));

        return TRUE;
    }


    /**
     * TestCaseID: HDO01
     * Drop the operators.
     */
    public function testDropOperator()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;

        // Turn to "Operators" page.
        $this->assertTrue($this->get("$webUrl/operators.php", array(
                       'server' => $SERVER,
                        'database' => $DATABASE,
                        'schema' => 'public',
                        'subject' => 'schema'))
                    );

        // Drop the first operator.
        $this->assertTrue($this->clickLink($lang['strdrop']));
        $this->assertTrue($this->setField('cascade', true));
        $this->assertTrue($this->clickSubmit($lang['strdrop']));
        // Verify whether the operator is dropped correctly.
        $this->assertTrue($this->assertWantedText($lang['stroperatordropped']));

        // Drop the second operator.
        $this->assertTrue($this->clickLink($lang['strdrop']));
        $this->assertTrue($this->setField('cascade', true));
        $this->assertTrue($this->clickSubmit($lang['strdrop']));
        // Verify whether the operator is dropped correctly.
        $this->assertTrue($this->assertWantedText($lang['stroperatordropped']));

        // Drop the third operator.
        $this->assertTrue($this->clickLink($lang['strdrop']));
        $this->assertTrue($this->setField('cascade', true));
        $this->assertTrue($this->clickSubmit($lang['strdrop']));
        // Verify whether the operator is dropped completely.
        $this->assertTrue($this->assertWantedText($lang['strnooperators']));

        return TRUE;
    }
}
