<?php
	$test_title = 'Database tests';

	if (isset($_GET['run'])) {
		global $lang;
		require('../config.test.php');
		require('../testBuilder.class.php');
		/*
		 * 1/ create test database with altered name and owner
		 * 2/ alter its owner
		 * 3/ alter its name, owner and comment back to normal
		 * NB: dropping database is in the cleantests.php tests
		 */
		$t = new TestBuilder($test_title,
			'Create and Alter database.'
		);

		$t->login($admin_user, $admin_user_pass);

	/** 1 **/
		$t->addComment('1. create test database with altered name and owner');
		$t->clickAndWait("link={$lang['strdatabases']}");
		$t->clickAndWait("link={$lang['strcreatedatabase']}");

		/* db name */
		if ($t->data->hasAlterDatabaseRename())
			$t->type('formName', "{$testdb}toalter");
		else $t->type('formName', $testdb);
		/* template */
		$t->select('formTemplate', 'template0');
		/* encoding*/
		$t->select('formEncoding', 'SQL_ASCII');
		/* comment*/
		if ($t->data->hasSharedComments())
			$t->type('formComment', "database comment to alter");
		/* create */
		$t->clickAndWait("//input[@value='{$lang['strcreate']}']");
		$t->assertText("//p[@class='message']", $lang['strdatabasecreated']);

	/** 2 **/
		$t->addComment('2. alter DB\'s owner');
		if ($t->data->hasAlterDatabaseOwner()) {
			$t->clickAndWait("link={$lang['strdatabases']}");
			/* we don't need to check if hasAlterDatabaseRename here because
			 * hasAlterDatabase is actually calling it */
			$t->clickAndWait("//tr/td/a[text()='{$testdb}toalter']/../../td/a[text()='{$lang['stralter']}']");
			if ($t->data->hasAlterDatabaseOwner())
				$t->select('owner', $super_user[$t->server['desc']]);
			$t->clickAndWait('alter');
			$t->assertText("//p[@class='message']", $lang['strdatabasealtered']);
		}

	/** 3 **/
		$t->addComment('3. alter DB\'s name, owner and comment back to normal');
		if ($t->data->hasAlterDatabase()) {
			$t->clickAndWait("link={$lang['strdatabases']}");
			/* we don't need to check if hasAlterDatabaseRename here because
			 * hasAlterDatabase is actually calling it */
			$t->clickAndWait("//tr/td/a[text()='{$testdb}toalter']/../../td/a[text()='{$lang['stralter']}']");
			$t->type('newname', $testdb);
			/* owner */
			if ($t->data->hasAlterDatabaseOwner())
				$t->select('owner', $admin_user);
			/* comment */
			if ($t->data->hasSharedComments())
				$t->type('dbcomment', "database comment");
			/* alter */
			$t->clickAndWait('alter');
			$t->assertText("//p[@class='message']", $lang['strdatabasealtered']);
		}

		$t->logout();
		unset($t);
	}
?>
