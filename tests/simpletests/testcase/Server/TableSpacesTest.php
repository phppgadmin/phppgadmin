<?php
/**
 * Function area: Server
 * Sub function area: TableSpaces
 *
 * @author     Augmentum SpikeSource Team
 * @copyright  2005 by Augmentum, Inc.
 */

// Import the precondition class.
if (is_dir('../Public')) {
    require_once '../Public/SetPrecondition.php';
}

/**
 * This class is to test the tablespace management.
 * It includes create/drop/alter/list tablespaces.
 */
class TableSpacesTest extends PreconditionSet
{
    // Declare member variables for the table space name and location.
    private $_tableSpaceName = 'TestTableSpace';
    private $_location;

    public function setUp()
    {
        global $webUrl;
        global $SUPER_USER_NAME;
        global $SUPER_USER_PASSWORD;
        global $lang;

        $this->login($SUPER_USER_NAME, $SUPER_USER_PASSWORD, "$webUrl/login.php");

        return TRUE;
    }

    public function tearDown()
    {
        $this->logout();

        return TRUE;
    }

    /*
     * TestCaseID: SCT01
	 * Test to create tablespace.
	 * XXX: Your PgSQL admin user must own data/TableSpace
     */
    public function testCreate()
    {
        global $webUrl;
        global $POWER_USER_NAME;
        global $lang, $SERVER;
        $this->_location = getcwd() . '/data/TableSpace';

        // Turn to the create tablespace page.
        $this->assertTrue($this->get("$webUrl/tablespaces.php", array('server' => $SERVER)));
        $this->assertTrue($this->clickLink($lang['strcreatetablespace']));

        // Enter information for creating a tablespace.
        $this->assertTrue($this->setField('formSpcname', $this->_tableSpaceName));
        $this->assertTrue($this->setField('formOwner', $POWER_USER_NAME));
        $this->assertTrue($this->setField('formLoc', $this->_location));

        // Then submit and verify it.
        $this->assertTrue($this->clickSubmit($lang['strcreate']));
        $this->assertWantedText($lang['strtablespacecreated']);

        return TRUE;
    }

    /*
     * TestCaseID: SAT01
     * Test to alter existing tablespace's properties.
     */
    public function testAlter()
    {
        global $webUrl;
        global $NORMAL_USER_NAME;
        global $lang, $SERVER;

        // Turn to the alter tablespace page.
        $this->assertTrue($this->get("$webUrl/tablespaces.php", array('server' => $SERVER)));
        $this->assertTrue($this->get("$webUrl/tablespaces.php", array(
                        'server' => $SERVER,
                        'action' => 'edit',
                        'tablespace' => $this->_tableSpaceName))
                    );

        // Enter information for altering the tableSpace's properties.
        $this->assertTrue($this->setField('name', $this->_tableSpaceName));
        $this->assertTrue($this->setField('owner', $NORMAL_USER_NAME));

        // Then submit and verify it.
        $this->assertTrue($this->clickSubmit($lang['stralter']));
        $this->assertWantedText($lang['strtablespacealtered']);

        return TRUE;
    }

    /*
     * TestCaseID: SPT01
     * Test to grant privileges for tablespace.
     */
    public function testGrantPrivilege()
    {
        global $webUrl;
        global $NORMAL_USER_NAME;
        global $lang, $SERVER;

        // Turn to the privileges page.
        $this->assertTrue($this->get("$webUrl/privileges.php", array('server' => $SERVER)));
        $this->assertTrue($this->get("$webUrl/privileges.php", array(
                        'server' => $SERVER,
                        'subject' => 'tablespace',
                        'tablespace' => $this->_tableSpaceName))
                    );

        // Grant with no privileges selected.
        $this->assertTrue($this->clickLink($lang['strgrant']));
        $this->assertTrue($this->setField('username[]', array($NORMAL_USER_NAME)));
        $this->assertTrue($this->setField('privilege[CREATE]', true));
        $this->assertTrue($this->setField('privilege[ALL PRIVILEGES]', true));
        $this->assertTrue($this->setField('grantoption', true));

        // Then submit and verifiy it.
        $this->assertTrue($this->clickSubmit($lang['strgrant']));
        $this->assertWantedText($lang['strgranted']);
        $this->assertWantedText($NORMAL_USER_NAME);

        return TRUE;
    }

