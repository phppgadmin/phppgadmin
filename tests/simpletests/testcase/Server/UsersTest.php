<?php
/**
 * Function area: Server
 * Sub function area: Users
 *
 * @author     Augmentum SpikeSource Team
 * @copyright  2005 by Augmentum, Inc.
 */

// Import the precondition class.
require_once 'Public/SetPrecondition.php';

/**
 * This class is to test the user management.
 * It includes create/drop/alter/list users.
 */
class UsersTest extends PreconditionSet
{
    // Declare the user names which are created, altered and dropped in the class.
    private $_superUserName = "superuser";
    private $_powerUserName = "poweruser";

    public function setUp()
    {
        global $webUrl, $SUPER_USER_NAME, $SUPER_USER_PASSWORD, $SERVER;

        $this->login($SUPER_USER_NAME, $SUPER_USER_PASSWORD, "$webUrl/login.php");

        return TRUE;
    }

    public function tearDown()
    {
        $this->logout();

        return TRUE;
    }

    /*
     * TestCaseID: SCU01
     * Test to create super user.
     */
    public function testCreateSuper()
    {
        global $webUrl;
        global $lang, $SERVER;

        // Turn to the "Create user" page.
        $this->assertTrue($this->get("$webUrl/users.php", array('server' => $SERVER)));

        $this->assertTrue($this->clickLink($lang['strcreateuser']));

        // Enter information for creating a user.
        $this->assertTrue($this->setField('formUsername', $this->_superUserName));
        $this->assertTrue($this->setField('formPassword', '123456'));
        $this->assertTrue($this->setField('formConfirm', '123456'));
        $this->assertTrue($this->setField('formSuper', true));
        $this->assertTrue($this->setField('formCreateDB', true));

        // Then submit and verify it.
        $this->assertTrue($this->clickSubmit($lang['strcreate']));
        $this->assertWantedText($this->_superUserName);

        return TRUE;
    }

    /*
     * TestCaseID: SCU02
     * Test to create power user.
     */
    public function testCreatePower()
    {
        global $webUrl;
        global $lang, $SERVER;

        // Turn to the "Create user" page.
        $this->assertTrue($this->get("$webUrl/users.php", array('server' => $SERVER)));
        $this->assertTrue($this->clickLink($lang['strcreateuser']));

        // Enter information for creating a user.
        $this->assertTrue($this->setField('formUsername', $this->_powerUserName));
        $this->assertTrue($this->setField('formPassword', '123456'));
        $this->assertTrue($this->setField('formConfirm', '123456'));
        $this->assertTrue($this->setField('formCreateDB', true));

        //Then submit and verify it.
        $this->assertTrue($this->clickSubmit($lang['strcreate']));
        $this->assertWantedText($this->_powerUserName);

        return TRUE;
    }

    /*
     * TestCaseID: SLU01
     * Test to list all the users.
     */
    public function testListUsers()
    {
        global $webUrl;
        global $SUPER_USER_NAME;
        global $POWER_USER_NAME;
        global $NORMAL_USER_NAME;
        global $lang, $SERVER;

        // Get the users list page and verify it.
        $this->assertTrue($this->get("$webUrl/users.php", array('server' => $SERVER)));
        $this->assertWantedText($SUPER_USER_NAME);
        $this->assertWantedText($POWER_USER_NAME);
        $this->assertWantedText($NORMAL_USER_NAME);

        return TRUE;
    }

    /*
     * TestCaseID: SAU01
     * Test to alter existing user's properties.
     */
    public function testAlter()
    {
        global $webUrl;
        global $lang, $SERVER;

        // Turn to the "alter user" page.
        $this->assertTrue($this->get("$webUrl/users.php"));
        $this->assertTrue($this->get("$webUrl/users.php", array(
                    'action' => 'edit',
                    'username' => $this->_superUserName,
                    'server' => $SERVER))
        );

        // Enter the information for altering the user's properties.
        $this->assertTrue($this->setField('newname', $this->_superUserName));
        $this->assertTrue($this->setField('formPassword', '56789'));
        $this->assertTrue($this->setField('formConfirm', '56789'));
        $this->assertTrue($this->setField('formSuper', true));
        $this->assertTrue($this->setField('formCreateDB', false));

        // Then submit and verify it.
        $this->assertTrue($this->clickSubmit($lang['stralter']));
        $this->assertWantedText($this->_superUserName);

        return TRUE;
    }

    /*
     * TestCaseID: SDU01
     * Test to drop existing user.
     */
    public function testDrop()
    {
        global $webUrl;
        global $lang, $SERVER;

        // Turn to the drop user page..
        $this->assertTrue($this->get("$webUrl/users.php", array('server' => $SERVER)));
        $this->assertTrue($this->get("$webUrl/users.php", array(
                'action' => 'confirm_drop',
                'username' => $this->_superUserName,
                'server' => $SERVER))
        );

        // Confirm to drop the user and verify it.
        $this->assertTrue($this->clickSubmit($lang['strdrop']));
        $this->assertNoUnWantedText($this->_superUserName);

        return TRUE;
    }

    /*
     * TestCaseID: SDU02
     * Test to drop existing user when the user is login.
     */
    public function testDropLogin()
    {
        global $webUrl;
        global $lang, $SERVER;

        // Create a new browser to login the power user which we want to drop.
        $newBrowser = $this->createBrowser();
        $newBrowser->get("$webUrl/login.php", array('server' => $SERVER));
        $this->assertTrue($newBrowser->setField('loginUsername', $this->_powerUserName));
        $this->assertTrue($newBrowser->setFieldById('loginPassword', '123456'));
        $this->assertTrue($newBrowser->clickSubmit('Login'));
        $this->assertTrue($newBrowser->get("$webUrl/all_db.php", array('server' => $SERVER)));

        // Turn to the old browser which we login with super user at very beginning.
        $this->assertTrue($this->get("$webUrl/users.php", array('server' => $SERVER)));
        $this->assertTrue($this->get("$webUrl/users.php", array('action' => 'confirm_drop',
                'username' => $this->_powerUserName,
                'server' => $SERVER))
        );

        // Confirm to drop the user and verify it.
        $this->assertTrue($this->clickSubmit($lang['strdrop']));
        $this->assertNoUnWantedText($this->_powerUserName);

        // Go back to the power user browser and try to create the database.
        // It will log out and $lang['strloginfailed'] will be displayed in the page.
        $this->setBrowser($newBrowser);

        $this->assertTrue($this->clickLink($lang['strcreatedatabase']));
        $this->assertWantedText($lang['strloginfailed']);

        return TRUE;
    }

    /*
     * TestCaseID: SDU03
     * Test to drop the user self.
     */
    public function testDropSelf()
    {
        global $webUrl;
        global $SUPER_USER_NAME;
        global $lang, $SERVER;

        // Turn to the drop user page..
        $this->assertTrue($this->get("$webUrl/users.php", array('server' => $SERVER)));
        $this->assertTrue($this->get("$webUrl/users.php", array(
                'action' => 'confirm_drop',
                'username' => $SUPER_USER_NAME,
                'server' => $SERVER    ))
        );

        // Confirm to drop the user and verify it.
        $this->assertTrue($this->clickSubmit($lang['strdrop']));
        $this->assertWantedText($SUPER_USER_NAME);
        $this->assertWantedText($lang['struserdroppedbad']);

        return TRUE;
    }
}
