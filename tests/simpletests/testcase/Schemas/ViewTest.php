<?php
 /**
  * Function area:       Schemas
  * Subfunction area:    View
  * @author     Augmentum SpikeSource Team 
  * @copyright  2005 by Augmentum, Inc.
  */
 
// Import the precondition class.
if(is_dir('../Public')) {
    require_once('../Public/SetPrecondition.php');
}


/**
 * A test case suite for testing VIEW feature in phpPgAdmin, including
 * cases for creating, altering, browsing and dropping views.
 */
class ViewTest extends PreconditionSet
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
     * Clean up all the result. 
     */
    function tearDown()
    {
        // Logout from the system.
        $this->logout(); 
        
        return TRUE;
    }
    
    
    /**
     * TestCaseID: HCV01
     * Test creating a view in an existing table directly.
     */
    function testCreateViewDirectly()
    {
    	
        global $webUrl;
        global $lang, $SERVER, $DATABASE;
        
        $this->createTable($DATABASE, 'public', 'viewtest', '3');       

        // Turn to the "Create view" page.
		$this->assertTrue($this->get("$webUrl/views.php", array(
			            'server' => $SERVER,
						'action' => 'create',
						'database' => $DATABASE,
						'schema' => 'public'))
					);

        // Enter the definition of the view.        
        $this->assertTrue($this->setField('formView', 'createviewdirectly'));        
        $this->assertTrue($this->setField('formDefinition', 
                                          'select field0, field1 from viewtest'));
        $this->assertTrue($this->setField('formComment',  'Create View Directly.'));
         
        // Click "Create" button to create the view.
        $this->assertTrue($this->clickSubmit($lang['strcreate']));    
         
        // Verify whether the view is created correctly.
        $this->assertTrue($this->assertWantedText($lang['strviewcreated']));
        
        return TRUE;   
    }
     
     
    /** 
     * TestCaseID: HCV02
     * This test case test for creating a view in an existing table with wizard.
     */
    function testCreateViewWithWizard()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;
         
        // Turn to the "Create view with wizard" page.
		$this->assertTrue($this->get("$webUrl/views.php", array(
			            'server' => $SERVER,
						'action' => 'wiz_create',
						'database' => $DATABASE,
						'schema' => 'public'))
					);

        // Select the table.        
        $this->assertTrue($this->setField('formTables[]',  array('public.viewtest')));    
       
        //$this->assertTrue($this->clickSubmit($lang['strnext']));
        // If we do not hardcoded it here, it will cause fail. Encoding issue.
        $this->assertTrue($this->clickSubmit('Next >'));

        $this->assertTrue($this->setField('formView', 'createwitwizard'));
        $this->assertTrue($this->setField('formComment',
                                          'Create the view with wizard.'));        
        
        // Click "Create" button for creating the view.
        $this->assertTrue($this->clickSubmit($lang['strcreate'])); 
        
        return TRUE;   
    }
     
     
    /**
     * TestCaseID: HCV03
     * Test creating a view in an existing table directly.
     * But in this test case, some illegal data will be input.
     */
    function testCreateViewDirectlyNegative()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;
        
        // Turn to the "Create view" page.
		$this->assertTrue($this->get("$webUrl/views.php", array(
			            'server' => $SERVER,
						'action' => 'create',
						'database' => $DATABASE,
						'schema' => 'public'))
					);
         
        // Enter the definition of the view.        
        $this->assertTrue($this->setField('formView', 'createviewdirectly'));        
        $this->assertTrue($this->setField('formDefinition', 
                                          'select firstfield, secondfield from noexisttable'));
        $this->assertTrue($this->setField('formComment', 
                                          'Create view directly use illegal definition.'));        
         
        // Click "Create" button to create the view.
        $this->assertTrue($this->clickSubmit($lang['strcreate']));    
         
        // Verify whether the view is created correctly.
        $this->assertTrue($this->assertWantedText($lang['strviewcreatedbad']));
        
        return TRUE;   
    } 
     
     
    /**
     * TestCaseID: HCV04
     * Test creating a view in an existing table with wizard.
     * But in this test case, some illegal data will be input.
     */
    function  testCreateViewWithWizardNegation()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;
         
        // Turn to the "Create view with wizard" page.
		$this->assertTrue($this->get("$webUrl/views.php", array(
			            'server' => $SERVER,
						'action' => 'wiz_create',
						'database' => $DATABASE,
						'schema' => 'public'))
					);
                 
        // Select the table.        
        $this->assertTrue($this->setField('formTables[]', array('public.viewtest')));
        //$this->assertTrue($this->clickSubmit($lang['strnext']));
        // If we do not hardcoded it here, it will cause fail. Encoding issue.
        $this->assertTrue($this->clickSubmit('Next >'));
         
        $this->assertTrue($this->setField('formView', 'createwitwizard'));
        $this->assertTrue($this->setField('formComment',
                                          'Create the view do not select any field with wizard.'));        

        // Click "Create" button for creating the view.
        $this->assertTrue($this->clickSubmit($lang['strcreate']));
        $this->assertTrue($this->assertWantedText($lang['strviewneedsfields']));
                
        return TRUE;   
    }
     
     
    /**
     * TestCaseID: HBV01
     * Test browsing an existing view with illegal data.
     */
    function testBrowseView()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;
         
        // Browse the view "createviewdirectly" created just now.
		$this->assertTrue($this->get("$webUrl/display.php", array(
			            'server' => $SERVER,
						'database' => $DATABASE,
						'schema' => 'public',
						'subject' => 'view',
						'return_url' => 'views.php%3Fdatabase%3Dtest%26amp%3Bschema%3Dpublic',
						'return_desc' => 'Back',
						'view' => 'createviewdirectly'))
					); 
                  
        // Click the links in the view-display page.
        $this->assertTrue($this->clickLink($lang['strexpand']));
        $this->assertTrue($this->clickLink($lang['strcollapse']));
        $this->assertTrue($this->clickLink($lang['strrefresh']));
        $this->assertTrue($this->clickLink($lang['strback']));
        
        return TRUE;   
    } 
     
     
    /**
     * TestCaseID: HSV01
     * Test selecting rows from an existing view.
     */
    function testSelectView()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;
        
		$this->assertTrue($this->get("$webUrl/views.php", array(
			            'server' => $SERVER,
						'action' => 'confselectrows',
						'database' => $DATABASE,
						'schema' => 'public',
						'view' => 'createviewdirectly'))
					); 
     
        // Enter the query conditions.
        $this->assertTrue($this->setField('show[field0]', TRUE)); 
        $this->assertTrue($this->setField('show[field1]', TRUE)); 
        $this->assertTrue($this->setField('values[field0]', 'yes'));
        $this->assertTrue($this->setField('values[field1]', 'no'));
                    
        $this->assertTrue($this->clickSubmit($lang['strselect']));
        $this->assertTrue($this->assertWantedText($lang['strnodata']));
        
        return TRUE;   
    } 
     
    
    /**
     * TestCaseID: HAV01
     * Alter the properties of an existing view.
     */ 
    function testAlterView()
     {
         global $webUrl;
        global $lang, $SERVER, $DATABASE;
         
         // Turn to the view display page.
		 $this->assertTrue($this->get("$webUrl/views.php", array(
			             'server' => $SERVER,
						 'database' => $DATABASE,
						 'schema' => 'public',
						 'subject' => 'schema'))
					 );
         // Select a view.
         $this->assertTrue($this->clickLink('createviewdirectly'));
         // Select a column.
         $this->assertTrue($this->clickLink($lang['stralter']));  
         // Alter the properties of the view.
         $this->assertTrue($this->setField('field', 'newfield'));
         $this->assertTrue($this->setField('comment', 'alterintestcase')); 
        
         // Click the "Alter" button to alter the properties.  
         $this->assertTrue($this->clickSubmit($lang['stralter']));         
         // Verify whether the properties are altered.  
         $this->assertTrue($this->assertWantedText($lang['strcolumnaltered'])); 
        
         return TRUE;
     }
     
     
    /**
     * TestCaseID: HAD01
     * Alter the definition of an existing view.
     */
    function testAlterDefinition()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;
         
        // Turn to the view display page.
		$this->assertTrue($this->get("$webUrl/views.php", array(
			            'server' => $SERVER,
						'database' => $DATABASE,
						'schema' => 'public',
						'subject' => 'schema'))
					);
        // Select a view.
        $this->assertTrue($this->clickLink('createviewdirectly'));
        // Browse the definition of the view.
        $this->assertTrue($this->clickLink("{$lang['strdefinition']} {$lang['strdefinition']}"));
        $this->assertTrue($this->clickLink($lang['stralter']));
        
        // Alter the definition here.
        $this->assertTrue($this->setField('formDefinition', 
                                          'SELECT viewtest.field0 AS newfield, ' .
                                          'viewtest.field2 AS field1 FROM viewtest;'));
        $this->assertTrue($this->setField('formComment', 'The definition be altered.')); 
            
        // Click the "Alter" button.
        $this->assertTrue($this->clickSubmit($lang['stralter']));
        //Verify whether the definition is altered.
        $this->assertTrue($this->assertWantedText($lang['strviewupdated'])); 

        return TRUE;
    }
     
      
    /**
     * TestCaseID: HDV01
     * Test dropping a view.
     */
    function testDropView()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;
         
        // Drop the view which was created in the last case.
		$this->assertTrue($this->get("$webUrl/views.php", array(
			            'server' => $SERVER,
						'action' => 'confirm_drop',
						'database' => $DATABASE,
						'schema' => 'public',
						'view' => 'createviewdirectly'))
					);
                
        $this->assertTrue($this->setField('cascade', TRUE));         
        $this->assertTrue($this->clickSubmit($lang['strdrop']));
         
        // Verify whether the view is dropped successfully.
        $this->assertTrue($this->assertWantedText($lang['strviewdropped']));
        
        // Drop the table which is created in setUp().
        $this->dropTable($DATABASE, 'viewtest', 'public');
        
        return TRUE;   
    } 
}
?>
