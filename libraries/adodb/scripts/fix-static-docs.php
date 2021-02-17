<?php 
/**
* A Program to post-process the dokuwiki document export to clean it up
* and fix the broken links
*
* @link http://adodb.org/dokuwiki/doku.php?id=v6:offline_docs_build
* @author Mark Newnham
* @since 02/13/2015
*/

/**
* Recurses a directory and deletes files inside
*
* Copied from php.net
*
* @param string $dir  The driectory name
* $return null
*/
function delTree($dir) { 
    $files = array_diff(scandir($dir), array('.','..')); 
    foreach ($files as $file) {
      (is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file"); 
    } 
    return rmdir($dir); 
} 

/**
* Initializes the listdiraux method with a starting directory point
*
* copied from php.net
*
* @param  string $dir	Starting directory
* @return array			FQ list of files
*/  
function listdir($dir='.') { 
    if (!is_dir($dir)) { 
        return false; 
    } 
    $files = array(); 
    listdiraux($dir, $files); 

    return $files; 
} 

/**
* Recurses a directory structure and creates a list of files
*
* @param  string 	$dir	Starting directory
* @param  string[]  $files  By reference, the current file list
* @return null
*/  
function listdiraux($dir, &$files) { 
    $handle = opendir($dir); 
    while (($file = readdir($handle)) !== false) { 
        if ($file == '.' || $file == '..') { 
            continue; 
        } 
        
		if (preg_match('/v6$/',$file))
			/*
			* This is only v5 documentation
			*/
			continue;
		
		$filepath = $dir == '.' ? $file : $dir . '/' . $file; 
        if (is_link($filepath)) 
            continue; 
        if (is_file($filepath)) 
            $files[] = $filepath; 
        else if (is_dir($filepath)) 
            listdiraux($filepath, $files); 
    } 
    closedir($handle); 
} 
/*
* Clean up the documentation directory from prior use
*/
if (is_dir('documentation'))
	deltree('documentation');
mkdir('documentation');

$files = listdir('documentation-base'); 
sort($files, SORT_LOCALE_STRING); 

/*
* Loop through files in documentation-base directory, creating a mirror
* structure in documentation, and applying the post-process rules defined
* below
*/
foreach ($files as $f) { 

	$r = str_replace('documentation-base','documentation',$f);
    $dList = explode ('/',$r);
	$titleList = $dList;
	/*
	* Get rid of the initial directory
	*/
	array_shift($titleList);
	
	$depth = count($dList) -2;
	$dSlash = '';
	while(count($dList) > 1)
	{
		$dSlash .= array_shift($dList) . '/';
		if (!is_dir($dSlash))
			mkdir($dSlash);
	}
	if (!is_file($f))
		continue;
	if (substr($f,-4) <> 'html')
	{
		/*
		* An image or something else, copy unmodified
		*/
		copy ($f,$r);
		continue;
	}
		
	$prepend = str_repeat('../',$depth);
	
	$doc = new DOMDocument();
	@$doc->loadHTMLFile($f);
	
	/*
	* Remove Page Tools Group
	*/
	$xpath = new DOMXPath($doc);
		
	/*
	* Remove Top Menu Tools Group, and add a link to the ADOdb site
	*/
	$nodes = $xpath->query("//div[@class='tools group']");
	foreach($nodes as $node) {
		$pn = $node->parentNode;
		$pn->removeChild($node);
		$newChild = $doc->createElement('div','');
		$newDiv = $pn->appendChild($newChild);
		$newDiv->setAttribute('style','text-align:right');
		$newChild = $doc->createElement('a','ADOdb Web Site');
		$newA = $newDiv->appendChild($newChild);
		$newA->setAttribute('href','http://adodb.org');
    }
	
	/*
	* Remove Trace
	*/
	$nodes = $xpath->query("//div[@class='breadcrumbs']");
	foreach($nodes as $node) {
		$node->parentNode->removeChild($node);
    }
	
	/*
	* Remove Side Menu Tools Group
	*/
	$nodes = $xpath->query("//div[@id='dokuwiki__pagetools']");
	foreach($nodes as $node) {
		$node->parentNode->removeChild($node);
    }
	
	/*
	* Fix main links
	*/
	$nodes = $xpath->query("//a[@class='wikilink1']");
	foreach($nodes as $node) {
		$n =  $node->getAttribute('title');
		$p = $prepend . str_replace(':','/',$n) . '.html';
		$node->setAttribute('href', $p);
    }
	
	/*
	* Fix In Page links
	*/
	$nodes = $xpath->query("//a[@class='wikilink2']");
	foreach($nodes as $node) {
		$n =  $node->getAttribute('title');
		$p = $prepend . str_replace(':','/',$n) . '.html';
		$node->setAttribute('href', $p);
    }
	
	/*
	* Make Graphic point to first page. This will break if the image size
	* ever changes.
	*/
	$corePage = $prepend . '/index.html';
	$nodes = $xpath->query("//img[@width='176']");
	foreach($nodes as $node) {
		$node->parentNode->setAttribute('href', $corePage);
    }
	
	/*
	* Change title of page
	*/
	$nodes = $xpath->query("//title");
	foreach($nodes as $node) {
		
		
		$docTitle = implode(':',$titleList);
		$docTitle = str_replace('.html','',$docTitle);
		$pn = $node->parentNode; 
		$pn->removeChild($node);
		$newChild = $doc->createElement('title',$docTitle);
		$pn->appendChild($newChild);
		
    }
		
	$doc->saveHTMLFile($r);
	
    echo  $r, "\n"; 
}
/*
* Now remove the original index and replace it with the hardcopy documentation one
*/
unlink ('documentation/index.html');
rename('documentation/adodb_index.html','documentation/index.html');

/*
* We could add in an auto zip and upload here, but this is a good place to
* stop and check the output
*/

?>
