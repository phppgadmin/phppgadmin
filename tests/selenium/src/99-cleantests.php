<?php
	$test_title = 'Cleaner tests';

	if (isset($_GET['run'])) {
		global $lang;
		require('../config.test.php');
		require('../testBuilder.class.php');
		/*
		 * 1/ login as user and try to drop database -> fail
		 * 2/ logout / login as admin_user and drop user role/user
		 * 3/ try to drop himself -> fail
		 * 4/ drop domain -> fail table promo depend on it
		 * 5/ drop domain with cascade, test if promo.year disappeared
		 * 6/ drop table student with cascade using the action button
		 * 7/ drop table promo using the button from tblproperties
		 * 8/ drop test database
		 * 9/ logout & login as superuser and drop admin_user
		 */
		$t = new TestBuilder($test_title,
			'Clean every created stuff for test.'
		);

	/** 1 **/
		$t->addComment('1. login as user and try to drop database -> fail');
		$t->login($user, $user_pass);
		$t->clickAndWait("link={$lang['strdatabases']}");
		$t->clickAndWait("//tr/td/a[text()='{$testdb}']/../../td/a[text()='{$lang['strdrop']}']");
		$t->clickAndWait('drop');
		$t->assertText('//p[@class=\'message\']', $lang['strdatabasedroppedbad']);

	/** 2 **/
		$t->addComment('2. logout / login as admin_user and drop user role/user');
		$t->logout();
		$t->login($admin_user, $admin_user_pass);
		if ($t->data->hasRoles()) {
			$t->clickAndWait("link={$lang['strroles']}");
			$t->clickAndWait("//tr/td/a[text()='{$user}']/../../td/a[text()='{$lang['strdrop']}']");
			$t->clickAndWait('drop');
			$t->assertText('//p[@class=\'message\']', $lang['strroledropped']);
		} else {
			$t->clickAndWait("link={$lang['strusers']}");
			$t->clickAndWait("//tr/td[text()='{$user}']/../td/a[text()='{$lang['strdrop']}']");
			$t->clickAndWait('drop');
			$t->assertText('//p[@class=\'message\']', $lang['struserdropped']);
		}

	/** 3 **/
		$t->addComment('3. try to drop himself -> fail');
		if ($t->data->hasRoles()) {
			$t->clickAndWait("link={$lang['strroles']}");
			$t->clickAndWait("link={$admin_user}");
			$t->assertText("//div[@class='trail']/descendant::a[@title='{$lang['strrole']}']/span[@class='label']", $admin_user);
			$t->clickAndWait("link={$lang['strdrop']}");
			$t->clickAndWait('drop');
			$t->assertText('//p[@class=\'message\']', $lang['strroledroppedbad']);
		} else {
			$t->clickAndWait("link={$lang['strusers']}");
			$t->clickAndWait("//tr/td[text()='{$admin_user}']/../td/a[text()='{$lang['strdrop']}']");
			$t->clickAndWait('drop');
			$t->assertText('//p[@class=\'message\']', $lang['struserdroppedbad']);
		}

	/** 4 **/
		$t->addComment('4. drop domain -> fail table promo depend on it');
		$t->clickAndWait("link={$lang['strdatabases']}");
		$t->clickAndWait("link={$testdb}");
		$t->clickAndWait("link={$lang['strschemas']}");
		$t->clickAndWait("link=test_schema");
		$t->clickAndWait("link={$lang['strdomains']}");
		$t->clickAndWait("//tr/td/a[text()='year']/../../td/a[text()='{$lang['strdrop']}']");
		$t->clickAndWait('drop');
		$t->assertText('//p[@class=\'message\']', $lang['strdomaindroppedbad']);

	/** 5 **/
		$t->addComment('5. drop domain with cascade, test if promo.year disapeared');
		$t->clickAndWait("//div[@class='trail']/descendant::a[@title='{$lang['strdatabase']}']/span[@class='label' and text()='{$testdb}']");
		$t->clickAndWait("link={$lang['strschemas']}");
		$t->clickAndWait("link=test_schema");
		$t->clickAndWait("link={$lang['strdomains']}");
		$t->clickAndWait("//tr/td/a[text()='year']/../../td/a[text()='{$lang['strdrop']}']");
		$t->check('cascade');
		$t->clickAndWait('drop');
		$t->assertText('//p[@class=\'message\']', $lang['strdomaindropped']);
		$t->clickAndWait("//div[@class='trail']/descendant::a[@title='{$lang['strschema']}']/span[@class='label' and text()='test_schema']");
		$t->clickAndWait("link={$lang['strtables']}");
		$t->clickAndWait("link=promo");
		$t->clickAndWait("link={$lang['strcolumns']}");
		$t->assertErrorOnNext('Element link=year not found');
		$t->clickAndWait("link=year");

	/** 6 **/
		$t->addComment('6. drop table student with cascade using the action button');
		$t->clickAndWait("//div[@class='trail']/descendant::a[@title='{$lang['strserver']}']/span[@class='label' and text()='{$t->server['desc']}']");
		$t->clickAndWait("link={$lang['strdatabases']}");
		$t->clickAndWait("link={$testdb}");
		$t->clickAndWait("link={$lang['strschemas']}");
		$t->clickAndWait("link=public");
		$t->clickAndWait("//tr/td/a[text()='student']/../../td/a[text()='{$lang['strdrop']}']");
		$t->check('cascade');
		$t->clickAndWait('drop');
		$t->assertText('//p[@class=\'message\']', $lang['strtabledropped']);

	/** 7 **/
		$t->addComment('7. drop table promo using the button from tblproperties');
		$t->clickAndWait("//div[@class='trail']/descendant::a[@title='{$lang['strserver']}']/span[@class='label' and text()='{$t->server['desc']}']");
		$t->clickAndWait("link={$lang['strdatabases']}");
		$t->clickAndWait("link={$testdb}");
		$t->clickAndWait("link={$lang['strschemas']}");
		$t->clickAndWait("link=test_schema");
		$t->clickAndWait("link={$lang['strtables']}");
		$t->clickAndWait("link=promo");
		$t->clickAndWait("//ul[@class='navlink']/li/a[text()='{$lang['strdrop']}']");
		$t->clickAndWait('drop');
		$t->assertText('//p[@class=\'message\']', $lang['strtabledropped']);

	/** 8 **/
		$t->addComment('8. drop test database');
		$t->clickAndWait("//div[@class='trail']/descendant::a[@title='{$lang['strserver']}']/span[@class='label' and text()='{$t->server['desc']}']");
		$t->clickAndWait("link={$lang['strdatabases']}");
		$t->clickAndWait("//tr/td/a[text()='{$testdb}']/../../td/a[text()='{$lang['strdrop']}']");
		$t->clickAndWait('drop');
		$t->assertText('//p[@class=\'message\']', $lang['strdatabasedropped']);

	/** 9 **/
		$t->addComment('9. logout & login as superuser and drop admin_user');
		$t->logout();
		$t->login($super_user[$t->server['desc']], $super_pass[$t->server['desc']]);

		if ($t->data->hasRoles()) {
			/* drop adminuser */
			$t->clickAndWait("link={$lang['strroles']}");
			$t->clickAndWait("//tr/td/a[text()='{$admin_user}']/../../td/a[text()='{$lang['strdrop']}']");
			$t->clickAndWait('drop');
			$t->assertText('//p[@class=\'message\']', $lang['strroledropped']);
		} else {
			$t->clickAndWait("link={$lang['strusers']}");
			$t->clickAndWait("//tr/td[text()='{$admin_user}']/../td/a[text()='{$lang['strdrop']}']");
			$t->clickAndWait('drop');
			$t->assertText('//p[@class=\'message\']', $lang['struserdropped']);
		}

		$t->logout();
		unset($t);
	}
?>
