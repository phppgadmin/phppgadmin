<?php
	$test_title = 'Roles, Users and Groups tests';

	if (isset($_GET['run'])) {
		global $lang;
		require('../config.test.php');
		require('../testBuilder.class.php');
		/*
		 * 1/ login as superuser
		 * 2/ create admin_user role/user with wrong pass conf -> fail
		 * 3/ create admin_user
		 * 4/ logout & login as admin_user
		 * 5/ create user role/user with altered name, pass and props
		 * 6/ alter user back to the normal value
		 * NB: dropping role tests are in the cleantests.php tests
		 */
		$t = new TestBuilder($test_title,
			'Create test admin role, test user role and tests Roles (or user/groups) features.'
		);

		/* 1 */
		$t->addComment('1. login as superuser');
		$t->login($super_user[$t->server['desc']], $super_pass[$t->server['desc']]);

		/* 2 */
		$t->addComment('2. create admin_user role/user with wrong pass conf -> fail');
		if ($t->data->hasRoles()) {
			$t->clickAndWait("link={$lang['strroles']}");
			$t->clickAndWait("link={$lang['strcreaterole']}");
			$t->type('formRolename', $admin_user);
			$t->click('formCreateRole');
			$t->click('formInherits');
			$t->click('formCanLogin');
		} else {
			$t->clickAndWait("link={$lang['strusers']}");
			$t->clickAndWait("link={$lang['strcreateuser']}");
			$t->type('formUsername', $admin_user);
		}
		$t->type('formPassword', "{$admin_user_pass}bad");
		$t->type('formConfirm', $admin_user_pass);
		$t->click('formSuper');
		$t->click('formCreateDB');
		$t->clickAndWait('create');
		$t->assertText("//p[@class='message']", $lang['strpasswordconfirm']);

		/* 3 */
		$t->addComment('3. create admin_user');
		$t->type('formPassword', $admin_user_pass);
		$t->type('formConfirm', $admin_user_pass);
		$t->clickAndWait('create');
		if ($t->data->hasRoles())
			$t->assertText("//p[@class='message']", $lang['strrolecreated']);
		else
			$t->assertText("//p[@class='message']", $lang['strusercreated']);

		/* 4 */
		$t->addComment('4. logout & login as admin_user');
		$t->logout();
		$t->login($admin_user, $admin_user_pass);


		/* 5 */
		$current_user="{$user}toalter";
		$t->addComment('5. create user role/user with altered name, pass and props');
		if ($t->data->hasRoles()) {
			$t->clickAndWait("link={$lang['strroles']}");
			$t->clickAndWait("link={$lang['strcreaterole']}");
			$t->type('formRolename', "{$user}toalter");
			$t->check('formCanLogin');
			$t->check('formCreateRole'); // will be revert
			$t->check('formInherits'); // will be revert
		} else {
			$t->clickAndWait("link={$lang['strusers']}");
			$t->clickAndWait("link={$lang['strcreateuser']}");
			if ($t->data->hasUserRename())
				$t->type('formUsername', "{$user}toalter");
			else {
				$t->type('formUsername', $user);
				$current_user=$user;
			}
		}
		$t->check('formSuper'); // will be revert
		$t->check('formCreateDB'); // will be revert
		$t->type('formPassword', "{$user_pass}toalter");
		$t->type('formConfirm', "{$user_pass}toalter");
		$t->clickAndWait('create');
		if ($t->data->hasRoles()) {
			$t->assertText("//p[@class='message']", $lang['strrolecreated']);
			$t->assertText("//tr/td/a[text()='{$user}toalter']", "{$user}toalter");
			$t->assertText("//tr/td/a[text()='{$user}toalter']/../../td[2]", $lang['stryes']);//super user ?
			$t->assertText("//tr/td/a[text()='{$user}toalter']/../../td[3]", $lang['stryes']);//create db ?
			$t->assertText("//tr/td/a[text()='{$user}toalter']/../../td[4]", $lang['stryes']); //create role
			$t->assertText("//tr/td/a[text()='{$user}toalter']/../../td[5]", $lang['stryes']); //inherit
			$t->assertText("//tr/td/a[text()='{$user}toalter']/../../td[6]", $lang['stryes']); //can login
		}
		else {
			$t->assertText("//p[@class='message']", $lang['strusercreated']);
			$t->assertText("//tr/td[text()='{$current_user}']", $current_user);
			$t->assertText("//tr/td[text()='{$current_user}']/../td[2]", $lang['stryes']);//super user ?
			$t->assertText("//tr/td[text()='{$current_user}']/../td[3]", $lang['stryes']);//create db ?
		}

		/* 6 */
		$t->addComment('6. alter user back to the normal value');
		if ($t->data->hasRoles()) {
			$t->clickAndWait("link={$lang['strroles']}");
			$t->clickAndWait("link={$user}toalter");
			$t->clickAndWait("link={$lang['stralter']}");
			$t->type('formNewRoleName', $user);
			$t->uncheck('formCreateRole'); // revert
			$t->uncheck('formInherits'); // revert
		} else {
			$t->clickAndWait("link={$lang['strusers']}");
			if ($t->data->hasUserRename()) {
				$t->clickAndWait("//tr/td[text()='{$user}toalter']/../td/a[text()='{$lang['stralter']}']");
				$t->type('newname', $user);
			}
			else
				$t->clickAndWait("//tr/td[text()='{$user}']/../td/a[text()='{$lang['stralter']}']");
		}
		$t->uncheck('formSuper'); // revert
		$t->uncheck('formCreateDB'); // revert
		$t->type('formPassword', $user_pass);
		$t->type('formConfirm', $user_pass);
		$t->clickAndWait('alter');
		if ($t->data->hasRoles()) {
			$t->assertText("//p[@class='message']", $lang['strrolealtered']);
			$t->assertText("//tr/td/a[text()='{$user}']", $user);
			$t->assertText("//tr/td/a[text()='{$user}']/../../td[2]", $lang['strno']);//super user ?
			$t->assertText("//tr/td/a[text()='{$user}']/../../td[3]", $lang['strno']);//create db ?
			$t->assertText("//tr/td/a[text()='{$user}']/../../td[4]", $lang['strno']); //create role
			$t->assertText("//tr/td/a[text()='{$user}']/../../td[5]", $lang['strno']); //inherit
			$t->assertText("//tr/td/a[text()='{$user}']/../../td[6]", $lang['stryes']); //can login
		}
		else {
			$t->assertText("//p[@class='message']", $lang['struserupdated']);
			$t->assertText("//tr/td[text()='{$user}']/../td[2]", $lang['strno']);//super user ?
			$t->assertText("//tr/td[text()='{$user}']/../td[3]", $lang['strno']);//create db ?
			$t->assertText("//p[@class='message']", $lang['struserupdated']);
		}

		$t->logout();
		unset($t);
	}
?>
