<?php
/**
 * Function area     : Database.
 * Sub Function area : Sql.
 *
 * @author     Augmentum SpikeSource Team
 * @copyright  Copyright (c) 2005 by Augmentum, Inc.
 */

// Import the precondition class.
if (is_dir('../Public')) {
    require_once '../Public/SetPrecondition.php';
}

/**
 * This class is to test the Sql implementation.
 */
class SqlTest extends PreconditionSet
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
     * TestCaseId: DES001
     * This test is used to send the "select" sql script to phpPgAdmin for
     * implementation.
     */
    public function testSimpleSelectSql()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;

        $this->assertTrue($this->get("$webUrl/database.php",
            array('database' => $DATABASE,
                'subject' => 'database',
                'action' => 'sql',
                'server' => $SERVER))
        );
        $this->assertTrue($this->setFieldById(0, "select id from student;"));

        $this->assertTrue($this->clickSubmit($lang['strexecute']));

        return TRUE;
    }

    /**
     * TestCaseId: DES003
     * This test is used to send the "delete" sql script to phpPgAdmin for
     * implementation.
     */
    public function testSimpleDeleteSql()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;

        $this->assertTrue($this->get("$webUrl/database.php", array(
            'server' => $SERVER,
            'database' => $DATABASE,
            'subject' => 'database',
            'action' => 'sql'))
        );
        $this->assertTrue($this->setField('query', 'delete from "student";'));

        $this->assertTrue($this->clickSubmit($lang['strexecute']));

        return TRUE;
    }

    /**
     * TestCaseId: DES002
     * This test is used to send the "insert" sql script to phpPgAdmin for implement.
     */
    public function testSimpleInsertSql()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;

        $this->assertTrue($this->get("$webUrl/database.php", array(
            'server' => $SERVER,
            'database' => $DATABASE,
            'subject' => 'database',
            'action' => 'sql'))
        );
        $this->assertTrue($this->setField('query',
                                          "insert into studen t values " .
                                          "(nextval('public.student_id_seq'::text)" .
                                          ", 'test2', now(), 'test2 is a student.');"));

        $this->assertTrue($this->clickSubmit($lang['strexecute']));

        return true;
    }

    /**
     * TestCaseId: DES004
     * This test is used to send the "update" sql script to phpPgAdmin
     * for implementation.
     */
    public function testSimpleUpdateSql()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;

        $this->assertTrue($this->get("$webUrl/database.php", array(
            'database' => $DATABASE,
            'server' => $SERVER,
            'subject' => 'database',
            'action' => 'sql'))
        );
        $this->assertTrue($this->setField('query',
                                          'update public."student" ' .
                                          'set "birthday" = now();'));

        $this->assertTrue($this->clickSubmit($lang['strexecute']));

        return TRUE;
    }

    /**
     * TestCaseId: DES005
     * This test is used to send the "select"" sql script to PostgreSQL
     * for implementation about "Explain".
     */
    public function testExplain()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;

        $this->assertTrue($this->get("$webUrl/database.php", array(
            'server' => $SERVER,
            'database' => $DATABASE,
            'subject' => 'database',
            'action' => 'sql'))
        );

        $this->assertTrue($this->setField('query',
                                          'select "id" from "student";'));

        $this->assertTrue($this->setField('paginate', true));

        $this->assertTrue($this->clickSubmit($lang['strexplain']));

        // Here $lang['strsqlexecuted'] is not fit for this situation. Because the "%s"
        // make the assertion failed.
        $this->assertWantedText('Total runtime');

        return TRUE;
    }


    /**
     * TestCaseId: DES006
     * This test is used to send the "select" sql script to phpPgAdmin
     * for implementation about "Explain Analyze".
     */
    public function testExplainAnalyze()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;

        $this->assertTrue($this->get("$webUrl/database.php", array(
            'database' => $DATABASE,
            'server' => $SERVER,
            'subject' => 'database',
            'action' => 'sql'))
        );
        $this->assertTrue($this->setField('query',
                                          'select "id" from "student";'));

        $this->assertTrue($this->setField('paginate', true));

        $this->assertTrue($this->clickSubmit($lang['strexplainanalyze']));

        // Here $lang['strsqlexecuted'] is not fit for this situation. Because the "%s"
        // make the assertion failed.
        $this->assertWantedText('Total runtime');

        return TRUE;
    }


    /**
     * TestCaseId: DES007
     * This test is used to send the "select" sql script file to phpPgAdmin
     * for implementation about "upload" sql script.
     *
     * Note: The SimpleTest doesn't support this yet currently.
     *       So this failed.
     */
    public function testUploadSQLFile()
    {

        global $webUrl;
        global $lang, $SERVER, $DATABASE;

        $this->assertTrue($this->get("$webUrl/database.php", array(
            'server' => $SERVER,
            'database' => $DATABASE,
            'subject' => 'database',
            'action' => 'sql'))
        );

        $webServerUrl = getcwd();
        $sqlScriptUrl = getcwd() . "/data/select.sql";

        $this->assertTrue ($this->setField('script', $sqlScriptUrl));

        $this->assertTrue($this->clickSubmit($lang['strexecute']));

        $this->assertWantedText($lang['strsqlexecuted']);

        return TRUE;
    }


    /**
     * TestCaseId: DES009
     * This test is used to send the "select" sql script to the topbar link
     * in phpPgAdmin for implementation.
     */
    public function testSelectTopSQL()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;

        $this->get("$webUrl/sqledit.php", array('action' => 'sql', 'server' => $SERVER));

        $this->assertTrue($this->setField('database', $DATABASE));
        $this->assertTrue($this->setField('query', 'select * from student;'));

        $this->assertTrue($this->clickSubmit($lang['strexecute']));

        $this->assertWantedText($lang['strsqlexecuted']);

        return true;
    }


    /**
     * TestCaseId: DES010;
     * This test is used to send the "select" sql script to the topbar link
     * in phpPgAdmin for implementation.
     */
    public function testResultFromSelectTopSQL()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;

        $this->get("$webUrl/sqledit.php", array('action' => 'sql', 'server' => $SERVER));

        $this->assertTrue($this->setField('database', $DATABASE));
        $this->assertTrue($this->setField('query', 'select * from student;'));
        $this->assertTrue($this->setField('paginate', true));
        $this->assertTrue($this->clickSubmit($lang['strexecute']));

        $this->assertTrue($this->clickLink($lang['strexpand']));
        $this->assertWantedText($lang['strnodata']);

        $this->assertTrue($this->clickLink($lang['strcollapse']));
        $this->assertWantedText($lang['strnodata']);

        $this->assertTrue($this->clickLink($lang['strrefresh']));

        return TRUE;
    }


    /**
     * TestCaseId: DES011
     * This test is used to generate the report by the sql in topbar
     * in phpPgAdmin for implementation.
     */
    public function testReportByTopSql()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;

        $this->assertTrue($this->get("$webUrl/reports.php", array(
            'action' => 'create',
            'server' => $SERVER,
            'db_name' => $DATABASE,
            'report_sql' => 'select id from student;')
        ));

        $this->assertTrue($this->setField('report_name', 'ppasimpletestreport'));
        $this->assertTrue($this->setField('descr', 'ppasimpletest tests'));

        $this->assertTrue($this->clickSubmit($lang['strsave']));

        $this->assertWantedText($lang['strreportcreated']);
    }


    /**
     * TestCaseId: DES012
     * This test is used to download the specified format of
     * report by the sql in topbar in phpPgAdmin for implementation.
     */
    public function testDownloadTopSql()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;

        $this->assertTrue($this->get("$webUrl/dataexport.php", array(
            'server' => $SERVER,
            'query' => 'select+id+from+student%3B',
            'database' => $DATABASE))
        );

        $this->assertTrue($this->setField('d_format', 'XML'));
        $this->assertTrue($this->setField('output', 'show'));

        $this->assertTrue($this->clickSubmit($lang['strexport']));

        // Here anything about xml cannot be found in English.php so hard code.
        $this->assertWantedPattern('/<?xml/');
    }
}
