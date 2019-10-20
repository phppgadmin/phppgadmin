<?php
/**
 * Function area     : Database.
 * Sub Function area : Variables.
 * 
 * @author     Augmentum SpikeSource Team
 * @copyright  Copyright (c) 2005 by Augmentum, Inc.
 */
 
// Import the precondition class.
if (is_dir('../Public'))
{
    require_once('../Public/SetPrecondition.php');
} 

/**
 * This class is to test the Variables about phpPgAdmin implementation.
 */
class VariablesTest extends PreconditionSet 
{
    /**
     * Set up the preconditon.
     */
    function setUp()
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
    function tearDown()
    {
        // Logout this system.
        $this->logout();

        return TRUE;
    }

    
    /**
     * TestCaseId: DVA001
     * This test is used to display the list of Processes.
     */
    function testVariablesList()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;

		$this->assertTrue($this->get("$webUrl/database.php", array(
			            'server' => $SERVER,
						'database' => $DATABASE,
						'subject' => 'database',
						'action' => 'variables'))
					);
        
        $this->assertWantedText($lang['strname']);
        $this->assertWantedText($lang['strsetting']);

        return TRUE;
    }
}

?>
