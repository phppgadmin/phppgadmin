<?php
	$test_title = 'Domain tests';

	if (isset($_GET['run'])) {
		global $lang;
		require('../config.test.php');
		require('../testBuilder.class.php');
		/*
		 * 1/ create domain
		 * 2/ add unwanted domain constraint
		 * 3/ drop unwanted check constraint on domain
		 * 4/ alter domain giving owner to super_user
		 * 5/ alter back the owner to admin_user
		 * NB: dropping domain is in the cleantests.php tests
		 */
		$t = new TestBuilder($test_title,
			'Create and Alter a domain.'
		);

		$t->login($admin_user, $admin_user_pass);

	/** 1 **/
		$t->addComment('1. create domain');
		$t->clickAndWait("link={$lang['strdatabases']}");
		$t->clickAndWait("link={$testdb}");
		$t->clickAndWait("link={$lang['strschemas']}");
		$t->clickAndWait("link=test_schema");
		$t->clickAndWait("link={$lang['strdomains']}");
		$t->clickAndWait("link={$lang['strcreatedomain']}");
		$t->type('domname', 'year');
		$t->select('domtype', 'label=integer');
		if ($t->data->hasAlterDomains()) {
			$t->type('domdefault', '1900');
			$t->click('domnotnull');
		}
		else
			$t->type('domdefault', 'extract(year from current_date)');

		if ($t->data->hasDomainConstraints())
			$t->type('domcheck', 'VALUE &gt;= 1901 AND VALUE &lt;= 2155');
		$t->clickAndWait("//input[@value='{$lang['strcreate']}']");
		$t->assertText("//p[@class='message']", $lang['strdomaincreated']);
		$t->assertText("//tr/td/a[text()='year']", 'year');
		$t->assertText("//tr/td/a[text()='year']/../../td[2]", 'integer');
		if ($t->data->hasAlterDomains()) {
			$t->assertText("//tr/td/a[text()='year']/../../td[3]/div", 'NOT NULL');
			$t->assertText("//tr/td/a[text()='year']/../../td[4]", '1900');
		}
		/* we doesn't test default as Postgres change it with internal methods */
		//else
		//	$t->assertText("//tr/td/a[text()='year']/../../td[4]", 'extract(year from current_date)');
		$t->assertText("//tr/td/a[text()='year']/../../td[5]", $admin_user);
		if ($t->data->hasDomainConstraints()) {
			$t->clickAndWait("link=year");
			if ($t->data->major_version == '7.4')
				$t->assertText("//tr/td[text()='$1']/../td[2]", 'CHECK (VALUE >= 1901 AND VALUE <= 2155)');
			else
				$t->assertText("//tr/td[text()='year_check']/../td[2]", 'CHECK (VALUE >= 1901 AND VALUE <= 2155)');
		}

	/** 2 **/
		$t->addComment('2. add unwanted domain constraint');
		if ($t->data->hasDomainConstraints()) {
			$t->clickAndWait("//div[@class='trail']/descendant::a[@title='{$lang['strschema']}']/span[@class='label' and text()='test_schema']");
			$t->clickAndWait("link={$lang['strdomains']}");
			$t->clickAndWait("link=year");
			$t->clickAndWait("//a[text()='{$lang['straddcheck']}']");
			$t->type('name', 'test_to_drop');
			$t->type('definition', 'VALUE > 1900');
			$t->clickAndWait('add');
			$t->assertText("//p[@class='message']", $lang['strcheckadded']);
			$t->assertText("//tr/td[text()='test_to_drop']/../td[2]", 'CHECK (VALUE > 1900)');
		}

	/** 3 **/
		$t->addComment('3. drop unwanted check constraint on domain');
		if ($t->data->hasDomainConstraints()) {
			$t->clickAndWait("//div[@class='trail']/descendant::a[@title='{$lang['strschema']}']/span[@class='label' and text()='test_schema']");
			$t->clickAndWait("link={$lang['strdomains']}");
			$t->clickAndWait("link=year");
			$t->clickAndWait("//tr/td[text()='test_to_drop']/../td/a[text()='{$lang['strdrop']}']");
			$str = sprintf($lang['strconfdropconstraint'], 'test_to_drop', 'year');
			$t->assertText("//p[text()='$str']", sprintf($lang['strconfdropconstraint'], 'test_to_drop', 'year'));
			$t->clickAndWait('drop');
			$t->assertText("//p[@class='message']", $lang['strconstraintdropped']);
		}

	/** 4 **/
		$t->addComment('4. alter domain giving owner to super_user');
		if ($t->data->hasAlterDomains()) {
			$t->clickAndWait("//div[@class='trail']/descendant::a[@title='{$lang['strschema']}']/span[@class='label' and text()='test_schema']");
			$t->clickAndWait("link={$lang['strdomains']}");
			$t->clickAndWait("//tr/td/a[text()='year']/../../td/a[text()='{$lang['stralter']}']");
			$t->click('domnotnull');
			$t->type('domdefault', 'extract(year from current_date)');
			$t->select('domowner', $super_user[$t->server['desc']]);
			$t->clickAndWait('alter');
			$t->assertText("//p[@class='message']", $lang['strdomainaltered']);
			$t->assertText("//tr/th[text()='{$lang['strnotnull']}']/../td", '');
			/* we doesn't test default as Postgres change it with internal methods */
			//$t->assertText("//tr/th[text()='{$lang['strdefault']}']/../td", 'extract(year from current_date)');
			$t->assertText("//tr/th[text()='{$lang['strowner']}']/../td", $super_user[$t->server['desc']]);
		}

	/** 5 **/
		$t->addComment('5. alter back the owner to admin_user');
		if ($t->data->hasAlterDomains()) {
			$t->clickAndWait("//div[@class='trail']/descendant::a[@title='{$lang['strschema']}']/span[@class='label' and text()='test_schema']");
			$t->clickAndWait("link={$lang['strdomains']}");
			$t->clickAndWait("//tr/td/a[text()='year']/../../td/a[text()='{$lang['stralter']}']");
			$t->select('domowner', $admin_user);
			$t->clickAndWait('alter');
			$t->assertText("//p[@class='message']", $lang['strdomainaltered']);
			$t->assertText("//tr/th[text()='{$lang['strowner']}']/../td", $admin_user);
		}

		$t->logout();
		unset($t);
	}
?>
