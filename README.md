# phppgadmin
the premier web-based administration tool for postgresql

- [INSTALL](#install)
- [CONTRIBUTE](#contribute)
- [TRANSLATORS](#translators)
- [CREDIT](#credit)

# INSTALL

* [1. Unpack your download](#1-unpack-your-download)
* [2. Configure phpPgAdmin](#2-configure-phppgadmin)
* [3. Ensure the statistics collector is enabled in PostgreSQL.](#3-ensure-the-statistics-collector-is-enabled-in-postgresql)
* [4. Browse to the phpPgAdmin installation using a web browser.](#4-browse-to-the-phppgadmin-installation-using-a-web-browser)
* [5. Impontart - Security](#5-important---security)

## 1. Unpack your download

* If you've downloaded a tar.gz package, execute from a terminal:

      gunzip phpPgAdmin-*.tar.gz
      tar -xvf phpPgAdmin-*.tar

* Else, if you've downloaded a tar.bz2 package, execute from a terminal: 

      bunzip2 phpPgAdmin-*.tar.bz2
      tar -xvf phpPgAdmin-*.tar

* Else, if you've downloaded a zip package, execute from a terminal:

      unzip phpPgAdmin-*.zip

## 2. Configure phpPgAdmin

* edit phpPgAdmin/conf/config.inc.php
* If you mess up the configuration file, you can recover it from the
   `config.inc.php-dist` file.

## 3. Ensure the statistics collector is enabled in PostgreSQL.
phpPgAdmin will display table, index performance, and usage statistics if you have enabled the PostgreSQL statistics collector. While this is normally enabled by default, to ensure it is running, make sure the following lines in your `postgresql.conf` are uncommented: 

    track_activities
    track_counts 

## 4. Browse to the phpPgAdmin installation using a web browser.  
You might need cookies enabled for phpPgAdmin to work.

## 5. Important - Security

PostgreSQL by default does not require you to use a password to log in.
We **STRONGLY** recommend that you enable md5 passwords for local connections in your `pg_hba.conf`, and set a password for the default superuser account.

Due to the large number of phpPgAdmin installations that have not set passwords on local connections, there is now a configuration file option called '`extra_login_security`', which is **TRUE** by default. While this option is enabled, you will be unable to log in to phpPgAdmin as the '`root`', '`administrator`', '`pgsql`' or '`postgres`' users and empty passwords will not work.
   
Once you are certain you have properly secured your database server, you can then disable '`extra_login_security`' so that you can log in as your database administrator using the administrator password.

# CONTRIBUTE

* [Source repository](#source-repository)
* [Tips](#tips)
* [Common Variable](#common-variable)
* [Working with recordsets](#working-with-recordsets)
* [Updating language files for the Mono-lingual](#updating-language-files-for-the-mono-lingual)
* [Understanding the Work/Branch/Tag/Release Process](#understanding-the-work-branch-tag-release-process)
* [Getting help](#getting-help)

phpPgAdmin is Free/Open Source software and contributions are welcome from
everyone. 

## Source repository

phpPgAdmin uses git for source control management. The phpPgAdmin git repository 
is hosted at github:

  https://github.com/phppgadmin/phppgadmin

Our development process is based around Pull Requests. The best way to 
contribute is with the following guidelines: 

### = Setup = 

1. Make your own fork of the phppgadmin repository.

2. Add the source repository as a remote called "upstream":
```bash
git remote add upstream git@github.com:phppgadmin/phppgadmin.git
```
or
```bash
git remote add upstream https://github.com/devopsdays/devopsdays-web.git
```

   You only need to create your fork once, as long as you don't delete it. 

### = Patches = 

1. Before starting any new change, it is essential that you rebase your local repository from the upstream. You may think that working from your fork is enough, but sometimes upstream changes will affect your work in ways you may not anticipate, so you'll want to stay current. Issue these commands:

```bash
git checkout master
git pull upstream master --rebase
```

This confirms you are on the master branch locally, and then applies the changes from the upstream to your copy.

2. Create a new local branch for your changes. This helps to keep things tidy!
```
git checkout -b describe_my_fix
```

3. Make your changes, test them locally (use the Selenium tests), then push that branch up to origin on your fork.

```
git push origin describe_my_fix 
```

4. Submit a Pull Request for the branch you just pushed. As a bonus, if you can add either `[BUG]` or `[FEATURE]` to the the title according to the purpose that will help with patch review.

5. Additionally, please mention the versions of PHP and PostgreSQL that you have tested against. 

6. While we would like to enhance our automated testing, until that happens, we at least suggest reviewing the Pull Request on the website and verifying that your changes will merge cleanly. If not, please address any conflicts.

7. As a reminder, smaller patches are easier to digest and consume. If you mix multiple fixes or features into your Pull Requests, it is likely that your submission will not be merged. 

8. Please note that submitting code is considered a transfer of copyright to the phpPgAdmin project. phpPgAdmin is made available under the GPL v2 license.

Push access to the main phpPgAdmin git repository can be granted to developers
with a track record of useful contributions to phpPgAdmin at the discretion of the phpPgAdmin development team. 
                            
## Tips

When you submit code to phpPgAdmin, we do expect it to adhere to the existing coding standards in the source.  So, instead of using your personal favourite
code layout style, please format it to look like surrounding code.
In general, we want the code to be portable, standard compliant (e.g. to W3C (X)HTML and CSS) and independent of specific configurations of PHP, the web server, PostgreSQL or the user browser. We also try to support as many versions as possible of these applications.

Test your code properly! For example, if you are developing a feature to create
domains, try naming your domain all of the following:

* `"`
* `'`
* `\`
* words with spaces
* `<br><br><br>`

Don't forget to make sure your changes still pass the existing Selenium test suite. Additionally, you should add or update the test suite as needed to cover your new features. 

If you are adding a new class function, be sure to use the `clean`, `fieldClean`, `arrayClean` and `fieldArrayClean` functions to properly escape odd characters in user input. Examine existing functions that do similar things to yours to get yours right.

When writing data to the display, you should always `urlencode()` variables in HREFs and `htmlspecialchars()` variables in forms.  Rather than use `action=""` attributes in HTML form elements use `action="thisformname.php"`.  This ensures that browsers remove query strings when expanding the given relative URL into a full URL.

When working on database classes, always schema qualify your SQL where it is possible with the current schema ($data->_schema) for pg73+ classes. Then don't forget to write your method for older classes which don't support schemas.

When working with git, always make sure to do a '`git pull`' both before you start; so you have the latest code to work with; and also again before you create your patch; to minimize the chance of having conflicts. If you plan to submit your code via github pull requests, we strongly recommend doing your work in a feature specific branch. If you want to submit multiple patches, they should all live in their own branch. Remember, smaller changes are easier to review, approve, and merge. 


## Common Variable

* `$data` - A data connection to the current or default database.
* `$misc` - Contains miscellaneous functions.  eg. printing headers & footers, etc.
* `$lang` - Global array containing translated strings. The strings in this array have already been converted to HTML, so you should not `htmlspecialchars()` them.
* `$conf` - Global array of configuration options.

## Working with recordsets

phpPgAdmin uses the ADODB database library for all its database access.  We have also written our own wrapper around the ADODB library to make it more object oriented (`ADODB_base.pclass`).

This is the general form for looping over a recordset:

```php
$rs = $class->getResults();
if (is_object($rs) && $rs->recordCount() > 0) {
	while (!$rs->EOF) {
		echo $rs->fields['field'];
		$rs->moveNext();
	}
}
else echo "No results.";
```

## Updating language files for the Mono-lingual

If you need to add or modify language strings for a new feature, the preferred method is:

* `cd` into `lang/` subdirectory
* modify `english.php` file only! 

If you've done it correctly, when you create your patch, it should only have 
diffs of the lang/english.php file. For more information on how the language system works, please see the [TRANSLATORS](#translators) part.

## Understanding the Work/Branch/Tag/Release Process

All new work for phpPgAdmin is done against the git master branch. When we feel we are ready to do a new release, we create a branch (ex. REL_4-1). This becomes the stable branch for all future 4.1.x releases, and any bugfixes needed for 4.1 would go in that branch.

When we release a new revision, we tag that at release time (REL_4-1-1), so a checkout of any tag should give you the same files that downloading the release would have given you. As a general rule, we do not introduce new features into existing stable branches, only bugfixes and language updates. This means if you want to work on new features, you should be working against the git master.
Eventually we will call for another release, and that will be branched (REL_4-2) and the cycle will start over. 

On occasion we have created out-of-band branches, typically labeled as DEV_foo.
These were used for temporary, concurrent development of large features, and should not be used by other developers. When development of those features is completed, the branches get merged in as appropriate, so no further development  should occur on those branches. 

## Getting help

We prefer communication to happen via Github and Pull Requests. Beyond that, some contributors have been known to hang out on the Postgres Slack Team.

# TRANSLATORS

If you like phpPgAdmin, then why not translate it into your native language?

There are quite a large number of strings to be translated.  Partial
translations are better than no translations at all, and a rough guide is that
the strings are in the order from most important to least important in the
language file.  You can ask the developers list if you don't know what a
certain string means.

We tried keeping translation easy in phpPgAdmin by using ONLY the UTF-8 charset.
Make sure to always work on UTF-8 files when creating a new translation or
editing an existing one.

To Create a new translation:

1. Go to the `lang/` subdirectory

2. Copy `english.php` to `yourlanguage.php`

3. Update the comment at the top of the file.  Put yourself as the language maintainer. Edit the 'applang' variable and put your language's name in it, in your language.
   Edit the 'applocale' and put your language code according to the standard: http://www.ietf.org/rfc/rfc1766.txt

   Basically, you just need to put your language code[¹](http://www.w3.org/WAI/ER/IG/ert/iso639.htm) and optionally country code[²](http://www.iso.org/iso/country_codes/iso_3166_code_lists/country_names_and_code_elements.htm) separated by a `-`. As instance for french canadian, it is: fr-CA

4. Go through as much of the rest of the file as you wish, replacing the English strings with strings in your native language.

   At this point, you can send the `yourlanguage.php` file to us and we will take care of testing and recoding the translation. Please only do that if you find the rest of these steps too difficult.

5. To add your language to phpPgAdmin, edit the `lang/translations.php` file and add your language to the `$appLangFiles` array.
   Also, add your language to the `$availableLanguages` array for browser auto detection.

6. Send your contribution to us. We need the `lang/translations.php` entry as well as the `lang/yourlanguage.php` file. Email to the developers list: [phppgadmin-devel@lists.sourceforge.net](mailto:phppgadmin-devel@lists.sourceforge.net)

7. There exists a tool named `langcheck` in the `lang/ directory`.  To run it, just type `php langcheck <language>`.  It will give you a report about which strings are missing from your language file and which need to be deleted.

Thank you for your contribution! You have just made phpPgAdmin accessible to thousands more users!

# CREDIT

* [Project Administration & Major Projects](#project-administration---major-projects)
* [Translators](#translators)
* [Look & Feel](#look---feel)
* [Contributors](#contributors)
* [Third Party Libraries](#third-party-libraries)
* [Corporate Sponsors](#corporate-sponsors)
* [Project resources:](#project-resources-)
* [Past Feature Sponsors:](#past-feature-sponsors-)

## Project Administration & Major Projects

- Robert Treat (xzilla) 

## Translators

- Kuo Chaoyi (Chinese Utf8) 
- Angelo Rigo (Brazilan Portuguese)
- Chan Min Wai (Chinese)
- He Wei Ping (Chinese)
- Chih-Hsin Lee (Trad. Chinese)
- Hugo Jonker (Dutch)
- Pascal Peyre (French)
- Guillaume Lelarge (French)
- ioguix (French)
- H. Etzel, Markus Bertheau (German)
- Kalef (Italian)
- Tadashi Jokagi (Japanese)
- Rafal Slubowski (Polish)
- Alexander Khodorisky (Russian)
- Martin Marqués (Spanish)
- Andrej Misovic (Slovak)
- Devrim Gunduz (Turkish)
- Libor Vanek (Czech)
- Marek Černocký (Czech)
- Stefan Malmqvist (Swedish)
- Nicola Soranzo (Italian)
- Petri Jooste (Afrikaans)
- Sulyok Péter (Hungarian)
- Zaki Almuallim (Arabic)
- Erdenemandal Bat-Erdene (Mongolian)
- Alex Rootoff (Ukrainian)
- Jonatan (Hebrew)
- Alin Vaida (Romanian)
- Arne Eckmann (Danish)
- Francisco Cabrita (Portuguese)
- Bernat Pegueroles (Catalan)
- Fernando Wendt (Brazilan Portuguese)
- Adamantios Diamantidis (Greek)
- Alexey Baturin (Russian UTF8)
- Adrián Chaves Fernández (Galician)

## Look & Feel

- Davey (CSS)
- ioguix (Cappuccino theme)
- Tomasz Pala (Gotar theme)
- Felipe Figueroa (Bootstrap theme)

## Contributors

- Dan Wilson
- Christopher Kings-Lynne
- Jehan-Guillaume (ioguix) De Rorthais
- Felix Meinhold
- Jean-Michel Poure
- Rafal Slubowski
- Brett Toolin
- Mark Gibson (Pop-up SQL window)
- Nicola Soranzo
- Oliver Meyer & Sven Kiera (Table icons link to browse table)
- Bryan Encina (SQL window improvements, bug fixes, admin)
- Dan Boren (Object comments)
- Adrian Nida (Fix time outs)
- Russell Smith
- Guillaume Lelarge
- Ian Barwick
- Javier Carlos 
- Eric Kinolik
- John Jawed
- Karl O. Pinc  
- Tomasz Pala
- Ivan Zolotukhin 
- Kristoffer `spq` Janke
- Leonardo Augusto Sapiras (Improve phpPgAdmin ergonomy during the GSoC 2010, with ioguix as mentor)
- Julien Rouhaud, aka. rjuju (nested groups)
- Felipe Figueroa aka. amenadiel
- Jean-Michel Vourgère (nirgal)

## Third Party Libraries

- Highlight.php (Jacob D. Cohen of rafb.net)
- XLoadTree2 (Erik Arvidsson & Emil A Eklund of webfx.eae.net)
- jQuery (http://jquery.com/)

## Corporate Sponsors

## Project resources:
- Github => Official project home  

## Past Feature Sponsors:
- SpikeSource (www.spikesource.com) - Slony support
- Google Summer of Code (http://code.google.com/soc/2006/pgsql/appinfo.html?csaid=DB096D908B948D89) - phpPgAdmin Improvements
- Google Summer of Code (http://code.google.com/soc/2007/postgres/appinfo.html?csaid=E89B3D5E2DC4170A) - Full Text Search in PostgreSQL GUI Tools
- Google Summer of Code (http://code.google.com/p/google-summer-of-code-2010-postgres/downloads/detail?name=Leonardo_Augusto_Sapiras.tar.gz) - Improve phpPgAdmin ergonomy
- Dalibo (http://dalibo.com) - sponsored development as Jehan-Guillaume (ioguix) de Rorthais employer
- OmniTI (https://omniti.com) - sponsored development as Robert Treat employer 
- credativ (https://credativ.com) - sponsored php development / review
