<?php
    $test_title = 'Index tests';

    if (isset($_GET['run'])) {
        global $lang;
        require '../config.test.php';
        require '../testBuilder.class.php';
        /**
		 * 1/ Create the unique index
		 * 2/ Drop the index
		 **/
        $t = new TestBuilder($test_title,
            'Create/Drop an unique Index'
        );

        $t->login($admin_user, $admin_user_pass);

        /** 1 **/
        $t->addComment('1. Create the unique index');
        $t->clickAndWait("link={$lang['strdatabases']}");
        $t->clickAndWait("link={$testdb}");
        $t->clickAndWait("link={$lang['strschemas']}");
        $t->clickAndWait('link=public');
        $t->clickAndWait("link={$lang['strtables']}");
        $t->clickAndWait('link=student');
        $t->clickAndWait("link={$lang['strindexes']}");
        $t->clickAndWait("link={$lang['strcreateindex']}");
        $t->type('formIndexName', 'name_unique');
        $t->addSelection('TableColumnList', 'label=name');
        $t->click('add');
        $t->clickAndWait("//input[@value='{$lang['strcreate']}']");
        $t->assertText("//p[@class='message']", $lang['strindexcreated']);

        /** 2 **/
        $t->addComment('2. Cluster on the index');
        $t->clickAndWait("link={$lang['strindexes']}");
        $t->clickAndWait("//tr/td[text()='name_unique']/../td/a[text()='Cluster']");
        $t->clickAndWait('cluster');
        $t->assertText("//p[@class='message']", $lang['strclusteredgood'] . ' ' . $lang['stranalyzegood']);

        /** 3 **/
        $t->addComment('3. Drop the index');
        $t->clickAndWait("link={$lang['strindexes']}");
        $t->clickAndWait("//tr/td[text()='name_unique']/../td/a[text()='Drop']");
        $t->click('cascade');
        $t->clickAndWait('drop');
        $t->assertText("//p[@class='message']", $lang['strindexdropped']);

        $t->logout();
        unset($t);
    }
