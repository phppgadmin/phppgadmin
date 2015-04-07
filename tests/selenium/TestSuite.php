<?php
	/**
	 * Build the test files for each servers in config.test.php
	 */

	chdir('../..'); /* go back to root so we can include scripts */

	function oops($dbms, $fn, $errno, $errmsg, $p1=false, $p2=false) {
		echo "</table><div>{$errmsg}</div>\n";
		exit;
	}
	
	$_no_db_connection = true; /* load lib.inc.php without trying to connect */

	require_once('./libraries/lib.inc.php');
	require('./tests/selenium/config.test.php');
	require_once('./classes/database/Connection.php');

	$test_dir = './tests/selenium';
	$test_src_dir = "{$test_dir}/src";

	echo "<table border=\"1\">
		<tr>
			<th>Test suite for PPA</th>
		</tr>\n";

	$servers = $misc->getServers();

	/* Loop on the servers given in the conf/config.inc.conf file */
	foreach ($servers as $server) {
		// Is this server in our list of configured servers?
		if (!in_array($server['desc'], $test_servers))
			continue;

		$fd = opendir($test_src_dir);
		$files = array();
		while ($file = readdir($fd))
			if (preg_match('@[0-9]+.*\.php$@', $file))
				$files[] = $file;
		closedir($fd);
		sort($files);
		/* include the tests creator scripts here
		 * in the order you want them executed.
		 * Each script append itself to the TestSuite.html file.
		 **/
		foreach ($files as $testgroupfile) {
			require("{$test_src_dir}/{$testgroupfile}");
			echo "<tr><td><a href=\"{$webUrl}/{$test_src_dir}/{$testgroupfile}?server={$server['id']}&amp;run=1\">[{$server['desc']}] {$test_title}</a></td></tr>\n";
		}
	}
	echo "</table>";
?>
