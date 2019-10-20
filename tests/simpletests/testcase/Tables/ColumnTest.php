<?php
 /**
  * Function area:       Table
  * Subfunction area:    Column
  * @author     Augmentum SpikeSource Team 
  * @copyright  2005 by Augmentum, Inc.
  */

// Import the precondition class.
if(is_dir('../Public')) 
{
    require_once('../Public/SetPrecondition.php');
}


/**
 * A test case suite for testing COLUMN feature in phpPgAdmin, including
 * cases for adding, altering and dropping columns in a table.
 */
class ColumnTest extends PreconditionSet{
    
    /**
     * Set up the preconditon. 
     */
    function setUp()
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
    function tearDown(){
        $this->logout(); 

        return TRUE;
    }    

    /**
     * TestCaseID: TNC01
     * Add a column to the table
     */ 
    function testAddColumn()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;
        
        // Go to the Columns page
		$this->assertTrue($this->get("$webUrl/tblproperties.php",
			array('action' => 'add_column',
				'database' => $DATABASE,
				'schema' => 'public',
				'table' => 'student',
				'server' => $SERVER))
		);
        
        // Set properties for the new column    
        $this->assertTrue($this->setField('field', 'sid'));        
        $this->assertTrue($this->setField('type', 'integer'));                
        $this->assertTrue($this->clickSubmit($lang['stradd']));
        
        // Verify if the column is created correctly.
		$this->assertTrue($this->assertWantedText($lang['strcolumnadded'])); 
        
