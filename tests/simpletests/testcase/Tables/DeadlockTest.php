<?php
 /**
  * Function area:       Table
  * Subfunction area:    Exception
  * @author     Augmentum SpikeSource Team
  * @copyright  2005 by Augmentum, Inc.
  */

// Import the precondition class.
if (is_dir('../Public')) {
    require_once '../Public/SetPrecondition.php';
}

/**
 * A test case suite for testing exceptional scenarios in phpPgAdmin
 */
class DeadlockTest extends PreconditionSet
{
    /**
     * Set up the preconditon.
     */
    public function setUp()
    {
        return TRUE;
    }

    /**
     * Clean up all the result.
     */
    public function tearDown()
    {
        $this->logout();

        return TRUE;
    }

    /**
     * TestCaseID: TET03
     *
     * Scenario:
     * 1. Open the first session, login as power user. Try to add a check
     *    constraint: id_check (id > 0), without commit
     * 2. Open the second session, login as super user. Change the password
     *    of the power user and commit the alteration.
     * 3. Turn to the first session and commit the add check operation.
     * 4. Rollback the changes, that's, change back the password for the
     *    power user.
     *
     * Expected result:  Login failed.
     */
    public function testAddCheckScenario()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;
        global $POWER_USER_PASSWORD;
        global $SUPER_USER_PASSWORD;
        global $SUPER_USER_NAME;
        global $POWER_USER_NAME;


        $this->login($POWER_USER_NAME, $POWER_USER_PASSWORD,
                     "$webUrl/login.php");

        // Try to add a check constraint to the table student
        // Go to the constraints page
        $this->assertTrue($this->get("$webUrl/constraints.php", array(
            'server' => $SERVER,
            'action' => 'add_check',
            'database' => $DATABASE,
            'schema' => 'public',
            'table' => 'student'))
        );

        // Set properties for the new constraint
        $this->assertTrue($this->setField('name', 'id_check'));
        $this->assertTrue($this->setField('definition', 'id > 0'));


        // Open the second session, login as superuser
        $newBrowser = $this->createBrowser();
        $newBrowser->get("$webUrl/login.php");
        $this->assertTrue($newBrowser->setField('formUsername', $SUPER_USER_NAME));
        $this->assertTrue($newBrowser->setField('formPassword', $SUPER_USER_PASSWORD));
        $this->assertTrue($newBrowser->setField('formLanguage', $lang['applang']));
        $this->assertTrue($newBrowser->clickSubmit('Login'));

        // Alter the user's password
        $this->assertTrue($newBrowser->get("$webUrl/users.php"));
        $this->assertTrue($newBrowser->get("$webUrl/users.php", array(
            'server' => $SERVER,
            'action' => 'edit',
            'username' => $POWER_USER_NAME))
        );
        // Enter the information for altering the user's properties.
        $this->assertTrue($newBrowser->setField('newname', $POWER_USER_NAME));
        $this->assertTrue($newBrowser->setField('formPassword', '56789'));
        $this->assertTrue($newBrowser->setField('formConfirm', '56789'));
        $this->assertTrue($newBrowser->setField('formSuper', false));
        $this->assertTrue($newBrowser->setField('formCreateDB', true));
        $this->assertTrue($newBrowser->clickSubmit($lang['stralter']));


        // Now turn to the first session, submit the "add check" operation
        $this->assertTrue($this->clickSubmit($lang['stradd']));
        // Verify if the constraint is created correctly.
        $this->assertTrue($this->assertWantedText($lang['strloginfailed']));


        // Rollback the changes to return to the original state
        $this->assertTrue($newBrowser->get("$webUrl/users.php"));
        $this->assertTrue($newBrowser->get("$webUrl/users.php", array(
            'server' => $SERVER,
            'action' => 'edit',
            'username' => $POWER_USER_NAME))
        );

        // Change back the user's password
        $this->assertTrue($newBrowser->setField('newname', $POWER_USER_NAME));
        $this->assertTrue($newBrowser->setField('formPassword', $POWER_USER_PASSWORD));
        $this->assertTrue($newBrowser->setField('formConfirm', $POWER_USER_PASSWORD));
        $this->assertTrue($newBrowser->setField('formSuper', false));
        $this->assertTrue($newBrowser->setField('formCreateDB', true));
        $this->assertTrue($newBrowser->clickSubmit($lang['stralter']));

