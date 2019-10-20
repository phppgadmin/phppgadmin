<?php
	$test_title = 'Schema tests';

	if (isset($_GET['run'])) {
		global $lang;
		require('../config.test.php');
		require('../testBuilder.class.php');
		/*
		 * 1/ create test schema
		 * 2/ alter its name, owner and comment
		 * NB: dropping the schema is in the cleantests.php tests
		 */
		$t = new TestBuilder($test_title,
			'Create and Alter schema.'
		);

		$t->login($admin_user, $admin_user_pass);

	/** 1 **/
		$t->addComment('1. create test schema');
		$t->clickAndWait("link={$lang['strdatabases']}");
		$t->clickAndWait("link={$testdb}");
		$t->clickAndWait("link={$lang['strschemas']}");
		$t->clickAndWait("link={$lang['strcreateschema']}");
		if ($t->data->hasAlterSchema()) {
			$t->type('formName', 'test_schema_toalter');
			if ($t->data->hasAlterSchemaOwner())
				$t->select('formAuth', $super_user[$t->server['desc']]);
			else $t->select('formAuth', $admin_user);
			$t->type('formComment', 'test schema comment to alter');
		}
		else {
			$t->type('formName', 'test_schema');
			$t->type('formComment', 'test schema comment');
		}
		$t->clickAndWait('create');
		$t->assertText('//p[@class=\'message\']', $lang['strschemacreated']);

	/** 2 **/
		$t->addComment('2. alter schema\'s name, owner and comment');
		if ($t->data->hasAlterSchema()) {
			$t->clickAndWait("link={$lang['strschemas']}");
			$t->clickAndWait("//tr/td/a[text()='test_schema_toalter']/../../td/a[text()='{$lang['stralter']}']");
			$t->type('name', 'test_schema');
			if ($t->data->hasAlterSchemaOwner())
				$t->select('owner', $admin_user);
			$t->type('comment', 'test schema');
			$t->clickAndWait('alter');
			$t->assertText('//p[@class=\'message\']', $lang['strschemaaltered']);
		}

		$t->logout();
		unset($t);
	}
?>
