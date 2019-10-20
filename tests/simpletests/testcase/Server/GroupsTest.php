<?php
/**
 * Function area: Server
 * Sub function area: Groups
 *
 * @author     Augmentum SpikeSource Team 
 * @copyright  2005 by Augmentum, Inc.
 */

// Import the precondition class.
if(is_dir('../Public')) 
{
    require_once('../Public/SetPrecondition.php');
}

/**
 * This class is to test the group management.
 * It includes create/drop/alter/list groups.
 */
class GroupsTest extends PreconditionSet 
{
    // Declare the member variable for group name.
    private $_groupName = "testgroup";
        
    function setUp()
    {
        global $webUrl;
        global $SUPER_USER_NAME;
        global $SUPER_USER_PASSWORD;
        
        $this->login($SUPER_USER_NAME, $SUPER_USER_PASSWORD, "$webUrl/login.php");
        
        return TRUE;
    }

    function tearDown()
    {
        $this->logout();

        return TRUE;
    }

    /*
     * TestCaseID: SCG01
     * Test to create group. 
     */
    function testCreate() 
    {
        global $webUrl;
        global $POWER_USER_NAME;
        global $NORMAL_USER_NAME;
        global $lang, $SERVER;

        // Turn to create group page.
		$this->assertTrue($this->get("$webUrl/groups.php", array('server' => $SERVER)));
        $this->assertTrue($this->clickLink($lang['strcreategroup']));

        // Enter the information for creating group.
        $this->assertTrue($this->setField('name', $this->_groupName));
        $this->assertTrue($this->setField('members[]', array($POWER_USER_NAME, $NORMAL_USER_NAME)));

        // Then submit and verify it.
        $this->assertTrue($this->clickSubmit($lang['strcreate']));
        $this->assertWantedText($lang['strgroupcreated']);
        $this->assertWantedText($this->_groupName);

        return TRUE;
    }


    /*
     * TestCaseID: SAG01
     * Test to add users to the group.
     */
    function testAddUser() 
    {
        global $webUrl;
        global $SUPER_USER_NAME;
        global $POWER_USER_NAME;
        global $NORMAL_USER_NAME;
        global $lang, $SERVER;
        
        // Turn to the gruop's properties page.
        $this->assertTrue($this->get("$webUrl/groups.php", array('server' => $SERVER)));
		$this->assertTrue($this->get("$webUrl/groups.php",
			array('action' => 'properties',
				'group' => $this->_groupName,
				'server' => $SERVER))
		);
       
        // Select user and add it to the group.
        $this->assertTrue($this->setField('user', $SUPER_USER_NAME));
        $this->assertTrue($this->clickSubmit($lang['straddmember']));
        $this->assertTrue($this->setField('user', $POWER_USER_NAME));
        $this->assertTrue($this->clickSubmit($lang['straddmember']));
       
        // Verify the group's members.
        $this->assertWantedText($SUPER_USER_NAME);
        $this->assertWantedText($POWER_USER_NAME);
        $this->assertWantedText($NORMAL_USER_NAME);
        
        return TRUE;
    }
    
    
    /*
     * TestCaseID: SRG01
     * Test to Remove users from the group.
     */
    function testRemoveUser() 
    {
        global $webUrl;
        global $SUPER_USER_NAME;
        global $POWER_USER_NAME;
        global $NORMAL_USER_NAME;
        global $lang, $SERVER;
        
        // Turn to the group properties page.
        $this->assertTrue($this->get("$webUrl/groups.php", array('server' => $SERVER)));
		$this->assertTrue($this->get("$webUrl/groups.php",
			array('action' => 'properties',
				'group' => $this->_groupName,
				'server' => $SERVER))
		);
       
        // Drop users from the group and verify it.
        $this->assertTrue($this->clickLink($lang['strdrop']));
        $this->assertTrue($this->clickSubmit($lang['strdrop']));
        $this->assertWantedText($lang['strmemberdropped']);
        
        return TRUE;
    }
    
    
    /*
     * TestCaseID: SDG01
     * Test to drop the group. 
     */
    function testDrop() 
    {
        global $webUrl;
        global $lang, $SERVER;
        
        // Turn to the drop group page..
        $this->assertTrue($this->get("$webUrl/groups.php", array('server' => $SERVER)));
		$this->assertTrue($this->get("$webUrl/groups.php",
			array('server' => $SERVER,
				'action' => 'confirm_drop',
			   	'group' => $this->_groupName))
		);
       
        // Confirm to drop the group and verify it.
        $this->assertTrue($this->clickSubmit($lang['strdrop']));
        $this->assertWantedText($lang['strgroupdropped']);
        $this->assertNoUnWantedText($this->_groupName);
        
        return TRUE;
    }
}
?>
