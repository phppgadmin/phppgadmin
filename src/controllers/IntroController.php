<?php

namespace PHPPgAdmin\Controller;

/**
 * Base controller class
 */
class IntroController extends BaseController {
	public $_name = 'IntroController';

	/**
	 * Intro screen
	 *
	 * $Id: intro.php,v 1.19 2007/07/12 19:26:22 xzilla Exp $
	 */
	public function doDefault() {

		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;

		$appLangFiles = $this->appLangFiles;
		$misc         = $this->misc;
		$appThemes    = $this->appThemes;
		$appName      = $this->appName;
		$appVersion   = $this->appVersion;

		$misc->setNoDBConnection(true);

		$intro_html = $this->printTrail('root', false);

		$intro_html .= $this->printTabs('root', 'intro', false);

		$intro_html .= "<h1> $appName $appVersion (PHP " . phpversion() . ')</h1>';

		$intro_html .= '<form method="get" action="intro.php">';
		$intro_html .= '<table>';
		$intro_html .= '<tr class="data1">';
		$intro_html .= '<th class="data">' . $lang['strlanguage'] . '</th>';
		$intro_html .= '<td>';
		$intro_html .= '<select name="language" onchange="this.form.submit()">';

		$language = isset($_SESSION['webdbLanguage']) ? $_SESSION['webdbLanguage'] : 'english';
		foreach ($appLangFiles as $k => $v) {
			$selected = ($k == $language) ? ' selected="selected"' : '';
			$intro_html .= "\t<option value=\"{$k}\"" . $selected . ">{$v}</option>\n";
		}

		$intro_html .= '</select>';
		$intro_html .= '</td>';
		$intro_html .= '</tr>';
		$intro_html .= '<tr class="data2">';
		$intro_html .= '<th class="data">' . $lang['strtheme'] . '</th>';
		$intro_html .= '<td>';
		$intro_html .= '<select name="theme" onchange="this.form.submit()">';

		foreach ($appThemes as $k => $v) {
			$selected = ($k == $conf['theme']) ? ' selected="selected"' : '';
			$intro_html .= "\t<option value=\"{$k}\"" . $selected . ">{$v}</option>\n";
		}

		$intro_html .= '</select>';
		$intro_html .= '</td>';
		$intro_html .= '</tr>';
		$intro_html .= '</table>';
		$intro_html .= '<noscript><p><input type="submit" value="' . $lang['stralter'] . '" /></p></noscript>';
		$intro_html .= '</form>';

		$intro_html .= '<p>' . $lang['strintro'] . '</p>';

		$intro_html .= '<ul class="intro">';
		$intro_html .= '	<li><a href="http://phppgadmin.sourceforge.net/">' . $lang['strppahome'] . '</a></li>';
		$intro_html .= '<li><a href="' . $lang['strpgsqlhome_url'] . '">' . $lang['strpgsqlhome'] . '</a></li>';
		$intro_html .= '<li><a href="http://sourceforge.net/tracker/?group_id=37132&amp;atid=418980">' . $lang['strreportbug'] . '</a></li>';
		$intro_html .= '<li><a href="' . $lang['strviewfaq_url'] . '">' . $lang['strviewfaq'] . '</a></li>';
		$intro_html .= '<li><a target="_top" href="tests/selenium/selenium-lib/core/TestRunner.html?test=..%2F..%2FTestSuite.php&resultsUrl=..%2FpostResults">Selenium tests</a></li>';
		$intro_html .= '</ul>';

		if (isset($_GET['language'])) {
			$misc->setReloadBrowser(true);
		}

		echo $intro_html;

	}

	public function render() {

		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;

		$misc->printHeader($lang['strintro']);
		$misc->printBody();

		switch ($action) {

			default:
				$this->doDefault();
				break;
		}

		$misc->printFooter();

	}
}