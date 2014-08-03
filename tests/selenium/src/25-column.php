<?php
    $test_title = 'Column tests';

    if (isset($_GET['run'])) {
        global $lang;
        require '../config.test.php';
        require '../testBuilder.class.php';
        /**
		 * 1/ Add column
		 * 2/ Alter column
		 * 3/ Alter column bad
		 * 4/ Drop column
		 **/
        $t = new TestBuilder($test_title,
            'Add/Alter/Drop a column'
        );

        $t->login($admin_user, $admin_user_pass);

        /** 1 **/
        $t->addComment('1. add column');
        $t->clickAndWait("link={$lang['strdatabases']}");
        $t->clickAndWait("link={$testdb}");
        $t->clickAndWait("link={$lang['strschemas']}");
        $t->clickAndWait('link=public');
        $t->clickAndWait("link={$lang['strtables']}");
        $t->clickAndWait('link=student');
        $t->clickAndWait("link={$lang['strcolumns']}");
        $t->clickAndWait("link={$lang['straddcolumn']}");
        $t->type('field', 'new_col');
        $t->select('type', 'label=integer');
        $t->type('default', 0);
        $t->type('comment', 'test col to drop');
        $t->clickAndWait("//input[@value='Add']");
        $t->assertText("//p[@class='message']", $lang['strcolumnadded']);
        $t->assertText("//tr/td/a[text()='new_col']", 'new_col');
        $t->assertText("//tr/td/a[text()='new_col']/../../td[2]", 'integer');
        if ($t->data->hasCreateFieldWithConstraints()) {
            $t->assertText("//tr/td/a[text()='new_col']/../../td[3]", '');
            $t->assertText("//tr/td/a[text()='new_col']/../../td[4]", '0');
        }
        $t->assertText("//tr/td/a[text()='new_col']/../../td[10]", 'test col to drop');

        /** 2 **/
        $t->addComment('2. alter column');
        $t->clickAndWait("link={$lang['strcolumns']}");
        $t->clickAndWait('link=new_col');
        $t->clickAndWait("link={$lang['stralter']}");
        $t->type('field', 'altered_col');
        $current_type='integer';
        $current_default='1';
        if ($t->data->hasAlterColumnType()) {
            $t->select('type', 'label=character');
            $t->type('length', 1);
            $t->type('default', "'-'");
            $current_type='character(1)';
            $current_default="'-'";
        } else {
            $t->type('default', '2');
            $current_default='2';
        }
        $t->check('notnull');
        $t->type('comment', 'altered col to drop');
        $t->clickAndWait("//input[@value='Alter']");
        $t->assertText("//p[@class='message']", $lang['strcolumnaltered']);
        $t->assertText("//p[@class='comment']", 'altered col to drop');
        $t->assertText("//tr/th[text()='{$lang['strcolumn']}']/../../tr[2]/td[1]", 'altered_col');
        $t->assertText("//tr/td[1 and text()='altered_col']/../td[2]", $current_type);
        $t->assertText("//tr/td[1 and text()='altered_col']/../td[3]", 'NOT NULL');
        $t->assertText("//tr/td[1 and text()='altered_col']/../td[4]", "{$current_default}*");

        /** 3 **/
        $t->addComment('3. alter column fail');
        $t->clickAndWait("//div[@class='trail']/descendant::a[@title='{$lang['strtable']}']/span[@class='label' and text()='student']");
        $t->clickAndWait('link=name');
        $t->clickAndWait("link={$lang['stralter']}");
        $t->type('default', 'Bad default value');
        $t->clickAndWait("//input[@value='Alter']");
        $t->assertText("//p[@class='message']", $lang['strcolumnalteredbad']);
        $t->clickAndWait('cancel');

        /** 4 **/
        $t->addComment('4. drop column');
        $t->clickAndWait("//div[@class='trail']/descendant::a[@title='{$lang['strtable']}']/span[@class='label' and text()='student']");
        $t->clickAndWait('link=altered_col');
        $t->clickAndWait("link={$lang['strdrop']}");
        $t->click('cascade');
        $t->clickAndWait('drop');
        $t->assertText("//p[@class='message']", $lang['strcolumndropped']);
        $t->assertErrorOnNext("Element //tr/td/a[text()='altered_col'] not found");
        $t->clickAndWait("//tr/td/a[text()='altered_col']"); //fail

        $t->logout();
        unset($t);
    }
