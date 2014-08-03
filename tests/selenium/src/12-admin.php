<?php
    $test_title = 'Admin tests';

    if (isset($_GET['run'])) {
    global $lang;
    require '../config.test.php';
    require '../testBuilder.class.php';
    /*
	* 1/ swing through the admin panel
	*/
    $t = new TestBuilder($test_title,
        'Test the admin interfaces.'
    );

    $t->login($admin_user, $admin_user_pass);

    /** 1 **/
    $t->addComment('1. Check Variables');
    $t->clickAndWait("link={$lang['strdatabases']}");
    $t->clickAndWait("link={$testdb}");
    $t->clickAndWait("link={$lang['strvariables']}");
    $t->assertText("//tr/th[text()='{$lang['strname']}' and @class='data']/../th[1]", $lang['strname']);
    $t->assertText("//tr/th[text()='{$lang['strname']}' and @class='data']/../th[2]", $lang['strsetting']);
    $t->assertText("//tr[contains(@class,'data')]/td[text()='client_encoding']/../td[1]", 'client_encoding');
    $t->assertText("//tr[contains(@class,'data')]/td[text()='client_encoding']/../td[2]", 'UTF*8');

    /** 2 **/
    $t->addComment('2. Check Processes');
    $t->clickAndWait("link={$lang['strprocesses']}");
    $t->click("//a[@id='control']");
    $t->assertText("//a[@id='control']", $lang['strstart']);
    if ($t->data->hasPreparedXacts()) {
        $t->assertText('//h3[1]', $lang['strpreparedxacts']);
        $t->assertText('//h3[2]', $lang['strprocesses']);
    } else
        $t->assertText('//h3[1]', $lang['strprocesses']);

    if ($t->data->major_version > 8.1) {
        $t->assertText("//tr/th[text()='{$lang['strusername']}' and @class='data']/../th[1]", $lang['strusername']);
        $t->assertText("//tr/th[text()='{$lang['strusername']}' and @class='data']/../th[2]", $lang['strprocess']);
        $t->assertText("//tr/th[text()='{$lang['strusername']}' and @class='data']/../th[3]", $lang['strsql']);
        $t->assertText("//tr/th[text()='{$lang['strusername']}' and @class='data']/../th[4]", $lang['strstarttime']);
        $t->assertText("//tr/th[text()='{$lang['strusername']}' and @class='data']/../th[5]", $lang['stractions']);
        $t->assertText("//tr[contains(@class,'data')]/td[text()='{$admin_user}']/../td[1]", $admin_user);
        /* this check is a bit fragile, since it relies on new line wrapping */
        $t->assertText('//tr[contains(@class,\'data\')]/td[3]/pre[@class=\'data\']', 'SELECT datname, usename, *FROM pg_catalog.pg_stat_activity*WHERE datname=\'ppatests_db\'*ORDER BY usename,*pid');
        $t->assertText("//tr[contains(@class,'data')]/td[text()='{$admin_user}']/../td[5]", $lang['strcancel']);
        if ($t->data->hasQueryKill())
            $t->assertText("//tr[contains(@class,'data')]/td[text()='{$admin_user}']/../td[6]", $lang['strkill']);
    }

    /** 3 **/
    $t->addComment('3. Check Locks');
    $t->clickAndWait("link={$lang['strlocks']}");
    $t->click("//a[@id='control']");
    $t->assertText("//a[@id='control']", $lang['strstart']);
    $i = 1;
    $t->assertText("//tr/th[text()='{$lang['strschema']}' and @class='data']/../th[{$i}]", $lang['strschema']); $i++;
    $t->assertText("//tr/th[text()='{$lang['strschema']}' and @class='data']/../th[{$i}]", $lang['strtablename']); $i++;
    if ($t->data->hasVirtualTransactionId()) {
        $t->assertText("//tr/th[text()='{$lang['strschema']}' and @class='data']/../th[{$i}]", $lang['strvirtualtransaction']); $i++;
    }
    $t->assertText("//tr/th[text()='{$lang['strschema']}' and @class='data']/../th[{$i}]", $lang['strtransaction']); $i++;
    $t->assertText("//tr/th[text()='{$lang['strschema']}' and @class='data']/../th[{$i}]", $lang['strprocessid']); $i++;
    $t->assertText("//tr/th[text()='{$lang['strschema']}' and @class='data']/../th[{$i}]", $lang['strmode']); $i++;
    $t->assertText("//tr/th[text()='{$lang['strschema']}' and @class='data']/../th[{$i}]", $lang['strislockheld']);
    $t->assertText("//tr/td[1 and text()='pg_catalog']/../td[2 and text()='pg_class']/../td[1]", 'pg_catalog');
    $t->assertText("//tr/td[1 and text()='pg_catalog']/../td[2 and text()='pg_class']/../td[2]", 'pg_class');
    if ($t->data->hasVirtualTransactionId()) {
        $t->assertText("//tr/td[1 and text()='pg_catalog']/../td[2 and text()='pg_class']/../td[6]", 'AccessShareLock');
        $t->assertText("//tr/td[1 and text()='pg_catalog']/../td[2 and text()='pg_class']/../td[7]", $lang['stryes']);
    } else {
        $t->assertText("//tr/td[1 and text()='pg_catalog']/../td[2 and text()='pg_class']/../td[5]", 'AccessShareLock');
        $t->assertText("//tr/td[1 and text()='pg_catalog']/../td[2 and text()='pg_class']/../td[6]", $lang['stryes']);
    }

    /** 4 **/
    $t->addComment('4. Database Admin Options');
    $t->clickAndWait("link={$lang['stradmin']}");

    $t->assertText("//tr/th[text()='{$lang['strvacuum']}' and @class='data']/../th[1]", "{$lang['strvacuum']}*");
    $t->assertText("//tr/th[text()='{$lang['strvacuum']}' and @class='data']/../th[2]", "{$lang['stranalyze']}*");
    $t->assertText("//tr/th[text()='{$lang['strvacuum']}' and @class='data']/../th[3]", "{$lang['strcluster']}*");
    $t->assertText("//tr/th[text()='{$lang['strvacuum']}' and @class='data']/../th[4]", "{$lang['strreindex']}*");

    if ($t->data->hasAutovacuum())
        $t->assertText("//h2", $lang['strvacuumpertable']);

    /* vacuum full freeze analyze */
    $t->clickAndWait("//input[@value='{$lang['strvacuum']}']");
    $t->assertText("//h2", "{$lang['strvacuum']}*");
    $t->assertText("//p", sprintf($lang['strconfvacuumdatabase'], $testdb));
    $t->check("//input[@name='vacuum_full']");
    $t->check("//input[@name='vacuum_analyze']");
    /* PostgreSQL > 7.4 and < 8.2 doesn't support "vacuum full freeze"
	 * Split the test for these releases */
    if ($t->data->major_version < 8.2) {
        $t->clickAndWait("//input[@value='{$lang['strvacuum']}']");
        $t->assertText('//p[@class=\'message\']', $lang['strvacuumgood']);
        $t->clickAndWait("//input[@value='{$lang['strvacuum']}']");
    }
    $t->check("//input[@name='vacuum_freeze']");
    $t->clickAndWait("//input[@value='{$lang['strvacuum']}']");
    $t->assertText('//p[@class=\'message\']', $lang['strvacuumgood']);

    /* analyze */
    $t->clickAndWait("//input[@value='{$lang['stranalyze']}']");
    $t->assertText("//h2", "{$lang['stranalyze']}*");
    $t->assertText("//p", sprintf($lang['strconfanalyzedatabase'], $testdb));
    $t->clickAndWait("//input[@value='{$lang['stranalyze']}']");
    $t->assertText('//p[@class=\'message\']', $lang['stranalyzegood']);

    /* cluster */
    $t->clickAndWait("//input[@value='{$lang['strcluster']}']");
    $t->assertText("//h2", "{$lang['strcluster']}*");
    $t->assertText("//p", sprintf($lang['strconfclusterdatabase'], $testdb));
    $t->clickAndWait("//input[@value='{$lang['strcluster']}']");
    $t->assertText('//p[@class=\'message\']', $lang['strclusteredgood']);

    /* reindex */
    $t->clickAndWait("//input[@value='{$lang['strreindex']}']");
    $t->assertText("//h2", "{$lang['strreindex']}*");
    $t->assertText("//p", sprintf($lang['strconfreindexdatabase'], $testdb));
    $t->clickAndWait("//input[@value='{$lang['strreindex']}']");
    $t->assertText('//p[@class=\'message\']', $lang['strreindexgood']);

    /** 5 **/
    $t->addComment('5. Table Admin Options');
    $t->clickAndWait("link={$lang['strschemas']}");
    $t->clickAndWait("link=public");
    $t->clickAndWait("link={$lang['strtables']}");
    $t->clickAndWait("link=student");
    $t->clickAndWait("link={$lang['stradmin']}");

    $t->assertText("//tr/th[text()='{$lang['strvacuum']}' and @class='data']/../th[1]", "{$lang['strvacuum']}*");
    $t->assertText("//tr/th[text()='{$lang['strvacuum']}' and @class='data']/../th[2]", "{$lang['stranalyze']}*");
    $t->assertText("//tr/th[text()='{$lang['strvacuum']}' and @class='data']/../th[3]", "{$lang['strcluster']}*");
    $t->assertText("//tr/th[text()='{$lang['strvacuum']}' and @class='data']/../th[4]", "{$lang['strreindex']}*");

    /* vacuum freeze analyze full */
    $t->clickAndWait("//input[@value='{$lang['strvacuum']}']");
    $t->assertText("//h2", "{$lang['strvacuum']}*");
    $t->assertText("//p", sprintf($lang['strconfvacuumtable'], 'student'));
    $t->check("//input[@name='vacuum_full']");
    $t->check("//input[@name='vacuum_analyze']");
    /* PostgreSQL > 7.4 and < 8.2 doesn't support "vacuum full freeze"
	 * Split the test for these releases */
    if ($t->data->major_version < 8.2) {
        $t->clickAndWait("//input[@value='{$lang['strvacuum']}']");
        $t->assertText('//p[@class=\'message\']', $lang['strvacuumgood']);
        $t->clickAndWait("//input[@value='{$lang['strvacuum']}']");
    }
    $t->check("//input[@name='vacuum_freeze']");
    $t->clickAndWait("//input[@value='{$lang['strvacuum']}']");
    $t->assertText('//p[@class=\'message\']', $lang['strvacuumgood']);

    /* analyze */
    $t->clickAndWait("//input[@value='{$lang['stranalyze']}']");
    $t->assertText("//h2", "{$lang['stranalyze']}*");
    $t->assertText("//p", sprintf($lang['strconfanalyzetable'], 'student'));
    $t->clickAndWait("//input[@value='{$lang['stranalyze']}']");
    $t->assertText('//p[@class=\'message\']', $lang['stranalyzegood']);

    /* reindex */
    $t->clickAndWait("//input[@value='{$lang['strreindex']}']");
    $t->assertText("//h2", "{$lang['strreindex']}*");
    $t->assertText("//p", sprintf($lang['strconfreindextable'], 'student'));
    $t->clickAndWait("//input[@value='{$lang['strreindex']}']");
    $t->assertText('//p[@class=\'message\']', $lang['strreindexgood']);

    /* TODO cluster */

    /** 6 **/
    if ($t->data->hasAutovacuum()) {
        $t->assertText("//h2", $lang['strvacuumpertable']);
        $t->addComment('6. Autovacuum setup');
        $t->clickAndWait("link={$lang['straddvacuumtable']}");
        $t->assertText("//h2", sprintf($lang['streditvacuumtable'], 'student'));
        $t->check("//tr/td/input[@name='autovacuum_enabled' and @value='off']");
        $t->type("//tr/td/input[@name='autovacuum_vacuum_threshold']", 55);
        $t->type("//tr/td/input[@name='autovacuum_vacuum_scale_factor']", 0.25);
        $t->type("//tr/td/input[@name='autovacuum_analyze_threshold']", 55);
        $t->type("//tr/td/input[@name='autovacuum_analyze_scale_factor']", 0.15);
        $t->type("//tr/td/input[@name='autovacuum_vacuum_cost_delay']", 10);
        $t->type("//tr/td/input[@name='autovacuum_vacuum_cost_limit']", 200);
        $t->clickAndWait("//input[@value='{$lang['strsave']}']");
        $t->assertText("//tr/th[1 and text()='{$lang['strenabled']}' and @class='data']/../th[1]", $lang['strenabled']);
        $t->assertText("//tr/th[1 and text()='{$lang['strenabled']}' and @class='data']/../th[2]", $lang['strvacuumbasethreshold']);
        $t->assertText("//tr/th[1 and text()='{$lang['strenabled']}' and @class='data']/../th[3]", $lang['strvacuumscalefactor']);
        $t->assertText("//tr/th[1 and text()='{$lang['strenabled']}' and @class='data']/../th[4]", $lang['stranalybasethreshold']);
        $t->assertText("//tr/th[1 and text()='{$lang['strenabled']}' and @class='data']/../th[5]", $lang['stranalyzescalefactor']);
        $t->assertText("//tr/th[1 and text()='{$lang['strenabled']}' and @class='data']/../th[6]", $lang['strvacuumcostdelay']);
        $t->assertText("//tr/th[1 and text()='{$lang['strenabled']}' and @class='data']/../th[7]", $lang['strvacuumcostlimit']);
        $t->assertText("//tr/th[1 and text()='{$lang['strenabled']}' and @class='data']/../th[8]", $lang['stractions']);
        $t->assertText("//tr/th[1 and text()='{$lang['strenabled']}' and @class='data']/../../tr[2]/td[1]", 'off');
        $t->assertText("//tr/th[1 and text()='{$lang['strenabled']}' and @class='data']/../../tr[2]/td[2]", 55);
        $t->assertText("//tr/th[1 and text()='{$lang['strenabled']}' and @class='data']/../../tr[2]/td[3]", 0.25);
        $t->assertText("//tr/th[1 and text()='{$lang['strenabled']}' and @class='data']/../../tr[2]/td[4]", 55);
        $t->assertText("//tr/th[1 and text()='{$lang['strenabled']}' and @class='data']/../../tr[2]/td[5]", 0.15);
        $t->assertText("//tr/th[1 and text()='{$lang['strenabled']}' and @class='data']/../../tr[2]/td[6]", '10ms');
        $t->assertText("//tr/th[1 and text()='{$lang['strenabled']}' and @class='data']/../../tr[2]/td[7]", 200);
        $t->assertText("//tr/th[1 and text()='{$lang['strenabled']}' and @class='data']/../../tr[2]/td[8]", $lang['stredit']);
        $t->assertText("//tr/th[1 and text()='{$lang['strenabled']}' and @class='data']/../../tr[2]/td[9]", $lang['strdelete']);

        $t->clickAndWait("//tr/td/a[text()='{$lang['stredit']}']");
        $t->check("//tr/td/input[@name='autovacuum_enabled' and @value='on']");
        $t->clickAndWait("//input[@value='{$lang['strsave']}']");
        $t->assertText("//tr/th[1 and text()='{$lang['strenabled']}' and @class='data']/../../tr[2]/td[1]", 'on');

        $t->clickAndWait("//tr/td[@class='crumb']/a[@title='{$lang['strdatabase']}']");
        $t->clickAndWait("link={$lang['stradmin']}");
        $t->assertText("//h2", $lang['strvacuumpertable']);
        $t->assertText("//tr/th[3 and text()='{$lang['strenabled']}' and @class='data']/../th[1]", $lang['strschema']);
        $t->assertText("//tr/th[3 and text()='{$lang['strenabled']}' and @class='data']/../th[2]", $lang['strtable']);
        $t->assertText("//tr/th[3 and text()='{$lang['strenabled']}' and @class='data']/../th[3]", $lang['strenabled']);
        $t->assertText("//tr/th[3 and text()='{$lang['strenabled']}' and @class='data']/../th[4]", $lang['strvacuumbasethreshold']);
        $t->assertText("//tr/th[3 and text()='{$lang['strenabled']}' and @class='data']/../th[5]", $lang['strvacuumscalefactor']);
        $t->assertText("//tr/th[3 and text()='{$lang['strenabled']}' and @class='data']/../th[6]", $lang['stranalybasethreshold']);
        $t->assertText("//tr/th[3 and text()='{$lang['strenabled']}' and @class='data']/../th[7]", $lang['stranalyzescalefactor']);
        $t->assertText("//tr/th[3 and text()='{$lang['strenabled']}' and @class='data']/../th[8]", $lang['strvacuumcostdelay']);
        $t->assertText("//tr/th[3 and text()='{$lang['strenabled']}' and @class='data']/../th[9]", $lang['strvacuumcostlimit']);
        $t->assertText("//tr/th[3 and text()='{$lang['strenabled']}' and @class='data']/../th[10]", $lang['stractions']);
        $t->assertText("//tr/th[1 and text()='{$lang['strenabled']}' and @class='data']/../../tr[2]/td[1]", 'public');
        $t->assertText("//tr/th[1 and text()='{$lang['strenabled']}' and @class='data']/../../tr[2]/td[2]", 'student');
        $t->assertText("//tr/th[1 and text()='{$lang['strenabled']}' and @class='data']/../../tr[2]/td[3]", 'on');
        $t->assertText("//tr/th[1 and text()='{$lang['strenabled']}' and @class='data']/../../tr[2]/td[4]", 55);
        $t->assertText("//tr/th[1 and text()='{$lang['strenabled']}' and @class='data']/../../tr[2]/td[5]", 0.25);
        $t->assertText("//tr/th[1 and text()='{$lang['strenabled']}' and @class='data']/../../tr[2]/td[6]", 55);
        $t->assertText("//tr/th[1 and text()='{$lang['strenabled']}' and @class='data']/../../tr[2]/td[7]", 0.15);
        $t->assertText("//tr/th[1 and text()='{$lang['strenabled']}' and @class='data']/../../tr[2]/td[8]", '10ms');
        $t->assertText("//tr/th[1 and text()='{$lang['strenabled']}' and @class='data']/../../tr[2]/td[9]", 200);
        $t->assertText("//tr/th[1 and text()='{$lang['strenabled']}' and @class='data']/../../tr[2]/td[10]", $lang['stredit']);
        $t->assertText("//tr/th[1 and text()='{$lang['strenabled']}' and @class='data']/../../tr[2]/td[11]", $lang['strdelete']);

        $t->clickAndWait("//tr/th[1 and text()='{$lang['strenabled']}' and @class='data']/../../tr[2]/td[11]/a");
        $t->assertText("//p", sprintf($lang['strdelvacuumtable'], '*student*'));
        $t->clickAndWait("//input[@value='{$lang['stryes']}']");
        $t->assertText("//p[text()='{$lang['strnovacuumconf']}']", $lang['strnovacuumconf']);
    }

    /** 7 **/
    $t->logout();
    unset($t);
    }
