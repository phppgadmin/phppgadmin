<?php

	/**
	 * Main object browser.  This page first shows a list of databases and then
	 * if you click on a database it shows a list of database objects in that
	 * database.
	 *
	 * $Id: browser.php,v 1.59 2008/02/13 23:03:05 ioguix Exp $
	 */

	// Include application functions
	$_no_db_connection = true;
	$_no_bottom_link = true;
	include_once('./libraries/lib.inc.php');

	// Output header
	$misc->printHeader('', <<<EOL
		<script src="xloadtree/xtree2.js" type="text/javascript"></script>
		<script src="xloadtree/xloadtree2.js" type="text/javascript"></script>
		<style type="text/css">
			.webfx-tree-children { background-image: url("{$misc->icon('I')}"); }
		</style>
	EOL);

	$misc->printBody('browser');
?>
	<div dir="ltr">
		<div class="logo">
			<a href="intro.php" target="detail">
				<?= htmlspecialchars($appName) ?>
			</a>
		</div>

		<div class="refreshTree">
			<a href="browser.php" target="browser">
				<img src="<?= $misc->icon('Refresh'); ?>" alt="<?= $lang['strrefresh']; ?>" title="<?= $lang['strrefresh']; ?>" />
			</a>
		</div>

		<script type="text/javascript">
			webFXTreeConfig.rootIcon			= "<?= $misc->icon('Servers') ?>";
			webFXTreeConfig.openRootIcon		= "<?= $misc->icon('Servers') ?>";
			webFXTreeConfig.folderIcon			= "";
			webFXTreeConfig.openFolderIcon		= "";
			webFXTreeConfig.fileIcon			= "";
			webFXTreeConfig.iIcon				= "<?= $misc->icon('I') ?>";
			webFXTreeConfig.lIcon				= "<?= $misc->icon('L') ?>";
			webFXTreeConfig.lMinusIcon			= "<?= $misc->icon('Lminus') ?>";
			webFXTreeConfig.lPlusIcon			= "<?= $misc->icon('Lplus') ?>";
			webFXTreeConfig.tIcon				= "<?= $misc->icon('T') ?>";
			webFXTreeConfig.tMinusIcon			= "<?= $misc->icon('Tminus') ?>";
			webFXTreeConfig.tPlusIcon			= "<?= $misc->icon('Tplus') ?>";
			webFXTreeConfig.blankIcon			= "<?= $misc->icon('blank') ?>";
			webFXTreeConfig.loadingIcon			= "<?= $misc->icon('Loading') ?>";
			webFXTreeConfig.loadingText			= "<?= $lang['strloading'] ?>";
			webFXTreeConfig.errorIcon			= "<?= $misc->icon('ObjectNotFound') ?>";
			webFXTreeConfig.errorLoadingText	= "<?= $lang['strerrorloading'] ?>";
			webFXTreeConfig.reloadText			= "<?= $lang['strclicktoreload'] ?>";

			// Set default target frame:
			WebFXTreeAbstractNode.prototype.target = 'detail';

			// Disable double click:
			WebFXTreeAbstractNode.prototype._ondblclick = function(){}

			// Show tree XML on double click - for debugging purposes only
			/*
			// UNCOMMENT THIS FOR DEBUGGING (SHOWS THE SOURCE XML)
			WebFXTreeAbstractNode.prototype._ondblclick = function(e){
				var el = e.target || e.srcElement;

				if (this.src != null)
					window.open(this.src, this.target || "_self");
				return false;
			};
			*/

			var tree = new WebFXLoadTree("<?= $lang['strservers']; ?>", "servers.php?action=tree", "servers.php");

			tree.write();
			tree.setExpanded(true);
		</script>
	</div>
<?php
	// Output footer
	$misc->printFooter();
