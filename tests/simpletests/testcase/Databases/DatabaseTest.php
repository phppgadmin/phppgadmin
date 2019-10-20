<?php
/**
 * Function area     : Database.
 * Sub Function area : DatabaseManagement.
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
 * This class is to test the Database management.
 */
class DatabaseTest extends PreconditionSet
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
     * TestCaseId: DCD001
     * This test is used to create a default database with
     * "LATIN1" character set.
     * 
     * Note:  The open database cannot delete by phpPgAdmin,  so this case
     * can be run only one time.  It needs to change name of database for 
     * next time.
     */
    function testCreateLATIN1DBInSPT()
    {
        global $webUrl;
        global $lang, $SERVER;

        // Locate the list page of databases.
        $this->assertTrue($this->get("$webUrl/all_db.php"));

        // Click the hyperlink of "Create Database".
		$this->assertTrue($this->get("$webUrl/all_db.php", array(
			            'server' => $SERVER,
						'action' => 'create'))
					);

        // Fill the form about creating database.
        $this->assertTrue($this->setfield('formName', 'spikesource1'));
        $this->assertTrue($this->setfield('formEncoding', 'LATIN1'));        
        $this->assertTrue($this->setfield('formSpc', 'pg_default'));
        
        // Click the submit button.
        $this->assertTrue($this->clickSubmit($lang['strcreate']));

        // Verify weather the database has been created.
        // Because the phpPgAdmin cannot drop the currently open database.
        // this test case may be failed 
        // when running the testcase second time without removing the databases.
        $this->assertWantedText($lang['strdatabasecreated']);

        // Release the resource. 
		// XXX In fact, this line doesnot work because of phpPgAdmin's bug.
		// "cannot delete opened database"
        $this->dropDatabase('spikesource1');

        return TRUE;
    }


    /**
     * TestCaseId: DCD002
     * This test is used to create a defined database with other
     * character set "UNICODE".
     * 
     * Note:  The open database cannot delete by phpPgAdmin,  so this case
     * can be run only one time.  It needs to change name of database for 
     * next time.
     */
    function testCreateUNICODEDBInTester()
    {
        global $webUrl;
        global $lang, $SERVER;
        
        // Sleep for a while to wait for the template1 to be available
        sleep(20);
        // Locate the list page of databases.
        $this->assertTrue($this->get("$webUrl/all_db.php"));

        // Click the hyperlink of "Create Database".
		$this->assertTrue($this->get("$webUrl/all_db.php", array(
			            'server' => $SERVER,
						'action' => 'create'))
					);

        // Fill the form about creating database.
        $this->assertTrue($this->setfield('formName', 'spikesource2'));
        $this->assertTrue($this->setfield('formEncoding', 'UTF8'));
        $this->assertTrue($this->setfield('formSpc', 'pg_default'));
        
        // Click the submit button.
        $this->assertTrue($this->clickSubmit($lang['strcreate']));        

        // Verify weather the database has been created.
        // Because the phpPgAdmin cannot drop the currently open database,
        // this test case may be failed 
        // when running the testcase second time without removing the databases.
        $this->assertWantedText($lang['strdatabasecreated']);

        // Release the resource.
		// XXX In fact, this line doesnot work because of phpPgAdmin's bug (?)
		// "cannot delete opened database"
        $this->dropDatabase('spikesource2');

        return TRUE;
    }


    /**
     * TestCaseId: DDD001
     * This test is used to drop a defined database.
     *
     * This test is failed, because the PostgreSQL cannot support deleting
     * an open database currently.
     */
    function testDropDatabase()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;

        // Click the hyperlink of "Create Database".
		$this->assertTrue($this->get("$webUrl/all_db.php", array(
			            'server' => $SERVER,
						'action' => 'confirm_drop',
						'subject' => 'database',
						'database' => $DATABASE,
						'dropdatabase' => $DATABASE ))
					);

        // Click the submit button "Drop" next page.
        $this->assertTrue($this->clickSubmit($lang['strdrop']));

        // Verify weather the database has been dropped.
        // There is an issue about PostgreSQL.  So let me difine the displayed text.
        $this->assertWantedText($lang['strdatabasedropped']);

        // XXX Release the resource.  The lines below failed in deed. (can't delete opened db)
        $this->dropDatabase('SpikeSource1');
        $this->dropDatabase('SpikeSource2');

        return TRUE;
    }
}

?>
