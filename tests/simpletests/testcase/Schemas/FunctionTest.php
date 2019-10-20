<?php
 /**
  * Function area:       Schemas
  * Subfunction area:    Function
  * @author     Augmentum SpikeSource Team 
  * @copyright  2005 by Augmentum, Inc.
  */
 
// Import the precondition class.
if(is_dir('../Public')) {
    require_once('../Public/SetPrecondition.php');
}


/**
 * A test case suite for testing FUNCTION feature in phpPgAdmin, including
 * cases for creating, altering and dropping C/Sql/Internal functions.
 */
class FunctionTest extends PreconditionSet
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
     * TestCaseID: HCF01
     * Create a SQL/PL function.
     */
    function testCreatSqlFunction()
    {
        global $webUrl;
        global $lang, $DATABASE, $SERVER;

        // Turn to the "Create SQL/PL function" page.
		$this->assertTrue($this->get("$webUrl/functions.php", array(
			            'server' => $SERVER,
						'action' => 'create',
						'database' => $DATABASE,
						'schema' => 'public'))
					);
          
        // Enter the detail information of a SQL/PL function.
        $this->assertTrue($this->setField('formFunction', 'sqlplfunction')); 
        $this->assertTrue($this->setField('formArguments', 'double precision[], double precision'));
        $this->assertTrue($this->setField('formSetOf', 'SETOF'));
        $this->assertTrue($this->setField('formReturns', 'double precision'));
        $this->assertTrue($this->setField('formArray', '[ ]'));
        $this->assertTrue($this->setField('formLanguage', 'sql'));
        $this->assertTrue($this->setField('formDefinition', 'select $1'));
        $this->assertTrue($this->setField('formProperties[0]', 'VOLATILE'));
        $this->assertTrue($this->setField('formProperties[1]', 'RETURNS NULL ON NULL INPUT'));
        $this->assertTrue($this->setField('formProperties[2]', 'SECURITY INVOKER')); 
 
        // Click the "Create" button to create a function.  
        $this->assertTrue($this->clickSubmit($lang['strcreate']));
 
        // Verify whether the function is created successfully.
        $this->assertTrue($this->assertWantedText($lang['strfunctioncreated'])); 
 
        return TRUE;          
    } 
    
    /**
     * TestCaseID: HCF02
     * Create a internal function.
     */
    function testCreateInternalFunction()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;
        
        // Turn to the "Create internal function" page.
		$this->assertTrue($this->get("$webUrl/functions.php", array(
			            'server' => $SERVER,
						'action' => 'create',
						'language' => 'internal',
						'database' => $DATABASE,
						'schema' => 'public'))
					);
                
        // Enter the detail information of a SQL/PL function.
        $this->assertTrue($this->setField('formFunction', 'internalfunction'));    
        $this->assertTrue($this->setField('formArguments', 'boolean'));
        $this->assertTrue($this->setField('formSetOf', 'SETOF'));
        $this->assertTrue($this->setField('formReturns', 'name'));
        $this->assertTrue($this->setField('formArray', '[ ]'));
        $this->assertTrue($this->setField('formLinkSymbol', 'current_schemas'));
        $this->assertTrue($this->setField('formProperties[0]', 'VOLATILE'));
        $this->assertTrue($this->setField('formProperties[1]', 'RETURNS NULL ON NULL INPUT'));
        $this->assertTrue($this->setField('formProperties[2]', 'SECURITY INVOKER'));    

        // Click the "Create" button to create a function.        
        $this->assertTrue($this->clickSubmit($lang['strcreate']));
        
        // Verify whether the function is created successfully.
        $this->assertTrue($this->assertWantedText($lang['strfunctioncreated']));

        return TRUE;   
    }

    /**
     * TestCaseID: HCF03
     * Create a C function. 
     */
    function testCreateCFunction()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;

		$this->assertTrue($this->get("$webUrl/functions.php", array(
			            'server' => $SERVER,
						'database' => $DATABASE,
						'schema' => 'public'))
					);
        // Turn to the C-function create page.
        $this->assertTrue($this->clickLink($lang['strcreatecfunction']));  
        
        // Enter the definition of the C function.
        $this->assertTrue($this->setField('formFunction', 'cfunction'));
        $this->assertTrue($this->setField('formArguments', 'text'));
        $this->assertTrue($this->setField('formReturns', 'boolean'));
        $cFunLocation = getcwd() . '/../data/CFunction/euc_jp_and_sjis';
        $this->assertTrue($this->setField('formObjectFile', $cFunLocation));
        $this->assertTrue($this->setField('formLinkSymbol', 'mic_to_sjis'));
        $this->assertTrue($this->setField('formProperties[0]', 'VOLATILE'));
        $this->assertTrue($this->setField('formProperties[1]', 'RETURNS NULL ON NULL INPUT'));
        $this->assertTrue($this->setField('formProperties[2]', 'SECURITY DEFINER'));

        // Click the "Create"  button to create the C function.       
        $this->assertTrue($this->clickSubmit($lang['strcreate']));
        // Verify whether the function is created successfully.
        $this->assertTrue($this->assertWantedText($lang['strfunctioncreated']));
        
        return TRUE;         
    }  
    
    
    /**
     * TestCaseID: HAF01
     * Alter the definition of an existing function.
     */
    function testAlterFunction()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;
        
        // Turn to the function-display page.
		$this->assertTrue($this->get("$webUrl/functions.php", array(
			            'server' => $SERVER,
						'database' => $DATABASE,
						'schema' => 'public',
						'subject' => 'schema'))
					);
        
        // Alter the definition of "cfunction".        
        $this->assertTrue($this->clickLink('cfunction (text)'));
        $this->assertTrue($this->clickLink($lang['stralter']));
                
        // Alter the definition of the function.
        $this->assertTrue($this->setField('formProperties[0]', 'IMMUTABLE'));
        $this->assertTrue($this->setField('formProperties[1]', 'CALLED ON NULL INPUT'));
        $this->assertTrue($this->setField('formProperties[2]', 'SECURITY INVOKER'));   
                
        // Click the "Create"  button to alter the function.       
        $this->assertTrue($this->clickSubmit($lang['stralter']));
        // Verify whether the function is updated successfully.
        $this->assertTrue($this->assertWantedText($lang['strfunctionupdated']));             

        return TRUE;    
    } 
    
    
    /**
     * TestCaseID: HDF01
     * Drop an existing function.
     */
    function testDropFunction()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;
        
        // Turn to the function-display page.
		$this->assertTrue($this->get("$webUrl/functions.php", array(
			            'server' => $SERVER,
						'database' => $DATABASE,
						'schema' => 'public',
						'subject' => 'schema'))
					);
       
        // Drop the function "sqlplfunction". 
        $this->assertTrue($this->clickLink('sqlplfunction (double precision[], double precision)'));        
        $this->assertTrue($this->clickLink($lang['strdrop']));        
        $this->assertTrue($this->setField('cascade', TRUE));
        
        // Click the "Drop" button to dorp the function.
        $this->assertTrue($this->clickSubmit($lang['strdrop']));
        // Verify whether the function is dropped successfully.
        $this->assertTrue($this->assertWantedText($lang['strfunctiondropped']));
       
        // Drop the function "cfunction". 
        $this->assertTrue($this->clickLink('cfunction (text)'));        
        $this->assertTrue($this->clickLink($lang['strdrop']));        
        $this->assertTrue($this->setField('cascade', TRUE));
        
        // Click the "Drop" button to drop the function.
        $this->assertTrue($this->clickSubmit($lang['strdrop']));
        // Verify whether the function is dropped successfully.
        $this->assertTrue($this->assertWantedText($lang['strfunctiondropped'])); 
        
        // Drop the function "internalfunction". 
        $this->assertTrue($this->clickLink('internalfunction (boolean)'));          
        $this->assertTrue($this->clickLink($lang['strdrop']));        
        $this->assertTrue($this->setField('cascade', TRUE));
        
        // Click the "Drop" button to drop the function.
        $this->assertTrue($this->clickSubmit($lang['strdrop']));
        // Verify whether the function is dropped successfully.
        $this->assertTrue($this->assertWantedText($lang['strfunctiondropped']));
        
        return TRUE;
    } 
}
?>
