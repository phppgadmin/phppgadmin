<?php
    function connection_failed($dbms, $fn, $errno, $errmsg, $p1=false, $p2=false)
    {
        echo "<div>{$errmsg}</div>\n";
        exit;
    }

    $_no_db_connection = true; /* load lib.inc.php without trying to connect */
    $_REQUEST['language'] = 'english';
    define('ADODB_ERROR_HANDLER','connection_failed');
    chdir(dirname(__FILE__). '/../..');
    require_once './libraries/lib.inc.php';

    /*
	 * This class help building a selenium HTML test file for PPA.
	**/
    class TestBuilder
    {
        /**
		 * Constructor
		 * @param $serverDesc The server['desc'] conf param to which we are writing this test file for.
		 * @param $title The title of the HTML test page
		 * @param $desc The top description on the HTML test page
		 */
        public function __construct($title, $desc)
        {
            global $misc;
            $this->title = $title;

            $serverid = $_GET['server'];

            require './tests/selenium/config.test.php';
            require_once './classes/database/Connection.php';

            $this->webUrl = $webUrl;
            $_REQUEST['server'] = $serverid;

            $server = $misc->getServerInfo($serverid);
            $server['username'] = $super_user[$server['desc']];
            $server['password'] = $super_pass[$server['desc']];

            $misc->setServerInfo(null, $server, $serverid);

            $this->server = $server;

            $this->data = $misc->getDatabaseAccessor($server['defaultdb']);

            echo "<html>\n<head>
				<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">
				<title>$title</title>
				</head>
				<body>
				<table cellpadding=\"1\" cellspacing=\"1\" border=\"1\">
				<thead>
				<tr><td rowspan=\"1\" colspan=\"3\">$desc</td></tr>
				</thead><tbody>";
        }

        /**
		 * Write the test file, close it,
		 * append the test file to the TestSuite.html file and destroy itself
		 * @param $testfile The path to the test file to create
		 * @param $testsuite_file The path to the TestSuite.html file
		 */
        public function writeTests() { //$testfile, $testsuite_file)
        {
            //echo $this->code;
            return;
        }

        /**
		 * Add a selenium test to the file
		 * @param $action the selenium action (first column)
		 * @param $selector the selector to select the object to work on (second column)
		 * @param $value (optional) the expected (or not) value (third column)
		 */
        public function test($action, $selector, $value='')
        {
            echo "<tr>\n<td>$action</td>\n<td>$selector</td>\n<td>$value</td>\n</tr>\n";
        }

        public function addComment($c)
        {
            echo "<tr>\n<th colspan=\"3\">{$c}</th>\n</tr>\n";
        }

        /**
		 * Add steps to login on PPA using the given credentials
		 * @param $u The username to use
		 * @param $p The password to use
		 */
        public function login($u, $p)
        {
            $this->addComment("Login as {$this->server['username']}");
            $this->test('open', "{$this->webUrl}/intro.php");
            $this->select('language', 'English');
            $this->test('open', "{$this->webUrl}/login.php?server={$this->data->conn->host}&subject=server");
            $this->test('type', "//input[@name='loginUsername']", $u);
            $this->test('type', "//input[@id='loginPassword']", $p);
            $this->test('clickAndWait', 'loginSubmit');
            $this->test('assertText', "//div[@class='topbar']/descendant::span[@class='username']", $u);
        }

        /**
		 * Add steps to logout from the server
		 */
        public function logout()
        {
            global $lang;

            $this->addComment("Logout");
            $this->test('clickAndWait', "//div[@class='trail']/descendant::tr/td[1]/a/span[@class='label' and text()='phpPgAdmin']");
            $this->test('clickAndWait', "link={$lang['strservers']}");
            $this->test('clickAndWait', "//tr/td/a[text()='{$this->server['desc']}']/../../td/a[text()='{$lang['strlogout']}']");

            $this->test('assertText', "//p[@class='message']",
                sprintf($lang['strlogoutmsg'], $this->server['desc'])
            );
        }

        /**
		 * Add a selenium type test to the file
		 * @param $selector the selector to select the object to work on (second column)
		 * @param $value (optional) the expected (or not) value (third column)
		 */
        public function type($selector, $value)
        {
            $this->test('type', $selector, $value);
        }

        /**
		 * Add a selenium select test to the file
		 * @param $selector the selector to select the object to work on (second column)
		 * @param $value (optional) the expected (or not) value (third column)
		 */
        public function select($selector, $value)
        {
            $this->test('select', $selector, $value);
        }

        /**
		 * Add a selenium addSelection test to the file
		 * @param $selector the selector to select the object to work on (second column)
		 * @param $value (optional) the expected (or not) value (third column)
		 */
        public function addSelection($selector, $value)
        {
            $this->test('addSelection', $selector, $value);
        }

        /**
		 * Add a selenium click test to the file
		 * @param $selector the selector to select the object to work on (second column)
		 */
        public function click($selector)
        {
            $this->test('click', $selector);
        }

        /**
		 * Add a selenium check test to the file
		 * @param $selector the selector to select the object to work on (second column)
		 */
        public function check($selector)
        {
            $this->test('check', $selector);
        }

        /**
		 * Add a selenium uncheck test to the file
		 * @param $selector the selector to select the object to work on (second column)
		 */
        public function uncheck($selector)
        {
            $this->test('uncheck', $selector);
        }

        /**
		 * Add a selenium clickAndWait est to the file
		 * @param $selector the selector to select the object to work on (second column)
		 */
        public function clickAndWait($selector)
        {
            $this->test('clickAndWait', $selector);
        }

        /**
		 * Add a selenium assertText test to the file
		 * @param $selector the selector to select the object to work on (second column)
		 * @param $value (optional) the expected (or not) value (third column)
		 */
        public function assertText($selector, $value)
        {
            $this->test('assertText', $selector, $value);
        }

        /**
		 * Add a selenium assertErrorOnNext test to the file
		 * @param $msg the selenium error message expected
		 */
        public function assertErrorOnNext($msg)
        {
            $this->test('assertErrorOnNext', $msg);
        }

    }
