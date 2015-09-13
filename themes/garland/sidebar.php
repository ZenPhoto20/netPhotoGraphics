<?php
// force UTF-8 Ø

if (getOption('Allow_search')) {
	switch ($_zp_gallery_page) {
		case 'album.php':
		case 'image.php':
			$list = array('albums' => array($_zp_current_album->name), 'pages' => '0', 'news' => '0');
			$text = gettext('Search');
			break;
		case 'gallery.php':
			$list = array('albums' => '1', 'pages' => '0', 'news' => '0');
			$text = gettext('Search');
			break;
		case 'pages.php':
			$list = array('albums' => '0', 'pages' => '1', 'news' => '0');
			$text = gettext('Search');
			break;
		case 'news.php':
			if (is_NewsCategory()) {
				$list = array('news' => array($_zp_current_category->getTitlelink()), 'albums' => '0', 'images' => '0', 'pages' => '0');
				$text = gettext('Search');
			} else {
				$list = array('news' => '1', 'albums' => '0', 'images' => '0', 'pages' => '0');
				$text = gettext("Search");
			}
			break;
		case 'search.php':
			$categorylist = $_zp_current_search->getCategoryList();
			if (is_array($categorylist)) {
				$list = array('news' => $categorylist, 'albums' => '0', 'images' => '0', 'pages' => '0');
				$text = gettext('Search');
			} else {
				$albumlist = $_zp_current_search->getAlbumList();
				if (is_array($albumlist)) {
					$list = array('albums' => $albumlist, 'pages' => '0', 'news' => '0');
					$text = gettext('Search');
				} else {
					$list = NULL;
					$text = gettext('Search');
				}
			}
			break;
		default:
			$list = NULL;
			$text = gettext('Search');
			break;
	}
	printSearchForm(NULL, 'search', $_zp_themeroot . '/images/search.png', $text, NULL, NULL, $list);
	?>
	<br class="clearall" />
	<?php
}

if (function_exists('printCustomMenu') && getThemeOption('custom_index_page', NULL, 'garland') === 'gallery' && ($menu = getThemeOption('garland_menu'))) {
	?>
	<!-- custom menu -->
	<div class="menu">
		<?php
		printCustomMenu($menu, 'list', '', "menu-active", "submenu", "menu-active", 2);
		?>
	</div>
	<?php
} else { //	"standard zenpage sidebar menus
	?>
	<!-- standard menu -->	<?php
	if (extensionEnabled('zenpage') && ZP_NEWS_ENABLED) {
		if (getNumNews(true)) {
			?>
			<div class="menu">
				<h3><?php echo gettext("News articles"); ?></h3>
				<?php
				printAllNewsCategories(gettext("All news"), TRUE, "news_menu", "menu", true, "menu_sub", "menu_sub_active");
				?>
			</div>
			<?php
		}
	}
	?>

	<?php
	if (function_exists("printAlbumMenu")) {
		?>
		<div class="menu">
			<?php
			if (extensionEnabled('zenpage')) {
				if ($_zp_gallery_page == 'index.php' || $_zp_gallery_page != 'gallery.php') {
					?>
					<h3>
						<a href="<?php echo html_encode(getCustomPageURL('gallery')); ?>" title="<?php echo gettext('Album index'); ?>"><?php echo gettext("Gallery"); ?></a>
					</h3>
					<?php
				}
			} else {
				?>
				<h3><?php echo gettext("Gallery"); ?></h3>
				<?php
			}
			printAlbumMenu("list", "count", "album_menu", "menu", "menu_sub", "menu_sub_active", '');
			?>
		</div>
		<?php
	} else {
		if (extensionEnabled('zenpage')) {
			?>
			<div class="menu">
				<h3><?php echo gettext("Albums"); ?></h3>
				<ul id="album_menu">
					<li>
						<a href="<?php echo html_encode(getCustomPageURL('gallery')); ?>" title="<?php echo gettext('Album index'); ?>"><?php echo gettext('Album index'); ?></a>
					</li>
				</ul>
			</div>
			<?php
		}
	}
	?>

	<?php
	if (extensionEnabled('zenpage') && ZP_PAGES_ENABLED) {
		if (getNumPages(true)) {
			?>
			<div class="menu">
				<h3><?php echo gettext("Pages"); ?></h3>
				<?php
				printPageMenu("list", "page_menu", "menu-active", "submenu", "menu-active");
				?>
			</div>
			<?php
		}
	}
}
?>