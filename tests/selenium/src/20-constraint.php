<?php
    $test_title = 'Constraint tests';

    if (isset($_GET['run'])) {
        global $lang;
        require '../config.test.php';
        require '../testBuilder.class.php';
        /*
		 * 1/ Add 2 Foreign Keys
		 * 2/ Add check constraint
		 * 3/ Add unique key
		 * 4/ Drop PK before creating it again
		 * 5/ Add primary key
		 * 6/ Drop FK
		 * 7/ Drop unique
		 * 8/ Drop check
		 */
        $t = new TestBuilder($test_title,
            'Create and drop constraints...'
        );

        $t->login($admin_user, $admin_user_pass);

        /** 1 **/
        $t->addComment('1. Add 2 Foreign Keys');
        $t->addComment('1.1. Add 1st Foreign Keys');
        $t->clickAndWait("link={$lang['strdatabases']}");
        $t->clickAndWait("link={$testdb}");
        $t->clickAndWait("link={$lang['strschemas']}");
        $t->clickAndWait('link=public');
        $t->clickAndWait("link={$lang['strtables']}");
        $t->clickAndWait('link=student');
        $t->clickAndWait("link={$lang['strconstraints']}");
        $t->clickAndWait("link={$lang['straddfk']}");
        $t->type('name', 'student_id_promo_fk');
        $t->addSelection('TableColumnList','label=id_promo');
        $t->click('add');
        $t->select('target', 'label=test_schema.promo');
        $t->clickAndWait("//input[@value='Add']");
        $t->addSelection('TableColumnList', 'label=id');
        $t->click('add');
        $t->select('upd_action', 'label=CASCADE');
        $t->select('del_action', 'label=RESTRICT');
        $t->clickAndWait("//input[@value='Add']");
        $t->assertText("//p[@class='message']", $lang['strfkadded']);

        /** 1' **/
        $t->addComment('1.2. Add 2nd Foreign Keys');
        $t->clickAndWait("link={$lang['straddfk']}");
        $t->type('name', 'fk_to_drop');
        $t->addSelection('TableColumnList', 'label=id_promo');
        $t->click('add');
        $t->select('target','label=test_schema.promo');
        $t->clickAndWait("//input[@value='Add']");
        $t->addSelection('TableColumnList', 'label=id');
        $t->click('add');
        $t->select('upd_action', 'label=CASCADE');
        $t->select('del_action', 'label=RESTRICT');
        $t->clickAndWait("//input[@value='Add']");
        $t->assertText("//p[@class='message']", $lang['strfkadded']);

        /* 2 */
        $t->addComment('2. Add check constraint');
        $t->clickAndWait("link={$lang['straddcheck']}");
        $t->type('name', 'check_to_drop');
        $t->type('definition', 'extract(year from birthday) &lt; 2000');
        $t->clickAndWait('ok');
        $t->assertText("//p[@class='message']", $lang['strcheckadded']);

        /* 3 */
        $t->addComment('3. Add unique key');
        $t->clickAndWait("link={$lang['stradduniq']}");
        $t->type('name', 'unique_to_drop');
        $t->addSelection('TableColumnList', 'label=name');
        $t->click('add');
        $t->clickAndWait("//input[@value='{$lang['stradd']}']");
        $t->assertText("//p[@class='message']", $lang['struniqadded']);

        /* 4 */
        $t->addComment('4. Drop PK before creating it again');
        $t->clickAndWait("//tr/td/pre[text()='PRIMARY KEY (id)']/../../td/a[text()='{$lang['strdrop']}']");
        $t->clickAndWait('drop');
        $t->assertText("//p[@class='message']", $lang['strconstraintdropped']);

        /* 5 */
        $t->addComment('5. Add primary key');
        $t->clickAndWait("link={$lang['straddpk']}");
        $t->type('name', 'student_pk');
        $t->addSelection('TableColumnList', 'label=id');
        $t->click('add');
        $t->clickAndWait("//input[@value='Add']");
        $t->assertText("//p[@class='message']", $lang['strpkadded']);

        /* 6 */
        $t->addComment('6. Drop FK');
        $t->clickAndWait("//tr/td[text()='fk_to_drop']/../td/a[text()='{$lang['strdrop']}']");
        $t->clickAndWait('drop');
        $t->assertText("//p[@class='message']", $lang['strconstraintdropped']);

        /* 7 */
        $t->addComment('7. Drop unique');
        $t->clickAndWait("//tr/td[text()='unique_to_drop']/../td/a[text()='{$lang['strdrop']}']");
        $t->clickAndWait('drop');
        $t->assertText("//p[@class='message']", $lang['strconstraintdropped']);

        /* 8 */
        $t->addComment('8. Drop check');
        $t->clickAndWait("//tr/td[text()='check_to_drop']/../td/a[text()='{$lang['strdrop']}']");
        $t->clickAndWait('drop');
        $t->assertText("//p[@class='message']", $lang['strconstraintdropped']);

        $t->logout();
        unset($t);
    }
