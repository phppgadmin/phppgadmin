<?php
/**
 * Function area: Common manipulation
 * Sub function area: Import
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
 * This class is to test the import function.
 * It includes importing for XML format data and incorrect text format data.
 * 
 * This part test cases cannot pass because simpletest does not support upload file.
 */
class ImportTest extends PreconditionSet 
{
    // Declare the member variables for table name and the data file's path.
    private $_tableName = 'student';
    private $_dataFilePath = '.';
    
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
        // Clear the data and logout.
        $this->emptyTable();
        $this->logout();
        
        return TRUE;
    }
   
   
    /*
     * TestCaseID: CID01
     * Test to import XML format data into the table.
     * 
     * This test case will failed because SimpleTest1.0 doesn't support upload file.
     */
    function testXMLData() 
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;
        
        $this->_dataFilePath = getcwd() . '/./data/';
        
        // Turn to the import data page.
		$this->assertTrue($this->get("$webUrl/tblproperties.php", array(
			'server' => $SERVER,
			'database' => $DATABASE,
			'schema' => 'public',
			'table' => $this->_tableName,
			'subject' => 'table',
			'action' => 'import'))
		);

        // Enter information for importing the data.
        $this->assertTrue($this->setField('format', 'XML'));
        $this->assertTrue($this->setField('source', $this->_dataFilePath . $this->_tableName . '.xml'));
        
        // Then submit and verify it.
        $this->assertTrue($this->clickSubmit($lang['strimport']));
        // This assert will failed because SimpleTest1.0 doesn't support upload file.
        $this->assertWantedText($lang['strfileimported']);
        
        return TRUE;
    }
    
    /*
     * TestCaseID: CID02
     * Test to import incorrect text format data into the table.
     * 
     * This test case will failed because SimpleTest1.0 doesn't support upload file.
     */
    function testIncorectTxtData() 
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;
        
        $this->_dataFilePath = getcwd() . '/./data/';
        // Turn to the import data page.
		$this->assertTrue($this->get("$webUrl/tblproperties.php", array(
			            'server' => $SERVER,
						'database' => $DATABASE,
						'schema' => 'public',
						'table' => $this->_tableName,
						'subject' => 'table',
						'action' => 'import'))
					);
       
        // Enter information for importing the data.
        $this->assertTrue($this->setField('format', $lang['strauto']));
        $this->assertTrue($this->setField('source', $this->_dataFilePath . $this->_tableName . '.txt'));
        
        // Then submit and verify it.
        $this->assertTrue($this->clickSubmit($lang['strimport']));
        // This assert will failed because SimpleTest1.0 doesn't support upload file.
        $this->assertWantedText(sprintf($lang['strimporterrorline'], 2));
        
        return TRUE;
    }
    
    /*
     * Help to empty the table's data.
     */
    function emptyTable()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;
        
		$this->assertTrue($this->get("$webUrl/tables.php", array(
			            'server' => $SERVER,
						'action' => 'confirm_empty',
						'database' => $DATABASE,
						'schema' => 'public',
						'table' => $this->_tableName))
					);
        $this->assertTrue($this->clickSubmit($lang['strempty']));
    	
        return TRUE;
    }
    
}
?>
