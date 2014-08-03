<?php
/**
 * Function area: Common manipulation
 * Sub function area: Export
 *
 * @author     Augmentum SpikeSource Team
 * @copyright  2005 by Augmentum, Inc.
 */

// Import the precondition class.
if (is_dir('../Public')) {
    require_once '../Public/SetPrecondition.php';
}

/**
 * This class is to test the export function.
 * It includes server/database/table/view's export function.
 */
class ExportTest extends PreconditionSet
{
    public function setUp()
    {
        global $webUrl;
        global $SUPER_USER_NAME;
        global $SUPER_USER_PASSWORD;

        $this->login($SUPER_USER_NAME, $SUPER_USER_PASSWORD, "$webUrl/login.php");

        return TRUE;
    }


    public function tearDown()
    {
        $this->logout();

        return TRUE;
    }


    /*
     * TestCaseID: CED01
     * Test to export server data with "COPY" format.
     */
    public function testServerDataCopyShow()
    {
        global $webUrl, $lang, $SERVER, $DATABASE;

        // Turn to the export data page.
        $this->assertTrue($this->get("$webUrl/all_db.php", array(
            'action' => 'export',
            'server' => $SERVER))
        );

        // Enter information for exporting the data.
        $this->assertTrue($this->setField('what', 'dataonly'));
        $this->assertTrue($this->setField('d_format', 'COPY'));
        $this->assertTrue($this->setField('output', 'show'));

        //Then submit and verify it.
        $this->assertTrue($this->clickSubmit($lang['strexport']));
        $this->assertWantedText("connect $DATABASE");

        return TRUE;
    }


    /*
     * TestCaseID: CED02
     * Test to export server structure with "SQL" format.
     */
    public function testServerStructureSQLDownload()
    {
        global $webUrl, $lang, $SERVER, $DATABASE;

        // Turn to the export data page.
        $this->assertTrue($this->get("$webUrl/all_db.php", array(
            'action' => 'export',
            'server' => $SERVER))
        );

        // Enter information for exporting the data.
        $this->assertTrue($this->setField('what', 'structureonly'));
        $this->assertTrue($this->setField('d_format', 'SQL'));
        $this->assertTrue($this->setField('output', 'download'));

        //Then submit and verify it.
        $this->assertTrue($this->clickSubmit($lang['strexport']));
        $this->assertWantedText("connect $DATABASE");

        return TRUE;
    }

    /*
     * TestCaseID: CED03
     * Test to export database data with "SQL" format.
     */
    public function testDatabaseDataSQLShow()
    {
        global $webUrl, $lang, $SERVER, $DATABASE;

        // Turn to the export data page.
        $this->assertTrue($this->get("$webUrl/database.php", array(
            'server' => $SERVER,
            'database' => $DATABASE,
            'subject' => 'database',
            'action' => 'export'))
        );

        // Enter information for exporting the data.
        $this->assertTrue($this->setField('what', 'dataonly'));
        $this->assertTrue($this->setField('d_format', 'SQL'));
        $this->assertTrue($this->setField('output', 'show'));

        //Then submit and verify it.
        $this->assertTrue($this->clickSubmit($lang['strexport']));
        $this->assertWantedText('Data for Name: student');

        return TRUE;
    }


    /*
     * TestCaseID: CED04
     * Test to export database structure with "COPY" format.
     */
    public function testDatabaseStructureCOPYDownload()
    {
        global $webUrl, $lang, $SERVER, $DATABASE;

        // Turn to the export data page.
        $this->assertTrue($this->get("$webUrl/database.php", array(
            'server' => $SERVER,
            'database' => $DATABASE,
            'subject' => 'database',
            'action' => 'export'))
        );

        // Enter information for exporting the data.
        $this->assertTrue($this->setField('what', 'structureonly'));
        $this->assertTrue($this->setField('d_format', 'COPY'));
        $this->assertTrue($this->setField('output', 'download'));

        //Then submit and verify it.
        $this->assertTrue($this->clickSubmit($lang['strexport']));
        $this->assertWantedText('CREATE TABLE student');

        return TRUE;
    }

