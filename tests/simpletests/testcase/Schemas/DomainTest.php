<?php
 /**
  * Function area:       Schemas
  * Subfunction area:    Domain
  * @author     Augmentum SpikeSource Team 
  * @copyright  2005 by Augmentum, Inc.
  */
 
// Import the precondition class.
if(is_dir('../Public')) {
    require_once('../Public/SetPrecondition.php');
}


/**
 * A test case suite for testing DOMAIN feature in phpPgAdmin, including
 * cases for creating, altering and dropping domain.
 */
class DomainTest extends PreconditionSet
{
    /**
     * Set up the precondition. 
     */
    function setUp()
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
     * Cleans up all the result. 
     */
    function tearDown()
    {
        // Logout from the system.
        $this->logout(); 
        
        return TRUE;         
    }
    
    /**
     * TestCaseID: HCD01
     * Create a domain.
     */
    function testCreateDomain()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;
        
        // Turn to the "Create domain" page.
		$this->assertTrue($this->get("$webUrl/domains.php", array(
			            'server' => $SERVER,
						'action' => 'create',
						'database' => $DATABASE,
						'schema' => 'public'))
					);
                
        // Enter the detail information of the new domain.
        $this->assertTrue($this->setField('domname', 'spikedomain'));
        $this->assertTrue($this->setField('domtype', 'bigint'));
        $this->assertTrue($this->setField('domarray', '[ ]'));
        $this->assertTrue($this->setField('domnotnull', TRUE));

        // Click the "Create" button to create the domain.
        $this->assertTrue($this->clickSubmit($lang['strcreate']));
        
        // Verify whether the domain is created successfully.
        $this->assertTrue($this->assertWantedText($lang['strdomaincreated'])); 
        
        return TRUE;       
    } 
    
    /**
     * TestCaseID: HAD01
     * Alter the definition of a domain.
     */
    function testAlterDomain()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;
        
        // Display the domain which is to be altered.
		$this->assertTrue($this->get("$webUrl/domains.php", array(
			            'server' => $SERVER,
						'action' => 'properties',
						'database' => $DATABASE,
						'schema' => 'public',
						'domain' => 'spikedomain'))
					);

        $this->assertTrue($this->clickLink($lang['stralter']));
        $this->assertTrue($this->setField('domowner', 'tester'));    

        // Click the "Alter" button to alter the domain.
        $this->assertTrue($this->clickSubmit($lang['stralter']));
        // Verify whether the domain is altered successfully.
        $this->assertTrue($this->assertWantedText($lang['strdomainaltered']));    

        return TRUE;   
    } 

    /**
     * TestCaseID: HAC01
     * Add check to an existing domain.
     */
    function testAddCheck()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;

        // Display the domain to be be altered.
		$this->assertTrue($this->get("$webUrl/domains.php", array(
			            'server' => $SERVER,
						'action' => 'properties',
						'database' => $DATABASE,
						'schema' => 'public',
						'domain' => 'spikedomain'))
					);

        $this->assertTrue($this->clickLink($lang['straddcheck']));

        // Enter the check's definition.
        $this->assertTrue($this->setField('name', 'newcheck'));
        $this->assertTrue($this->setField('definition',
                                          'VALUE[0] > 3'));

        // Click the "Add" button add a new check.
        $this->assertTrue($this->clickSubmit($lang['stradd']));

        // Verify whether the new check added.
        $this->assertTrue($this->assertWantedText($lang['strcheckadded']));    

        return TRUE;   
    }    

    /**
     * TestCaseID: HDC01
     * Drops an existing constraint of a domain.
     */
    function testDropConstraint()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;

        // Turn to the domains-display page.
		$this->assertTrue($this->get("$webUrl/domains.php", array(
			            'server' => $SERVER,
						'database' => $DATABASE,
						'schema' => 'public&'))
					);

        // Display the specified damain.
        $this->assertTrue($this->clickLink('spikedomain'));

        // Drop the constraint.
		$this->assertTrue($this->get("$webUrl/domains.php", array(
			            'server' => $SERVER,
						'action' => 'confirm_drop_con',
						'database' => $DATABASE,
						'schema' => 'public',
						'constraint' => 'newcheck',
						'domain' => 'spikedomain',
						'type' => 'c'))
					);

        $this->assertTrue($this->setField('cascade', TRUE));

        // Click the "Drop" button to drop the constraint.
        $this->assertTrue($this->clickSubmit($lang['strdrop']));
        // Verify whether the constraint is dropped successfully.        
        $this->assertTrue($this->assertWantedText($lang['strconstraintdropped']));

        return TRUE;           
    }

    /**
     * TestCaseID: HDD01
     * Drop an existing domain.
     */
    function testDropDomain()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;

        // Turn to the "domains" page.
		$this->assertTrue($this->get("$webUrl/domains.php", array(
			            'server' => $SERVER,
						'database' => $DATABASE,
						'schema' => 'public',
						'subject' => 'schema'))
					);

		$this->assertTrue($this->get("$webUrl/domains.php", array(
			            'server' => $SERVER,
						'action' => 'confirm_drop',
						'database' => $DATABASE,
						'schema' => 'public',
						'domain' => 'spikedomain'))
					);
        $this->assertTrue($this->setField('cascade', TRUE));    
        
        // Click the "Drop" button to drop the domain.
        $this->assertTrue($this->clickSubmit($lang['strdrop']));
        // Verify whether the domain is dropped successfully.
        $this->assertTrue($this->assertWantedText($lang['strdomaindropped']));
        
        return TRUE;
    } 
} 
?>
