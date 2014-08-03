<?php
    $test_title = 'Sequence tests';

    if (isset($_GET['run'])) {
        global $lang;
        require '../config.test.php';
        require '../testBuilder.class.php';
        /*
		 * 1/ Create a sequence
		 * 2/ increment, reset sequence qnd set value
		 * 3/ alter sequence
		 * 4/ drop sequence
		 */
        $t = new TestBuilder($test_title,
            'Create alter and drop sequence...'
        );

        $t->login($admin_user, $admin_user_pass);

    /** 1 **/
        $t->addComment('1. Create a sequence');
        $t->clickAndWait("link={$lang['strdatabases']}");
        $t->clickAndWait("link={$testdb}");
        $t->clickAndWait("link={$lang['strschemas']}");
        $t->clickAndWait('link=public');
        $t->clickAndWait("link={$lang['strsequences']}");
        $t->clickAndWait("link={$lang['strcreatesequence']}");
        $t->type('formSequenceName', 'testcase_seq');
        $t->type('formIncrement', '2');
        $t->type('formMinValue', '1');
        $t->type('formMaxValue', '100');
        $t->type('formStartValue', '1');
        $t->type('formCacheValue', '1');
        $t->click('formCycledValue');
        $t->clickAndWait('create');
        $t->assertText("//p[@class='message']", $lang['strsequencecreated']);
        $t->clickAndWait('link=testcase_seq');
        $t->assertText("//tr/th[text()='{$lang['strname']}' and @class='data']/../../tr/td[1]", 'testcase_seq');
        $i=2;
        $t->assertText("//tr/td[text()='testcase_seq']/../td[". $i++ ."]", '1');
        if ($t->data->hasAlterSequenceStart())
            $t->assertText("//tr/td[text()='testcase_seq']/../td[". $i++ ."]", '1');
        $t->assertText("//tr/td[text()='testcase_seq']/../td[". $i++ ."]", '2');
        $t->assertText("//tr/td[text()='testcase_seq']/../td[". $i++ ."]", '100');
        $t->assertText("//tr/td[text()='testcase_seq']/../td[". $i++ ."]", '1');
        $t->assertText("//tr/td[text()='testcase_seq']/../td[". $i++ ."]", '1');
        $i++; // we ignore log_count
        $t->assertText("//tr/td[text()='testcase_seq']/../td[". $i ."]", $lang['stryes']);

    /** 2 **/
        $t->addComment('2. increment, reset sequence and set value');
        $t->clickAndWait("link={$lang['strsetval']}");
        $t->type('nextvalue', '2');
        $t->clickAndWait('setval');
        $t->assertText("//p[@class='message']", $lang['strsequencesetval']);
        if ($t->data->hasAlterSequenceStart()) $i = 3;
        else $i = 2;
        $t->assertText("//tr/td[text()='testcase_seq']/../td[$i]", '2');
        $t->clickAndWait("link={$lang['strnextval']}");
        $t->assertText("//p[@class='message']", $lang['strsequencenextval']);
        $t->assertText("//tr/td[text()='testcase_seq']/../td[$i]", '4');
        $t->clickAndWait("link={$lang['strreset']}");
        $t->assertText("//p[@class='message']", $lang['strsequencereset']);
        $t->assertText("//tr/td[text()='testcase_seq']/../td[$i]", '1');

    /** 3 **/
        $t->addComment('3. alter sequence');
        $t->clickAndWait("//div[@class='trail']/descendant::a[@title='{$lang['strdatabase']}']/span[@class='label' and text()='{$testdb}']");
        $t->clickAndWait("link={$lang['strschemas']}");
        $t->clickAndWait('link=public');
        $t->clickAndWait("link={$lang['strsequences']}");
        $t->clickAndWait('link=testcase_seq');
        $t->clickAndWait("link={$lang['stralter']}");
        $t->type('name', 'testcase_renamed_seq');
        $t->select('owner', "label={$user}");
        if ($t->data->hasAlterSequenceSchema())
            $t->select('newschema', 'label=test_schema');
        $t->type('comment', 'test comment on testcase_renamed_seq');
        if ($t->data->hasAlterSequenceStart())
            $t->type('formStartValue', 10);
        $t->type('formRestartValue', 20);
        $t->type('formIncrement', 3);
        $t->type('formMaxValue', 104);
        $t->type('formMinValue', 5);
        $t->type('formCacheValue', 6);
        $t->uncheck('formCycledValue');
        $t->clickAndWait('alter');
        $t->assertText("//p[@class='message']", $lang['strsequencealtered']);
        if ($t->data->hasAlterSequenceSchema())
            $t->assertText("//div[@class='trail']/descendant::a[@title='{$lang['strschema']}']/span[@class='label']", 'test_schema');
        $t->assertText("//p[@class='comment']", 'test comment on testcase_renamed_seq');
        $t->assertText("//tr/th[text()='{$lang['strname']}' and @class='data']/../../tr/td[1]", 'testcase_renamed_seq');
        $i = 2;
        if ($t->data->hasAlterSequenceStart())
            $t->assertText("//tr/td[text()='testcase_renamed_seq']/../td[". $i++ ."]", '10');
        $t->assertText("//tr/td[text()='testcase_renamed_seq']/../td[". $i++ ."]", '20');
        $t->assertText("//tr/td[text()='testcase_renamed_seq']/../td[". $i++ ."]", '3');
        $t->assertText("//tr/td[text()='testcase_renamed_seq']/../td[". $i++ ."]", '104');
        $t->assertText("//tr/td[text()='testcase_renamed_seq']/../td[". $i++ ."]", '5');
        $t->assertText("//tr/td[text()='testcase_renamed_seq']/../td[". $i++ ."]", '6');
        $i++; // we ignore log_count
        $t->assertText("//tr/td[text()='testcase_renamed_seq']/../td[". $i++ ."]", $lang['strno']);

        if ($t->data->hasAlterSequenceSchema())
            $t->clickAndWait("//div[@class='trail']/descendant::a[@title='{$lang['strschema']}']/span[@class='label' and text()='test_schema']");
        else
            $t->clickAndWait("//div[@class='trail']/descendant::a[@title='{$lang['strschema']}']/span[@class='label' and text()='public']");
        $t->clickAndWait("link={$lang['strsequences']}");
        $t->assertText("//tr/td[2]/a[text()='testcase_renamed_seq']/../../td[3]", $user);

    /** 4 **/
        $t->addComment('4. drop sequence');
        $t->clickAndWait("link={$lang['strsequences']}");
        $t->clickAndWait("//tr/td/a[text()='testcase_renamed_seq']/../../td/a[text()='{$lang['strdrop']}']");
        $t->click('cascade');
        $t->clickAndWait('drop');
        $t->assertText("//p[@class='message']", $lang['strsequencedropped']);

        $t->logout();
        unset($t);
    }
