<?php
/**
 * Function area     : Database.
 * Sub Function area : Casts.
 *
 * @author     Augmentum SpikeSource Team
 * @copyright  Copyright (c) 2005 by Augmentum, Inc.
 */

// Import the precondition class.
if (is_dir('../Public')) {
    require_once '../Public/SetPrecondition.php';
}

/**
 * This class is to test the Casts displayed list.
 */
class CastsTest extends PreconditionSet
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
     * TestCaseId: DLU001
     * This test is used to test Casts Displayed page.
     *
     * Note: It's strange here, because it only display one sentecse.
     */
    public function testLanguage()
    {
        global $webUrl;
        global $lang, $SERVER, $DATABASE;

        // Locate the list page of language.
        $this->assertTrue($this->get("$webUrl/casts.php", array(
                        'server' => $SERVER,
                        'database' => $DATABASE,
                        'subject' => 'database'))
                    );

        $this->assertWantedText($lang['strsourcetype']);
        $this->assertWantedText($lang['strtargettype']);
        $this->assertWantedText($lang['strimplicit']);

        return TRUE;
    }
}
