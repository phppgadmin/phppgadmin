<?php
/**
 * Function area     : Database.
 * Sub Function area : SchemaBasic.
 *
 * @author     Augmentum SpikeSource Team
 * @copyright  Copyright (c) 2005 by Augmentum, Inc.
 */

// Import the precondition class.
if (is_dir('../Public')) {
    require_once '../Public/SetPrecondition.php';
}

/**
 * This class is to test the Schema Basic Management of
 * PostgreSql implementation.
 */
class SchemaBasicTest extends PreconditionSet
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
     * TestCaseId: DCS001
     * This test is used to create one new schema for super user.
     */
    public function testCreateBasSchema()
    {
        global $webUrl, $SUPER_USER_NAME;
        global $lang, $SERVER, $DATABASE;

        $this->assertTrue($this->get("$webUrl/database.php", array(
                        'server' => $SERVER,
                        'database' => $DATABASE,
                        'subject' => 'database'))
                    );
        $this->assertTrue($this->get("$webUrl/schemas.php", array(
                        'server' => $SERVER,
                        'database' => $DATABASE,
                        'action' => 'create'))
                    );

        $this->assertTrue($this->setField('formName', 'testSchemaName'));
        $this->assertTrue($this->setField('formAuth', $SUPER_USER_NAME));
        $this->assertTrue($this->setField('formComment',
                                          'Comment of test schema.'));

        $this->assertTrue($this->clickSubmit($lang['strcreate']));

        $this->assertWantedText($lang['strschemacreated']);

        return TRUE;
    }

    /**
     * TestCaseId: DAS001
     * This test is used to modify one existent schema for super user.
     */
    public function testAlterBasSchema()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;

        $this->assertTrue($this->get("$webUrl/database.php", array(
                        'server' => $SERVER,
                        'database' => $DATABASE,
                        'subject' => 'database'))
        );
        $this->assertTrue($this->get("$webUrl/redirect.php", array(
                        'server' => $SERVER,
                        'section' => 'database',
                        'database' => $DATABASE))
        );
        $this->assertTrue($this->get("$webUrl/database.php", array(
                        'server' => $SERVER,
                        'database' => $DATABASE,
                        'subject' => 'database'))
        );
        $this->assertTrue($this->get("$webUrl/schemas.php", array(
                        'server' => $SERVER,
                        'action' => 'alter',
                        'database' => $DATABASE,
                        'schema' => 'testSchemaName'))
        );

        $this->assertTrue($this->setField('comment',
                                          'The comment has been changed.'));
        $this->assertTrue($this->clickSubmit('Alter'));

        $this->assertWantedText($lang['strschemaaltered']);

        return TRUE;
    }

    /**
     * TestCaseId: DDS001
     * This test is used to drop one existent schema for super user.
     */
    public function testDropBasSchema()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;

        $this->assertTrue($this->get("$webUrl/database.php", array(
                        'server' => $SERVER,
                        'database' => $DATABASE,
                        'subject' => 'database'))
        );
        $this->assertTrue($this->get("$webUrl/redirect.php", array(
                        'server' => $SERVER,
                        'section' => 'database',
                        'database' => $DATABASE))
        );
        $this->assertTrue($this->get("$webUrl/schemas.php", array(
                        'server' => $SERVER,
                        'action' => 'drop',
                        'database' => $DATABASE,
                        'schema' => 'testSchemaName'))
        );

        $this->assertTrue($this->setField('cascade', true));
        $this->assertTrue($this->clickSubmit($lang['strdrop']));

        $this->assertWantedText($lang['strschemadropped']);

        return TRUE;
    }
}
