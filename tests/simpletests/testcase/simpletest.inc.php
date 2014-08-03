<?php
/**
 * This file sets global variables for
 * $PHP_SIMPLETEST_HOME and $PHP_SIMPLETEST_REPORT_PATH.
 *
 * Derived from SpikeSource sample code.
 *
 * @author     Augmentum SpikeSource Team
 * @copyright  2005 by Augmentum, Inc.
 */
?>
<?php
    $PHP_SIMPLETEST_REPORT_PATH = false;
    global $PHP_SIMPLETEST_REPORT_PATH;
    for ($ii=1; $ii < $argc; $ii++) {
        if (strpos($argv[$ii], "PHP_SIMPLETEST_HOME=") !== false) {
            parse_str($argv[$ii]);
        } elseif (strpos($argv[$ii], "PHP_SIMPLETEST_REPORT_PATH=") !== false) {
            parse_str($argv[$ii]);
        }
    }

    if (empty($PHP_SIMPLETEST_HOME) || !is_dir($PHP_SIMPLETEST_HOME)) {
        $msg = "ERROR: Could not locate PHP_SIMPLETEST_HOME [$PHP_SIMPLETEST_HOME]. ";
        $msg .= "Use 'php <filename> PHP_SIMPLETEST_HOME=/path/to/simpletest/home'\n";
        die($msg);
    }

    error_log("PHP_SIMPLETEST_HOME=" . $PHP_SIMPLETEST_HOME);
    $include_path = get_include_path();
    set_include_path($PHP_SIMPLETEST_HOME. ":" . $include_path);
    define('__PHP_SIMPLETEST_HOME', $PHP_SIMPLETEST_HOME);
    echo $PHP_SIMPLETEST_REPORT_PATH;
    echo $PHP_SIMPLETEST_HOME;

?>
