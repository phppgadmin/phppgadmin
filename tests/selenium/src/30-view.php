<?php
    $test_title = 'View tests';

    if (isset($_GET['run'])) {
        global $lang;
        require '../config.test.php';
        require '../testBuilder.class.php';
        /**
		 * 1/ Create a view
		 * 2/ Alter a view
		 * 3/ Drop a view
		 **/
        $t = new TestBuilder($test_title,
            'Add/Alter/Drop a view'
        );

        $t->login($admin_user, $admin_user_pass);

        /** 1 **/
        $t->addComment('1. Create the view');
        $t->clickAndWait("link={$lang['strdatabases']}");
        $t->clickAndWait("link={$testdb}");
        $t->clickAndWait("link={$lang['strschemas']}");
        $t->clickAndWait('link=public');
        $t->clickAndWait("link={$lang['strviews']}");
        $t->clickAndWait("link={$lang['strcreateview']}");
        $t->type('formView', 'student_promo');
        $t->type('formDefinition', 'SELECT s.id, name, birthday, resume, spe, year
			FROM student AS s
			JOIN test_schema.promo AS p ON (s.id_promo=p.id)');
        $t->type('formComment', 'students and their promotion');
        $t->clickAndWait("//input[@value='Create']");
        $t->assertText("//p[@class='message']", "{$lang['strviewcreated']}");
        $t->assertText("//tr/td[2]/a[text()='student_promo']/../../td[2]", 'student_promo');
        $t->assertText("//tr/td[2]/a[text()='student_promo']/../../td[3]", $admin_user);
        $t->assertText("//tr/td[2]/a[text()='student_promo']/../../td[8]", 'students and their promotion');
        $t->assertText("//div[@class='trail']/descendant::a[@title='{$lang['strschema']}']/span[@class='label']", 'public');

        /** 2 **/
        $t->addComment('2. Alter the view');
        $t->clickAndWait("link={$lang['strviews']}");
        $t->clickAndWait('link=student_promo');
        $t->clickAndWait("//ul[@class='navlink']/li/a[text()='{$lang['stralter']}']");
        //Alter name
        $t->addComment('2.1. Alter view\'s name');
        $t->type('name', 'student_promo_renamed');
        $t->clickAndWait('alter');
        $t->assertText("//p[@class='message']", $lang['strviewaltered']);
        $t->assertText("//div[@class='trail']/descendant::a[@title='{$lang['strview']}']/span[@class='label']", 'student_promo_renamed');
        //Alter comment
        $t->addComment('2.2. Alter view\'s comment');
        $t->clickAndWait("//ul[@class='navlink']/li/a[text()='{$lang['stralter']}']");
        $t->type('comment', 'students and their promotion (altered)');
        $t->clickAndWait('alter');
        $t->assertText("//p[@class='message']", $lang['strviewaltered']);
        $t->assertText("//p[@class='comment']", 'students and their promotion (altered)');
        // Alter schema
        $current_shema='public';
        if ($t->data->hasAlterTableSchema()) {
            $t->addComment('2.3. Alter view\'s schema');
            $t->clickAndWait("//ul[@class='navlink']/li/a[text()='{$lang['stralter']}']");
            $t->select('newschema', 'label=test_schema');
            $t->clickAndWait('alter');
            $t->assertText("//p[@class='message']", $lang['strviewaltered']);
            $t->assertText("//div[@class='trail']/descendant::a[@title='{$lang['strschema']}']/span[@class='label']", 'test_schema');
            $current_shema='test_schema';
        }
        // Alter owner
        $t->addComment('2.4. Alter view\'s owner');
        $t->clickAndWait("//ul[@class='navlink']/li/a[text()='{$lang['stralter']}']");
        $t->select('owner', "label={$user}");
        $t->clickAndWait('alter');
        $t->assertText("//p[@class='message']", $lang['strviewaltered']);
        $t->clickAndWait("//div[@class='trail']/descendant::a[@title='{$lang['strschema']}']/span[@class='label' and text()='{$current_shema}']");
        $t->clickAndWait("link={$lang['strviews']}");
        $t->assertText("//tr/td[2]/a[text()='student_promo_renamed']/../../td[3]", $user);
        // Alter back everything
        $t->addComment('2.5. Alter back everything');
        $t->clickAndWait('link=student_promo_renamed');
        $t->clickAndWait("//ul[@class='navlink']/li/a[text()='{$lang['stralter']}']");
        $t->select('owner', "label={$admin_user}");
        $t->type('name', 'student_promo');
        if ($t->data->hasAlterTableSchema()) {
            $t->select('newschema', 'label=public');
        }
        $t->type('comment', 'students and their promotion');
        $t->clickAndWait('alter');
        $t->assertText("//p[@class='message']", $lang['strviewaltered']);
        $t->assertText("//p[@class='comment']", 'students and their promotion');
        $t->assertText("//div[@class='trail']/descendant::a[@title='{$lang['strview']}']/span[@class='label']", 'student_promo');
        $t->clickAndWait("//div[@class='trail']/descendant::a[@title='{$lang['strschema']}']/span[@class='label' and text()='public']");
        $t->clickAndWait("link={$lang['strviews']}");
        $t->assertText("//tr/td[2]/a[text()='student_promo']/../../td[3]", $admin_user);

        /** 3 **/
        $t->addComment('3. Drop the view');
        $t->clickAndWait("//tr/td/a[text()='student_promo']/../../td/a[text()='{$lang['strdrop']}']");
        $t->clickAndWait('drop');
        $t->assertText("//p[@class='message']", $lang['strviewdropped']);
        $t->assertErrorOnNext("Element //tr/td/a[text()='student_promo'] not found");
        $t->clickAndWait("//tr/td/a[text()='student_promo']"); //fail

        $t->logout();
        unset($t);
    }
