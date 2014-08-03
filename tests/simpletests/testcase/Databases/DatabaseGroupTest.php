<?php
/**
 * Function area     : Database.
 * This is group test file for database.
 *
 * @author     Augmentum SpikeSource Team
 * @copyright  Copyright (c) 2005 by Augmentum, Inc.
 */

 require_once 'DatabaseTest.php';
 require_once 'SqlTest.php';
 require_once 'FindObjectsTest.php';
 require_once 'VariablesTest.php';
 require_once 'SchemaBasicTest.php';
 require_once 'AdminTest.php';
 require_once 'ProcessesTest.php';
 require_once 'LanguageTest.php';
 require_once 'CastsTest.php';
 require_once 'HelpTest.php';

/**
 * Run all the test cases as one group.
 */
 class DatabaseGroupTest extends GroupTest
 {
    public function DatabaseGroupTest()
    {
        $this->GroupTest('Database group test begins.');

        /*
         * Hides it temporary.
         * $this->addTestClass(new TableTest());
         */
        $this->addTestClass(new SqlTest());
        $this->addTestClass(new DatabaseTest());
        $this->addTestClass(new FindObjectsTest());
        $this->addTestClass(new VariablesTest());
        $this->addTestClass(new SchemaBasicTest());
        $this->addTestClass(new AdminTest());
        $this->addTestClass(new ProcessesTest());
        $this->addTestClass(new LanguageTest());
        $this->addTestClass(new CastsTest());
        $this->addTestClass(new HelpTest());
    }
 }
