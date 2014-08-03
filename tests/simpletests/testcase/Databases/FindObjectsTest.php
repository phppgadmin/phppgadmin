<?php
/**
 * Function area     : Database.
 * Sub Function area : FindObjects.
 *
 * @author     Augmentum SpikeSource Team
 * @copyright  Copyright (c) 2005 by Augmentum, Inc.
 */

// Import the precondition class.
if (is_dir('../Public')) {
    require_once '../Public/SetPrecondition.php';
}

/**
 * This class is to test the FindObjects implementation.
 */
class FindObjectsTest extends PreconditionSet
{
    /**
     * Set up the preconditon.
     */
    public function setUp()
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
    public function tearDown()
    {
        // Logout this system.
        $this->logout();

        return TRUE;
    }

    /**
     * TestCaseId: DFO001
     * This test is used to find objects in the search component.
     */
    public function testSimpleFindObject()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;

        // Locate the list page of databases.
        $this->assertTrue($this->get("$webUrl/database.php", array(
            'server' => $SERVER,
            'database' => $DATABASE,
            'subject' => 'database',
            'action' => 'find'))
        );

        $this->assertTrue($this->setField('term', 'student'));
        $this->assertTrue($this->setField('filter', 'All objects'));
        $this->assertTrue($this->clickSubmit ($lang['strfind']));

        return true;
    }

    /**
     * TestCaseId: DFO002
     * This test is used to find objects in the search component.
     */
    public function testFindObjsInSchemas()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;

        // Locate the list page of databases.
        $this->assertTrue($this->get("$webUrl/database.php", array(
            'server' => $SERVER,
            'database' => $DATABASE,
            'subject' => 'database',
            'action' => 'find'))
        );

        $this->assertTrue($this->setField('term', 'student'));
        $this->assertTrue($this->setField('filter', 'Schemas'));
        $this->assertTrue($this->clickSubmit ($lang['strfind']));

        // Locate the list page of databases.
        $this->assertTrue($this->get("$webUrl/database.php", array(
                        'server' => $SERVER,
                        'database' => $DATABASE,
                        'subject' => 'database',
                        'action' => 'find'))
                    );

        $this->assertTrue($this->setField('term', 'student'));
        $this->assertTrue($this->setField('filter', 'Tables'));
        $this->assertTrue($this->clickSubmit ($lang['strfind']));

        // Locate the list page of databases.
        $this->assertTrue($this->get("$webUrl/database.php", array(
                        'server' => $SERVER,
                        'database' => $DATABASE,
                        'subject' => 'database',
                        'action' => 'find'))
                    );

        $this->assertTrue($this->setField('term', 'student'));
        $this->assertTrue($this->setField('filter', 'Views'));
        $this->assertTrue($this->clickSubmit ($lang['strfind']));

        // Locate the list page of databases.
        $this->assertTrue($this->get("$webUrl/database.php", array(
                       'server' => $SERVER,
                       'database' => $DATABASE,
                       'subject' => 'database',
                       'action' => 'find'))
                   );

        $this->assertTrue($this->setField('term', 'student'));
        $this->assertTrue($this->setField('filter', 'Sequences'));
        $this->assertTrue($this->clickSubmit ($lang['strfind']));

        // Locate the list page of databases.
        $this->assertTrue($this->get("$webUrl/database.php", array(
                        'server' => $SERVER,
                        'database' => $DATABASE,
                        'subject' => 'database',
                        'action' => 'find'))
                    );

        $this->assertTrue($this->setField('term', 'student'));
        $this->assertTrue($this->setField('filter', 'Columns'));
        $this->assertTrue($this->clickSubmit ($lang['strfind']));

        // Locate the list page of databases.
        $this->assertTrue($this->get("$webUrl/database.php", array(
                        'server' => $SERVER,
                        'database' => $DATABASE,
                        'subject' => 'database',
                        'action' => 'find'))
                    );

        $this->assertTrue($this->setField('term', 'student'));
        $this->assertTrue($this->setField('filter', 'Rules'));
        $this->assertTrue($this->clickSubmit ($lang['strfind']));

        // Locate the list page of databases.
        $this->assertTrue($this->get("$webUrl/database.php", array(
                       'server' => $SERVER,
                       'database' => $DATABASE,
                       'subject' => 'database',
                       'action' => 'find'))
                   );

        $this->assertTrue($this->setField('term', 'student'));
        $this->assertTrue($this->setField('filter', 'Indexes'));
        $this->assertTrue($this->clickSubmit ($lang['strfind']));

        // Locate the list page of databases.
        $this->assertTrue($this->get("$webUrl/database.php", array(
                       'server' => $SERVER,
                       'database' => $DATABASE,
                       'subject' => 'database',
                       'action' => 'find'))
                   );

        $this->assertTrue($this->setField('term', 'student'));
        $this->assertTrue($this->setField('filter', 'Triggers'));
        $this->assertTrue($this->clickSubmit ($lang['strfind']));

        // Locate the list page of databases.
        $this->assertTrue($this->get("$webUrl/database.php", array(
                        'server' => $SERVER,
                        'database' => $DATABASE,
                        'subject' => 'database',
                        'action' => 'find'))
                    );

        $this->assertTrue($this->setField('term', 'student'));
        $this->assertTrue($this->setField('filter', 'Constraints'));
        $this->assertTrue($this->clickSubmit ($lang['strfind']));

        // Locate the list page of databases.
        $this->assertTrue($this->get("$webUrl/database.php", array(
                        'server' => $SERVER,
                        'database' => $DATABASE,
                        'subject' => 'database',
                        'action' => 'find'))
                    );

        $this->assertTrue($this->setField('term', 'student'));
        $this->assertTrue($this->setField('filter', 'Functions'));
        $this->assertTrue($this->clickSubmit ($lang['strfind']));

        // Locate the list page of databases.
        $this->assertTrue($this->get("$webUrl/database.php", array(
                        'server' => $SERVER,
                        'database' => $DATABASE,
                        'subject' => 'database',
                        'action' => 'find'))
                    );

        $this->assertTrue($this->setField('term', 'student'));
        $this->assertTrue($this->setField('filter', 'Domains'));
        $this->assertTrue($this->clickSubmit ($lang['strfind']));

        return true;
    }

    /**
     * TestCaseId: DFO003
     * This test is used to find objects in the search component in top bar.
     */
    public function testFindTopObjects()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;

        // Locate the list page of databases.
        $this->assertTrue($this->get("$webUrl/sqledit.php", array(
            'server' => $SERVER,
            'action' => 'find'))
        );

        $this->assertTrue($this->setField('database', $DATABASE));
        $this->assertTrue($this->setField('term', 'All objects'));
        $this->assertTrue($this->clickSubmit ($lang['strfind']));

        return true;
    }
}