        return TRUE;             
    }
    
    
    /**
     * TestCaseID: TNC02
     * Add a column with the same name as the existing one
     */ 
    function testAddColumnWithExistingName()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;

        // Go to the Columns page
		$this->assertTrue($this->get("$webUrl/tblproperties.php", array(
			'server' => $SERVER,
			'action' => 'add_column',
			'database' => $DATABASE,
			'schema' => 'public',
			'table' => 'student'))
		);

        // Set properties for the new column    
        $this->assertTrue($this->setField('field', 'sid'));        
        $this->assertTrue($this->setField('type', 'integer'));                
        $this->assertTrue($this->clickSubmit($lang['stradd']));

        // Make sure the operation failed
        $this->assertTrue($this->assertWantedText($lang['strcolumnaddedbad']));

        return TRUE;               
    }    
    
    /**
     * TestCaseID: TNC03
     * Cancel the add column operation
     */ 
    function testCancelAddColumn()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;
        
        // Go to the Columns page
		$this->assertTrue($this->get("$webUrl/tblproperties.php", array(
			'server' => $SERVER,
			'action' => 'add_column',
			'database' => $DATABASE,
			'schema' => 'public',
			'table' => 'student'))
		);
        
        // Set properties for the new column    
        $this->assertTrue($this->setField('field', 'sid'));        
        $this->assertTrue($this->setField('type', 'integer'));                
        $this->assertTrue($this->clickSubmit($lang['strcancel'])); 
        
        return TRUE;             
    }
    
    
    /**
     * TestCaseID: TAC01
     * Alter a column of the table
     */ 
    function testAlterColumn()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;
        
        // Go to the Columns page
		$this->assertTrue($this->get("$webUrl/colproperties.php", array(
			'server' => $SERVER,
			'action' => 'properties',
			'database' => $DATABASE,
			'schema' => 'public',
			'table' => 'student',
			'column' => 'sid'))
		);
        
        // Set properties for the new column    
        $this->assertTrue($this->setField('field', 'sid'));        
        $this->assertTrue($this->setField('type', 'character')); 
        $this->assertTrue($this->setField('length', '18'));                
        $this->assertTrue($this->clickSubmit($lang['stralter']));
        
        // Verify if the column is altered correctly.
        $this->assertTrue($this->assertWantedText($lang['strcolumnaltered']));
        
        return TRUE;  
    }
    
    
    /**
     * TestCaseID: TAC02
     * Alter a column to be of negative length
     */ 
    function testNegativeLengthColumn()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;
        
        // Go to the Columns page
		$this->assertTrue($this->get("$webUrl/colproperties.php", array(
			'server' => $SERVER,
			'action' => 'properties',
			'database' => $DATABASE,
			'schema' => 'public',
			'table' => 'student',
			'column' => 'sid'))
		);
        
        // Set properties for the new column    
        $this->assertTrue($this->setField('field', 'sid'));        
        $this->assertTrue($this->setField('type', 'character')); 
        $this->assertTrue($this->setField('length', '-2'));                
        $this->assertTrue($this->clickSubmit($lang['stralter']));
        
        // Make sure the alteration failed.
        $this->assertTrue($this->assertWantedText($lang['strcolumnalteredbad'])); 
        
        return TRUE; 
    }
    
    
    /**
     * TestCaseID: TAC03
     * Cancel the alter column operation
     */ 
    function testCancelAlterColumn()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;
        
        // Go to the Columns page
		$this->assertTrue($this->get("$webUrl/colproperties.php", array(
			'server' => $SERVER,
			'action' => 'properties',
			'database' => $DATABASE,
			'schema' => 'public',
			'table' => 'student',
			'column' => 'sid'))
		);
        
        // Set properties for the new column    
        $this->assertTrue($this->setField('field', 'sid'));        
        $this->assertTrue($this->setField('type', 'character')); 
        $this->assertTrue($this->setField('length', '18'));                
        $this->assertTrue($this->clickSubmit($lang['strcancel']));
        
        return TRUE; 
    }
    
    
    /**
     * TestCaseID: TDC03
     * Cancel the drop column operation
     */ 
    function testCancelDropColumn()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;
        
        // Drop the column
		$this->assertTrue($this->get("$webUrl/tblproperties.php", array(
			'server' => $SERVER,
			'action' => 'confirm_drop',
			'database' => $DATABASE, 
			'schema' => 'public',
			'table' => 'student',
			'column' => 'sid'))
		);
        $this->assertTrue($this->clickSubmit($lang['strcancel']));
        
        return TRUE; 
    } 
    
    
    /**
     * TestCaseID: TDC01
     * Drop a column from the table
     */ 
    function testDropColumn()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;
        
        // Drop the column
		$this->assertTrue($this->get("$webUrl/tblproperties.php", array(
			'server' => $SERVER,
			'action' => 'confirm_drop',
			'database' => $DATABASE,
			'schema' => 'public',
			'table' => 'student',
			'column' => 'sid'))
		);
        $this->assertTrue($this->clickSubmit($lang['strdrop']));
        // Verify if the column is dropped correctly.
        $this->assertTrue($this->assertWantedText($lang['strcolumndropped']));
        
        return TRUE;          
    } 
    
    
    /**
     * TestCaseID: TDC02
     * Drop a column which "CASCADE" checked
     */ 
    function testDropColumnWithCascade()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;
        global $POWER_USER_NAME;
        global $POWER_USER_PASSWORD;        
        
        // Go to the Columns page
		$this->assertTrue($this->get("$webUrl/tblproperties.php", array(
			'server' => $SERVER,
			'action' => 'add_column',
			'database' => $DATABASE,
			'schema' => 'public',
			'table' => 'student'))
		);
        
        // Set properties for the new column    
        $this->assertTrue($this->setField('field', 'sid'));        
        $this->assertTrue($this->setField('type', 'integer'));                
        $this->assertTrue($this->clickSubmit($lang['stradd']));
        
        // Verify if the column is created correctly.
        $this->assertTrue($this->assertWantedText($lang['strcolumnadded'])); 
        
        $this->logout();        
        $this->login($POWER_USER_NAME, $POWER_USER_PASSWORD, 
                     "$webUrl/login.php");        
        
        // Drop the column with CASCADE checked
		$this->assertTrue($this->get("$webUrl/tblproperties.php" , array(
			'server' => $SERVER,
			'action' => 'confirm_drop',
			'database' => $DATABASE,
			'schema' => 'public',
			'table' => 'student',
			'column' => 'sid'))
		);
        $this->assertTrue($this->setField('cascade', TRUE));
        $this->assertTrue($this->clickSubmit($lang['strdrop']));
        // Verify if the column is dropped correctly.
        $this->assertTrue($this->assertWantedText($lang['strcolumndropped']));
        
        return TRUE;  
    }                       
}
?>