    /*
     * TestCaseID: SPT02
     * Test to revoke privileges for tablespace.
     */
    public function testRevokePrivilege()
    {
        global $webUrl;
        global $NORMAL_USER_NAME;
        global $lang, $SERVER;

        // Turn to the privileges page.
        $this->assertTrue($this->get("$webUrl/privileges.php", array('server' => $SERVER)));
        $this->assertTrue($this->get("$webUrl/privileges.php", array(
                        'server' => $SERVER,
                        'subject' => 'tablespace',
                        'tablespace' => $this->_tableSpaceName))
                    );

        // Revoke with no users selected.
        $this->assertTrue($this->clickLink($lang['strrevoke']));
        $this->assertTrue($this->setField('username[]', array($NORMAL_USER_NAME)));
        $this->assertTrue($this->setField('privilege[ALL PRIVILEGES]', true));

        // Then submit and verify it.
        $this->assertTrue($this->clickSubmit($lang['strrevoke']));
        $this->assertWantedText($lang['strgranted']);
        $this->assertNoUnWantedText($NORMAL_USER_NAME);

        return TRUE;
    }

    /*
     * TestCaseID: SPT03
     * Test to grant privilege with no privilege selected for tablespace.
     */
    public function testGrantNoPrivilege()
    {
        global $webUrl;
        global $NORMAL_USER_NAME;
        global $lang, $SERVER;

        // Turn to the privileges page.
        $this->assertTrue($this->get("$webUrl/privileges.php", array('server' => $SERVER)));
        $this->assertTrue($this->get("$webUrl/privileges.php", array(
                        'server' => $SERVER,
                        'subject' => 'tablespace',
                        'tablespace' => $this->_tableSpaceName))
        );

        // Grant whit no privilege selected.
        $this->assertTrue($this->clickLink($lang['strgrant']));
        $this->assertTrue($this->setField('username[]', array($NORMAL_USER_NAME)));

        // Then submit and verify it.
        $this->assertTrue($this->clickSubmit($lang['strgrant']));
        $this->assertWantedText($lang['strgrantbad']);

        return TRUE;
    }

    /*
     * TestCaseID: SPT04
     * Test to revoke privileges with no user selected for tablespace.
     */
    public function testRevokeNoUser()
    {
        global $webUrl;
        global $NORMAL_USER_NAME;
        global $lang, $SERVER;

        // Turn to the privileges page.
        $this->assertTrue($this->get("$webUrl/privileges.php", array('server' => $SERVER)));
        $this->assertTrue($this->get("$webUrl/privileges.php", array(
                        'server' => $SERVER,
                        'subject' => 'tablespace',
                        'tablespace' => $this->_tableSpaceName))
        );

        // Revoke whit no users selected.
        $this->assertTrue($this->clickLink($lang['strrevoke']));

        // Then submit and verify it.
        $this->assertTrue($this->clickSubmit($lang['strrevoke']));
        $this->assertWantedText($lang['strgrantbad']);

        return TRUE;
    }

    /*
     * TestCaseID: SDT01
     * Test to drop existing tablespace.
     */
    public function testDrop()
    {
        global $webUrl;
        global $lang, $SERVER;

        // Turn to the drop user page.
        $this->assertTrue($this->get("$webUrl/tablespaces.php", array('server' => $SERVER)));
        $this->assertTrue($this->get("$webUrl/tablespaces.php", array(
                        'server' => $SERVER,
                        'action' => 'confirm_drop',
                        'tablespace' => $this->_tableSpaceName))
        );

        // Confirm to drop the user and verify it.
        $this->assertTrue($this->clickSubmit($lang['strdrop']));
        $this->assertWantedText($lang['strtablespacedropped']);
        $this->assertNoUnWantedText($this->_tableSpaceName);

        return TRUE;
    }
}
