<?php
/**
 * Function area     : Database.
 * Sub Function area : Language.
 *
 * @author     Augmentum SpikeSource Team
 * @copyright  Copyright (c) 2005 by Augmentum, Inc.
 */

// Import the precondition class.
if (is_dir('../Public')) {
    require_once '../Public/SetPrecondition.php';
}

/**
 * This class is to test the Language displayed list.
 */
class LanguageTest extends PreconditionSet
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
     * TestCaseId: DLD001
     * This test is used to test Language Displayed page.
     */
    public function testLanguage()
    {
        global $webUrl, $SERVER, $DATABASE;

        // Locate the list page of language.
        $this->assertTrue($this->get("$webUrl/languages.php", array(
                        'server' => $SERVER,
                        'database' => $DATABASE,
                        'subject' => 'database'))
                    );

        $this->assertWantedPattern('/sql/');

        return TRUE;
    }
}
