<?php
	$test_title = 'Table tests';

	if (isset($_GET['run'])) {
		global $lang;
		require('../config.test.php');
		require('../testBuilder.class.php');
		/*
		 * 1/ create a table student in public
		 * 2/ create table promo in test_schema
		 * 3/ create table like student in test_schema
		 * 4/ alter each param one by one on test_toalter
		 * 5/ alter back test_toalter in one step
		 * NB: dropping a table is in the cleantests.php tests
		 */
		$t = new TestBuilder($test_title,
			'Create tables, make some alterations...'
		);

		$t->login($admin_user, $admin_user_pass);

	/** 1 **/
		$t->addComment('1. create a table student in public');
		$t->clickAndWait("link={$lang['strdatabases']}");
		$t->clickAndWait("link={$testdb}");
		$t->clickAndWait("link={$lang['strschemas']}");
		$t->clickAndWait("link=public");
		$t->clickAndWait("link={$lang['strtables']}");
		$t->clickAndWait("link={$lang['strcreatetable']}");
		$t->type('name','student');
		$t->type('fields', '5');
		$t->type('tblcomment', 'student table');
		$t->clickAndWait("//input[@value='{$lang['strnext']}']");
		$t->type('field[0]', 'id');
		$t->select('types0', 'label=SERIAL');
		$t->click('primarykey[0]');
		$t->type('field[1]', 'id_promo');
		$t->select('types1', 'label=integer');
		$t->type('field[2]', 'name');
		$t->select('types2', 'label=character varying');
		$t->type('lengths2', '20');
		$t->click('notnull[2]');
		$t->type('field[3]', 'birthday');
		$t->select('types3', 'label=date');
		$t->type('field[4]', 'resume');
		$t->select('types4', 'label=text');
		$t->clickAndWait("//input[@value='{$lang['strcreate']}']");
		$t->assertText("//p[@class='message']", $lang['strtablecreated']);

	/** 2 **/
		$t->addComment('2. create table promo in test_schema');
		$t->clickAndWait("//div[@class='trail']/descendant::a[@title='{$lang['strdatabase']}']/span[@class='label' and text()='{$testdb}']");
		$t->clickAndWait("link={$lang['strschemas']}");
		$t->clickAndWait("link=test_schema");
		$t->clickAndWait("link={$lang['strtables']}");
		$t->clickAndWait("link={$lang['strcreatetable']}");
		$t->type('name', 'promo');
		$t->type('fields', '3');
		$t->type('tblcomment', 'promotion\'s year & speciality');
		$t->clickAndWait("//input[@value='Next &gt;']");
		$t->type('field[0]', 'id');
		$t->select('types0', 'label=SERIAL');
		$t->click('primarykey[0]');
		$t->type('field[1]', 'spe');
		$t->select('types1', 'label=character varying');
		$t->type('lengths1','20');
		$t->click('notnull[1]');
		$t->type('field[2]', 'year');
		$t->select('types2', "label=regexp:\"?year\"?"); // 8.3 does not quote domains
		$t->click('notnull[2]');
		$t->clickAndWait("//input[@value='{$lang['strcreate']}']");
		$t->assertText("//p[@class='message']", $lang['strtablecreated']);

	/** 3 **/
		$t->addComment('3. create table like student in test_schema');
		if ($t->data->hasCreateTableLike()) {
			$t->clickAndWait("//div[@class='trail']/descendant::a[@title='{$lang['strdatabase']}']/span[@class='label' and text()='{$testdb}']");
			$t->clickAndWait("link={$lang['strschemas']}");
			$t->clickAndWait("link=test_schema");
			$t->clickAndWait("link={$lang['strtables']}");
			$t->clickAndWait("link={$lang['strcreatetablelike']}");
			$t->type('name', 'test_toalter');
			$t->select('like','label="public"."student"');
			$t->click('withdefaults');
			if ($t->data->hasCreateTableLikeWithConstraints())
				$t->click('withconstraints');
			if ($t->data->hasCreateTableLikeWithIndexes())
				$t->click('withindexes');
			$t->clickAndWait("//input[@value='{$lang['strcreate']}']");
			$t->assertText("//p[@class='message']", $lang['strtablecreated']);
		}
		else {
			/*no create like ? create it anyway for the next steps*/
			$t->clickAndWait("//div[@class='trail']/descendant::a[@title='{$lang['strdatabase']}']/span[@class='label' and text()='{$testdb}']");
			$t->clickAndWait("link={$lang['strschemas']}");
			$t->clickAndWait("link=test_schema");
			$t->clickAndWait("link={$lang['strtables']}");
			$t->clickAndWait("link={$lang['strcreatetable']}");
			$t->type('name','test_toalter');
			$t->type('fields', '1');
			$t->type('tblcomment', 'test table');
			$t->clickAndWait("//input[@value='{$lang['strnext']}']");
			$t->type('field[0]', 'id');
			$t->select('types0', 'label=SERIAL');
			$t->click('primarykey[0]');
			$t->clickAndWait("//input[@value='{$lang['strcreate']}']");
			$t->assertText("//p[@class='message']", $lang['strtablecreated']);
		}

	/** 4 **/
		$t->addComment('4. alter each param one by one on test_toalter');
		/*table name*/
		$t->addComment('4.1. alter table name');
		$t->clickAndWait("//div[@class='trail']/descendant::a[@title='{$lang['strdatabase']}']/span[@class='label' and text()='{$testdb}']");
		$t->clickAndWait("link={$lang['strschemas']}");
		$t->clickAndWait("link=test_schema");
		$t->clickAndWait("link={$lang['strtables']}");
		$t->clickAndWait("link=test_toalter");
		$t->clickAndWait("link={$lang['strcolumns']}"); /*alter using the link in the column page*/
		$t->clickAndWait("//ul[@class='navlink']/li/a[text()='{$lang['stralter']}']");
		$t->type('name', 'test_renamed');
		$t->clickAndWait('alter');
		$t->assertText("//p[@class='message']", $lang['strtablealtered']);
		$t->assertText("//div[@class='trail']/descendant::a[@title='{$lang['strtable']}']/span[@class='label']", 'test_renamed');

		/*table comment*/
		$t->addComment('4.2. alter table comment');
		$t->clickAndWait("//div[@class='trail']/descendant::a[@title='{$lang['strdatabase']}']/span[@class='label' and text()='{$testdb}']");
		$t->clickAndWait("link={$lang['strschemas']}");
		$t->clickAndWait("link=test_schema");
		$t->clickAndWait("link={$lang['strtables']}");
		$t->clickAndWait("link=test_renamed");
		$t->clickAndWait("link={$lang['strcolumns']}"); /*alter using the link in the column page*/
		$t->clickAndWait("//ul[@class='navlink']/li/a[text()='{$lang['stralter']}']");
		$t->type('comment', 'altered comment');
		$t->clickAndWait('alter');
		$t->assertText("//p[@class='message']", $lang['strtablealtered']);
		$t->assertText("//p[@class='comment']", 'altered comment');

		/*table owner*/
		$t->addComment('4.3. alter table owner');
		$t->clickAndWait("//div[@class='trail']/descendant::a[@title='{$lang['strdatabase']}']/span[@class='label' and text()='{$testdb}']");
		$t->clickAndWait("link={$lang['strschemas']}");
		$t->clickAndWait("link=test_schema");
		$t->clickAndWait("link={$lang['strtables']}");
		$t->clickAndWait("//tr/td/a[text()='test_renamed']/../../td/a[text()='{$lang['stralter']}']"); /*alter using the link in the table list page*/
		$t->select('owner', "label={$user}");
		$t->clickAndWait('alter');
		$t->assertText("//p[@class='message']", $lang['strtablealtered']);
		$t->clickAndWait("//div[@class='trail']/descendant::a[@title='{$lang['strschema']}']/span[@class='label' and text()='test_schema']");
		$t->clickAndWait("link={$lang['strtables']}");
		$t->assertText("//tr/td[2]/a[text()='test_renamed']/../../td[3]", $user);

		/*alter schema*/
		if ($t->data->hasAlterTableSchema()) {
			$t->addComment('4.4. alter table schema');
			$t->clickAndWait("//div[@class='trail']/descendant::a[@title='{$lang['strdatabase']}']/span[@class='label' and text()='{$testdb}']");
			$t->clickAndWait("link={$lang['strschemas']}");
			$t->clickAndWait("link=test_schema");
			$t->clickAndWait("link={$lang['strtables']}");
			$t->clickAndWait("//tr/td/a[text()='test_renamed']/../../td/a[text()='{$lang['stralter']}']"); /*alter using the link in the table list page*/
			$t->select('newschema', 'label=public');
			$t->clickAndWait('alter');
			$t->assertText("//p[@class='message']", $lang['strtablealtered']);
			$t->assertText("//div[@class='trail']/descendant::a[@title='{$lang['strschema']}']/span[@class='label']", 'public');
		}

	/** 5 **/
		$t->addComment('5. alter back test_toalter in one step');
		$t->clickAndWait("//div[@class='trail']/descendant::a[@title='{$lang['strdatabase']}']/span[@class='label' and text()='{$testdb}']");
		$t->clickAndWait("link={$lang['strschemas']}");
		if ($t->data->hasAlterTableSchema())
			$t->clickAndWait("link=public");
		else
			$t->clickAndWait("link=test_schema");
		$t->clickAndWait("link={$lang['strtables']}");
		$t->clickAndWait("link=test_renamed");
		$t->clickAndWait("link={$lang['strcolumns']}");
		$t->clickAndWait("//ul[@class='navlink']/li/a[text()='{$lang['stralter']}']");
		$t->type('name', 'test');

		$t->type('comment', 'normal comment');
		if ($t->data->hasAlterTableSchema())
			$t->select('newschema', 'label=test_schema');
		$t->select('owner', "label={$admin_user}");
		$t->clickAndWait('alter');
		$t->assertText("//p[@class='message']", $lang['strtablealtered']);
		$t->assertText("//div[@class='trail']/descendant::a[@title='{$lang['strtable']}']/span[@class='label']", 'test');
		$t->assertText("//p[@class='comment']", 'normal comment');
		$t->assertText("//div[@class='trail']/descendant::a[@title='{$lang['strschema']}']/span[@class='label']", 'test_schema');
		$t->clickAndWait("//div[@class='trail']/descendant::a[@title='{$lang['strschema']}']/span[@class='label' and text()='test_schema']");
		$t->clickAndWait("link={$lang['strtables']}");
		$t->assertText("//tr/td[2]/a[text()='test']/../../td[3]", $admin_user);

		$t->logout();
		unset($t);
	}
?>
