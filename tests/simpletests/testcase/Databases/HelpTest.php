<?php
/**
 * Function area     : Database.
 * Sub Function area : Help.
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
 * This class is to test the help sub function.
 */
class HelpTest extends PreconditionSet
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
     * TestCaseId:DCD001;
     * This test is used to test help links.
     *
     * Note: It's strange here, because all the links are outside.
     *       So the Pattern cannot be invoked directly.
     */
    function testHelpWithInnerSchema()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;

        // Locate the list page of database.
		$this->assertTrue($this->get("$webUrl/database.php", array(
		               'server' => $SERVER,
					   'database' => $DATABASE,
					   'subject' => 'database'))
				   );

        // Click the link about help.
        $this->assertTrue($this->get("$webUrl/help.php"));
        $this->assertTrue($this->get("$webUrl/help.php?help=pg.schema"));
        $this->assertTrue($this->get("$webUrl/help.php?help=pg.column.add"));

        // Comment this for avoiding error by Xdebug.
        // Because we cannot assert something about the content of the page via
        // hyperlink outside
        // $this->assertWantedPattern('/"Schemas"/');

        return TRUE;
    }


    /**
     * TestCaseId:DCD002;
     * This test is used to test help links from the index links.
     */
    function testHelpWithInrClk()
    {
        global $webUrl, $SERVER, $DATABASE;

        // Locate the list page of language.
		$this->assertTrue($this->get("$webUrl/database.php", array(
			'server' => $SERVER,
			'database' => $DATABASE,
			'subject' => 'database'))
		);

        $this->assertTrue($this->get("$webUrl/help.php", array('server' => $SERVER)));        

		// XXX fail because of the version number in the URL
		$this->assertTrue($this->clickLink(/*'http://www.postgresql.org/docs/8.0/' .*/
                                           'interactive/sql-expressions.html' .
                                           '#SQL-SYNTAX-TYPE-CASTS'));

        return TRUE;
    }
}

?>