        return TRUE;
    }

    /**
     * TestCaseID: TET01
     *
     * Scenario:
     * 1. Open the first session, login as power user. Try to create a
     *    database, don't commit;
     * 2. Open the second session, login as super user. Revoke the power
     *    user's createdb privilege
     * 3. Turn to the first session and commit the create database operation.
     * 4. Rollback the changes, that's, grant the createdb privilege to the
     *    power user.
     *
     * Expected result:  Failed to add database. Permission denied.
     */
    public function testCreateDatabaseScenario()
    {
        global $webUrl;
        global $lang, $SERVER;
        global $POWER_USER_PASSWORD;
        global $SUPER_USER_PASSWORD;
        global $SUPER_USER_NAME;
        global $POWER_USER_NAME;

        $this->login($POWER_USER_NAME, $POWER_USER_PASSWORD,
                     "$webUrl/login.php");

        // Try to create a database 'newdb', without commit the operation
        $this->assertTrue ($this->get ("$webUrl/all_db.php"));
        $this->assertTrue ($this->get ("$webUrl/all_db.php", array(
            'server' => $SERVER,
            'action' => 'create'))
        );
        $this->assertTrue ($this->setfield('formName', 'newdb'));
        $this->assertTrue ($this->setfield('formEncoding', 'UNICODE'));


        // Open another session, login as superuser
        $newBrowser = $this->createBrowser();
        $newBrowser->get("$webUrl/login.php");
        $this->assertTrue($newBrowser->setField('formUsername', $SUPER_USER_NAME));
        $this->assertTrue($newBrowser->setField('formPassword', $SUPER_USER_PASSWORD));
        $this->assertTrue($newBrowser->setField('formLanguage', $lang['applang']));
        $this->assertTrue($newBrowser->clickSubmit('Login'));

        // Revoke the user's createdb privilege
        $this->assertTrue($newBrowser->get("$webUrl/users.php"));
        $this->assertTrue($newBrowser->get("$webUrl/users.php", array(
            'server' => $SERVER,
            'action' => 'edit',
            'username' => $POWER_USER_NAME))
        );
        $this->assertTrue($newBrowser->setField('newname', $POWER_USER_NAME));
        $this->assertTrue($newBrowser->setField('formPassword', 'tester'));
        $this->assertTrue($newBrowser->setField('formConfirm', 'tester'));
        $this->assertTrue($newBrowser->setField('formSuper', false));
        $this->assertTrue($newBrowser->setField('formCreateDB', false));
        $this->assertTrue($newBrowser->clickSubmit($lang['stralter']));

        // Now turn to the first session, submit the "create DB" operation
        $this->assertTrue ($this->clickSubmit($lang['strcreate']));
        // Verify weather the database has been created.
        $this->assertTrue ($this->assertWantedText($lang['strdatabasecreatedbad']));

        // Rollback the changes to return to the original state
        $this->assertTrue($newBrowser->get("$webUrl/users.php"));
        $this->assertTrue($newBrowser->get("$webUrl/users.php", array(
            'server' => $SERVER,
            'action' => 'edit',
            'username' => $POWER_USER_NAME))
        );

        $this->assertTrue($newBrowser->setField('newname', $POWER_USER_NAME));
        $this->assertTrue($newBrowser->setField('formPassword', $POWER_USER_PASSWORD));
        $this->assertTrue($newBrowser->setField('formConfirm', $POWER_USER_PASSWORD));
        $this->assertTrue($newBrowser->setField('formSuper', false));
        $this->assertTrue($newBrowser->setField('formCreateDB', true));
        $this->assertTrue($newBrowser->clickSubmit($lang['stralter']));

        return TRUE;
    }

    /**
     * TestCaseID: TET02
     *
     * Scenario:
     * 1. Open the first session, login as power user. Create a column named
     *    "sid". Then try to rename it as "ssid", don't commit;
     * 2. Open the second session, login as power user. Drop the column "sid";
     * 3. Turn to the first session and commit the alter column operation.
     *
     * Expected result:  Failed to alter the column.
     */
    public function testDropColumnScenario()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;
        global $POWER_USER_PASSWORD;
        global $SUPER_USER_PASSWORD;
        global $SUPER_USER_NAME;
        global $POWER_USER_NAME;

        $this->login($POWER_USER_NAME, $POWER_USER_PASSWORD,
                     "$webUrl/login.php");

        // Add a column "sid"
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


        // Try to alter the column as "ssid"
        $this->assertTrue($this->get("$webUrl/tblproperties.php", array(
            'server' => $SERVER,
            'action' => 'properties',
            'database' => $DATABASE,
            'schema' => 'public',
            'table' => 'student',
            'column' => 'sid'))
        );
        // Set properties for the new column
        $this->assertTrue($this->setField('field', 'ssid'));
        $this->assertTrue($this->setField('type', 'character'));
        $this->assertTrue($this->setField('length', '18'));


        // Open the second session, login as power user
        $newBrowser = $this->createBrowser();
        $newBrowser->get("$webUrl/login.php");
        $this->assertTrue($newBrowser->setField('formUsername', $POWER_USER_NAME));
        $this->assertTrue($newBrowser->setField('formPassword', $POWER_USER_PASSWORD));
        $this->assertTrue($newBrowser->setField('formLanguage', $lang['applang']));
        $this->assertTrue($newBrowser->clickSubmit('Login'));

        // Drop the column
        $this->assertTrue($newBrowser->get("$webUrl/tblproperties.php", array(
            'server' => $SERVER,
            'action' => 'confirm_drop',
            'database' => $DATABASE,
            'schema' => 'public',
            'table' => 'student',
            'column' => 'sid'))
        );
        $this->assertTrue($newBrowser->clickSubmit($lang['strdrop']));


        // Now turn to the first session, submit the "create DB" operation
         $this->assertTrue($this->clickSubmit($lang['stralter']));
        // Verify if the column is altered correctly.
        $this->assertTrue($this->assertWantedText($lang['strcolumnalteredbad']));

        return TRUE;
    }


    /**
     * TestCaseID: TET05
     *
     * Scenario:
     * 1. Open the first session, login as power user. Create a table
     *    named "newtable". Then issue a SELECT query without commit.
     * 2. Open another session, login as power user. Drop the table "newtable";
     * 3. Turn to the first session and commit the SELECT query.
     *
     * Expected result:  ERROR:  relation "public.newtable" does not exist.
     */
    public function testQueryDroppedTable()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;
        global $POWER_USER_PASSWORD;
        global $SUPER_USER_PASSWORD;
        global $SUPER_USER_NAME;
        global $POWER_USER_NAME;

        $this->login($SUPER_USER_NAME, $SUPER_USER_PASSWORD,
                     "$webUrl/login.php");
        // Create a table.
        $this->assertTrue($this->get("$webUrl/tables.php", array(
            'server' => $SERVER,
            'action' => 'create',
            'database' => $DATABASE,
            'schema' => 'public'))
        );

        // Enter the table name and field number.
        $this->assertTrue($this->setField('name', 'newtable'));
        $this->assertTrue($this->setField('fields', '2'));
        $this->assertTrue($this->setField('spcname', 'pg_default'));
        $this->assertTrue($this->setField('tblcomment', 'Create from SimpleTest!'));

        // Click the button "next >" for inputing the detail information.
        //$this->assertTrue($this->clickSubmit($lang['strnext']));
        // If we do not hardcoded it here, it will cause fail. Encoding issue.
        $this->assertTrue($this->clickSubmit('Next >'));

        // Enter the detail information of the table.
        $this->assertTrue($this->setField('field[0]', 'firstfield'));
        $this->assertTrue($this->setField('type[0]', 'text'));
        $this->assertTrue($this->setField('array[0]', ''));

        $this->assertTrue($this->setField('field[1]', 'secondfield'));
        $this->assertTrue($this->setField('type[1]', 'text'));
        $this->assertTrue($this->setField('array[1]', ''));

        // Click the button "Create" to create the table
        $this->assertTrue($this->clickSubmit($lang['strcreate']));
        // Verify if the table create correctly.
        $this->assertTrue($this->assertWantedText($lang['strtablecreated']));


        // Issue a SELECT query

        // Turn to the "tables" page.
        $this->assertTrue($this->get("$webUrl/tables.php", array(
            'server' => $SERVER,
            'action' => 'confselectrows',
            'database' => $DATABASE,
            'schema' => 'public',
            'table' => 'newtable'))
        );

        // Select all the rows.
        $this->assertTrue($this->setField('show[firstfield]', true));
        $this->assertTrue($this->setField('show[secondfield]', true));


        // Open another session, login as super user, drop the table "newtable"
        $newBrowser = $this->createBrowser();
        $newBrowser->get("$webUrl/login.php");
        $this->assertTrue($newBrowser->setField('formUsername', $SUPER_USER_NAME));
        $this->assertTrue($newBrowser->setField('formPassword', $SUPER_USER_PASSWORD));
        $this->assertTrue($newBrowser->setField('formLanguage', $lang['applang']));
        $this->assertTrue($newBrowser->clickSubmit('Login'));

        // Drop the table
        $newBrowser->get("$webUrl/tables.php", array(
            'server' => $SERVER,
            'action' => 'confirm_drop',
            'database' => $DATABASE,
            'schema' => 'public',
            'table' => 'newtable')
        );
        $this->assertTrue($newBrowser->clickSubmit($lang['strdrop']));


        // Now turn to the first session, submit the SELECT query
        // Display all the rows.
        $this->assertTrue($this->clickSubmit($lang['strselect']));
        // Verify whether select successful.
        $this->assertTrue($this->assertWantedText($lang['strsqlerror']));
        $this->assertTrue($this->assertWantedText('public.newtable'));
        $this->assertTrue($this->assertWantedText('does not exist'));

        return TRUE;
    }
}