    /*
     * TestCaseID: CED05
     * Test to export table data with "XML" format.
     *
     * This test case need insert one row data firstly.
     * And the data will be removed in the end of the test case.
     */
    public function testTableDataShow()
    {
        global $webUrl, $lang, $SERVER, $DATABASE;

        // Turn to the "Insert row" interface.
        $this->assertTrue($this->get("$webUrl/tables.php", array(
            'server' => $SERVER,
            'action' => 'confinsertrow',
            'database' => $DATABASE,
            'schema' => 'public',
            'table' => 'student'))
        );
        // Set the value of the fields.
        $this->assertTrue($this->setField('values[name]', 'testname'));
        $this->assertTrue($this->setField('values[birthday]', '2005-05-31'));
        $this->assertTrue($this->setField('values[resume]', 'test resume'));

        // Click the "Insert" button insert a row.
        $this->assertTrue($this->clickSubmit($lang['strinsert']));

        // Verify if the row insert successful.
        $this->assertTrue($this->assertWantedText($lang['strrowinserted']));

        // Turn to the export data page.
        $this->assertTrue($this->get("$webUrl/tblproperties.php", array(
            'server' => $SERVER,
            'database' => $DATABASE,
            'schema' => 'public',
            'table' => 'student',
            'subject' => 'table',
            'action' => 'export'))
        );
        // Enter information for export the data.
        $this->assertTrue($this->setField('what', 'dataonly'));
        $this->assertTrue($this->setField('d_format', 'XML'));
        $this->assertTrue($this->setField('output', 'show'));

        // Then submit and verify it.
        $this->assertTrue($this->clickSubmit($lang['strexport']));
        $this->assertWantedPattern("/xml version/");


        // Turn to the export data page.
        $this->assertTrue($this->get("$webUrl/tblproperties.php", array(
            'server' => $SERVER,
            'database' => $DATABASE,
            'schema' => 'public',
            'table' => 'student',
            'subject' => 'table',
            'action' => 'export'))
        );

        // Enter information for exporting the data.
        $this->assertTrue($this->setField('what', 'dataonly'));
        $this->assertTrue($this->setField('d_format', 'XHTML'));
        $this->assertTrue($this->setField('output', 'show'));

        // Then submit and verify it.
        $this->assertTrue($this->clickSubmit($lang['strexport']));
        $this->assertWantedPattern('/testname/');

        // Empty the data in the table.
        $this->assertTrue($this->get("$webUrl/tables.php", array(
            'server' => $SERVER,
            'action' => 'confirm_empty',
            'database' => $DATABASE,
            'schema' => 'public',
            'table' => 'student'))
        );
        $this->assertTrue($this->clickSubmit($lang['strempty']));

        return TRUE;
    }


    /*
     * TestCaseID: CED06
     * Test to export database structure and data with "SQL" format.
     */
    public function testTableStructureDataSQLDownload()
    {
        global $webUrl, $lang, $SERVER, $DATABASE;

        // Turn to the export data page.
        $this->assertTrue($this->get("$webUrl/tblproperties.php", array(
            'server' => $SERVER,
            'database' => $DATABASE,
            'schema' => 'public',
            'table' => 'student',
            'subject' => 'table',
            'action' => 'export'))
        );

        // Enter information for exporting the data.
        $this->assertTrue($this->setField('what', 'structureanddata'));
        $this->assertTrue($this->setField('sd_format', 'COPY'));
        $this->assertTrue($this->setField('output', 'download'));

        //Then submit and verify it.
        $this->assertTrue($this->clickSubmit($lang['strexport']));
        $this->assertWantedText('CREATE TABLE student');

        return TRUE;
    }

    /*
     * TestCaseID: CED07
     * Test to export view structure.
     */
    public function testViewStructureShow()
    {
        global $webUrl, $lang, $SERVER, $DATABASE;

        // Turn to the export data page.
        $this->assertTrue($this->get("$webUrl/viewproperties.php", array(
            'server' => $SERVER,
            'database' => $DATABASE,
            'schema' => 'pg_catalog',
            'view' => 'pg_user',
            'subject' => 'view',
            'action' => 'export'))
        );

        // Enter information for exporting the data.
        $this->assertTrue($this->setField('s_clean', true));
        $this->assertTrue($this->setField('output', 'show'));

        //Then submit and verify it.
        $this->assertTrue($this->clickSubmit($lang['strexport']));
        $this->assertWantedText('CREATE VIEW pg_user');

        return TRUE;
    }

}
