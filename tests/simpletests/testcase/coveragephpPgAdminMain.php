<?php
/**
 * This is entrance to run all the test suites and generate a report for code coverage.
 *
 * @author     Augmentum SpikeSource Team
 * @copyright  2005 by Augmentum, Inc.
 */

require_once 'simpletest.inc.php';
require_once 'simpletest/web_tester.php';
require_once 'simpletest/reporter.php';

// Import the language file for phpPgAdmi to avoid hard code.
require_once 'lang/recoded/english.php';

require_once 'Public/common.php';
require_once 'Public/SetPrecondition.php';

// Creates GroupTest objects for running all the testcase.
require_once 'Server/ServerGroupTest.php';
require_once 'Databases/DatabaseGroupTest.php';
require_once 'Schemas/SchemasGroupTest.php';
require_once 'Tables/TableGroupTest.php';
require_once 'Common/CommonGroupTest.php';

$testServer = &new ServerGroupTest();
$testDatabase = &new DatabaseGroupTest();
$testSchema = &new SchemasGroupTest();
$testTable = &new TableGroupTest();
$testCommon = &new CommonGroupTest();

require_once 'phpcoverage.inc.php';
require_once 'remote/RemoteCoverageRecorder.php';
require_once 'reporter/HtmlCoverageReporter.php';

// These variables will be set by the phpcoverage.inc.php
global $PHPCOVERAGE_REPORT_DIR, $PHPCOVERAGE_APPBASE_PATH;

$cov_weburl = $webUrl . "/phpcoverage.remote.top.inc.php";

// Initialize RemoteCoverageRecorder
file_get_contents($cov_weburl . "?phpcoverage-action=init&cov-file-name=" .
                  urlencode("TestphpPgAdmin.xml") . "&tmp-dir=". urlencode("/tmp"));

// Run the simpletest test cases
$testServer->run(new TextReporter());
$testDatabase->run(new TextReporter());
$testSchema->run(new TextReporter());
$testTable->run(new TextReporter());
$testCommon->run(new TextReporter());

// Get the coverage data xml
$xml = file_get_contents($cov_weburl . "?phpcoverage-action=get-coverage-xml");

// Cleanup the recording
file_get_contents($cov_weburl . "?phpcoverage-action=cleanup");

$reporter = new HtmlCoverageReporter("phpPgAdmin Code coverage report","","$PHPCOVERAGE_REPORT_DIR");

// Sets the directories or file paths to be included in the code coverage recording.
$includePaths = array(realpath($PHPCOVERAGE_APPBASE_PATH));
$excludePaths = array(realpath($PHPCOVERAGE_APPBASE_PATH)."/lang", realpath($PHPCOVERAGE_APPBASE_PATH)."/libraries/adodb/drivers");
$cov = new RemoteCoverageRecorder($includePaths, $excludePaths, $reporter);

// Generate the code coverage report
$cov->generateReport($xml);

$reporter->printTextSummary();
