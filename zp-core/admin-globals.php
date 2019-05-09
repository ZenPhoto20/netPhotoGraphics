<?php

/**
 * Initialize globals for Admin
 *
 * @author Stephen Billard (sbillard)
 *
 * @package admin
 */
// force UTF-8 Ø

require_once(dirname(__FILE__) . '/functions-basic.php');
require_once(dirname(__FILE__) . '/initialize-basic.php');

zp_session_start();
require_once(SERVERPATH . '/' . ZENFOLDER . '/admin-functions.php');

$_zp_button_actions = $zenphoto_tabs = array();

if (abs(OFFSET_PATH) != 2) {
	if (TEST_RELEASE) {
		enableExtension('debug', 10 | ADMIN_PLUGIN, false);
	}
	//load feature and admin plugins
	foreach (array(FEATURE_PLUGIN, ADMIN_PLUGIN) as $mask) {
		if (DEBUG_PLUGINS) {
			switch ($mask) {
				case FEATURE_PLUGIN:
					debugLog('Loading the "feature" plugins.');
					break;
				case ADMIN_PLUGIN:
					debugLog('Loading the "admin" plugins.');
					break;
			}
		}
		$enabled = getEnabledPlugins();
		foreach ($enabled as $extension => $plugin) {
			$priority = $plugin['priority'];
			if ($priority & $mask) {
				$start = microtime();
				require_once($plugin['path']);
				if (DEBUG_PLUGINS) {
					zpFunctions::pluginDebug($extension, $priority, $start);
				}
				$_zp_loaded_plugins[$extension] = $extension;
			}
		}
	}

	//	just incase
	require_once(SERVERPATH . '/' . ZENFOLDER . '/functions-filter.php');
	require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/dynamic-locale.php');
}
if (!defined('SEO_FULLWEBPATH')) {
	define('SEO_FULLWEBPATH', FULLWEBPATH);
	define('SEO_WEBPATH', WEBPATH);
}

@ini_set('post_max_size', "10M");
@ini_set('post_input_vars', "2500");

$_SESSION['adminRequest'] = @$_COOKIE['zp_user_auth']; //	Allow "unprotected" i.php if the request came from an admin session

require_once(SERVERPATH . "/" . ZENFOLDER . '/rewrite.php');
if (OFFSET_PATH != 2 && !getOption('license_accepted') && !isset($_zp_invisible_execute)) {
	require_once(dirname(__FILE__) . '/license.php');
}

$_zp_sortby = array(
		gettext('Filename') => 'filename',
		gettext('Date') => 'date',
		gettext('Title') => 'title',
		gettext('ID') => 'id',
		gettext('Filemtime') => 'mtime',
		gettext('Owner') => 'owner',
		gettext('Published') => 'show'
);

// setup sub-tab arrays for use in dropdown
if (@$_zp_loggedin) {
	if ($_zp_current_admin_obj->reset) {
		$_zp_loggedin = USER_RIGHTS;
		$zenphoto_tabs['admin'] = array(
				'text' => gettext("admin"),
				'link' => WEBPATH . "/" . ZENFOLDER . '/admin-tabs/users.php?page=admin&tab=users',
				'ordered' => true,
				'subtabs' => NULL
		);
	} else {
		$admin = $_zp_current_admin_obj->getUser();
		if ($_zp_loggedin & ADMIN_RIGHTS) {
			$_zp_loggedin = ALL_RIGHTS;
		} else {
			if ($_zp_loggedin & MANAGE_ALL_ALBUM_RIGHTS) {
				// these are lock-step linked!
				$_zp_loggedin = $_zp_loggedin | ALBUM_RIGHTS;
			}
		}

		//	establish the menu order
		$zenphoto_tabs['overview'] = NULL;
		$zenphoto_tabs['options'] = NULL;
		$zenphoto_tabs['logs'] = NULL;
		$zenphoto_tabs['admin'] = NULL;
		$zenphoto_tabs['images'] = NULL;
		$zenphoto_tabs['edit'] = NULL;
		$zenphoto_tabs['news'] = NULL;
		$zenphoto_tabs['pages'] = NULL;
		$zenphoto_tabs['comments'] = NULL;
		$zenphoto_tabs['themes'] = NULL;
		$zenphoto_tabs['plugins'] = NULL;
		$zenphoto_tabs['menu'] = NULL;
		$zenphoto_tabs['upload'] = NULL;
		$zenphoto_tabs['development'] = NULL;

		if ($_zp_loggedin & OVERVIEW_RIGHTS) {
			$zenphoto_tabs['overview'] = array('text' => gettext("overview"),
					'link' => WEBPATH . "/" . ZENFOLDER . '/admin.php',
					'subtabs' => NULL);
			$zenphoto_tabs['overview']['subtabs'][gettext('Gallery statistics')] = '/' . ZENFOLDER . '/utilities/gallery_statistics.php?tab=gallerystats';
		}

		zp_register_filter('admin_tabs', 'refresh_subtabs', -1800);

		if ($_zp_loggedin & ALBUM_RIGHTS) {
			if (!$albums = zp_loggedin(MANAGE_ALL_ALBUM_RIGHTS)) {
				foreach ($_zp_gallery->getAlbums() as $key => $analbum) {
					$albumobj = newAlbum($analbum);
					if ($albumobj->isMyItem(ALBUM_RIGHTS)) {
						$albums = true;
						break;
					}
				}
			}
			if ($albums) {
				$zenphoto_tabs['edit'] = array('text' => gettext("albums"),
						'link' => WEBPATH . "/" . ZENFOLDER . '/admin-tabs/edit.php',
						'subtabs' => NULL);
			}
		}


		if (isset($_zp_CMS)) {
			list($articlestab, $categorystab, $pagestab) = cmsFilters::admin_pages();
			if ($articlestab && $categorystab) {
				$zenphoto_tabs['news'] = array('text' => gettext('news'),
						'link' => WEBPATH . "/" . ZENFOLDER . '/' . PLUGIN_FOLDER . '/zenpage/news.php',
						'subtabs' => array(gettext('articles') => PLUGIN_FOLDER . '/zenpage/news.php?page=news&tab=articles',
								gettext('categories') => PLUGIN_FOLDER . '/zenpage/categories.php?page=news&tab=categories'),
						'ordered' => true,
						'default' => 'articles');
			} else if ($articlestab) {
				$zenphoto_tabs['news'] = array('text' => gettext('news'),
						'link' => WEBPATH . "/" . ZENFOLDER . '/' . PLUGIN_FOLDER . '/zenpage/news.php',
						'subtabs' => NULL,
						'ordered' => true,
						'default' => 'articles');
			} else if ($categorystab) {
				$zenphoto_tabs['news'] = array('text' => gettext('categories'),
						'link' => WEBPATH . "/" . ZENFOLDER . '/' . PLUGIN_FOLDER . '/zenpage/categories.php',
						'subtabs' => NULL,
						'ordered' => true,
						'default' => 'categories');
			}
			if ($pagestab) {
				$_zp_loggedin = $_zp_loggedin | ZENPAGE_PAGES_RIGHTS;
				$zenphoto_tabs['pages'] = array('text' => gettext("pages"),
						'link' => WEBPATH . "/" . ZENFOLDER . '/' . PLUGIN_FOLDER . '/zenpage/pages.php',
						'subtabs' => NULL);
			}
		}

		if (getOption('adminTagsTab')) {
			zp_register_filter('admin_tabs', 'tags_subtab', -1900);
		}
		if ($_zp_loggedin & ADMIN_RIGHTS) {
			$zenphoto_tabs['admin'] = array(
					'text' => gettext("admin"),
					'link' => WEBPATH . "/" . ZENFOLDER . '/admin-tabs/users.php?page=admin&tab=users',
					'ordered' => true,
					'subtabs' => array(gettext('users') => 'admin-tabs/users.php?page=admin&tab=users')
			);
		} else {
			if ($_zp_loggedin & USER_RIGHTS) {
				$zenphoto_tabs['admin'] = array(
						'text' => gettext("my profile"),
						'link' => WEBPATH . "/" . ZENFOLDER . '/admin-tabs/users.php?page=admin&tab=users',
						'ordered' => true,
						'subtabs' => NULL
				);
			}
		}
		if (!zp_loggedin(MANAGE_ALL_ALBUM_RIGHTS)) {
			$sql = 'SELECT `filename` FROM ' . prefix('images') . ' WHERE `owner`=' . db_quote($_zp_current_admin_obj->getUser()) . ' LIMIT 1';
			$result = query_single_row($sql);
			if (!empty($result)) {
				$zenphoto_tabs['images'] = array(
						'text' => gettext("my images"),
						'link' => WEBPATH . "/" . ZENFOLDER . '/admin-tabs/images.php?page=admin&tab=images',
						'ordered' => true,
						'subtabs' => NULL
				);
			}
		}

		$subtabs = array();
		$optiondefault = '';
		if ($_zp_loggedin & OPTIONS_RIGHTS) {
			if ($_zp_loggedin & ADMIN_RIGHTS) {
				$optiondefault = '&tab=general';
				$subtabs[gettext("general")] = 'admin-tabs/options.php?page=options&tab=general';
			} else {
				$optiondefault = '&tab=gallery';
			}
			$subtabs[gettext("gallery")] = 'admin-tabs/options.php?page=options&tab=gallery';
			if ($_zp_loggedin & ADMIN_RIGHTS) {
				$subtabs[gettext("security")] = 'admin-tabs/options.php?page=options&tab=security';
			}
			$subtabs[gettext("image")] = 'admin-tabs/options.php?page=options&tab=image';
			$subtabs[gettext("search")] = 'admin-tabs/options.php?page=options&tab=search';
			if ($_zp_loggedin & ADMIN_RIGHTS) {
				$subtabs[gettext("plugin")] = 'admin-tabs/options.php?page=options&tab=plugin';
			}

			if ($_zp_loggedin & THEMES_RIGHTS) {
				if (empty($optiondefault))
					$optiondefault = '&tab=theme';
				$subtabs[gettext("theme")] = 'admin-tabs/options.php?page=options&tab=theme';
			}
			$zenphoto_tabs['options'] = array('text' => gettext("options"),
					'link' => WEBPATH . "/" . ZENFOLDER . '/admin-tabs/options.php?page=options' . $optiondefault,
					'subtabs' => $subtabs,
					'ordered' => true,
					'default' => 'gallery');
		}

		if ($_zp_loggedin & THEMES_RIGHTS) {
			$zenphoto_tabs['themes'] = array('text' => gettext("themes"),
					'link' => WEBPATH . "/" . ZENFOLDER . '/admin-tabs/themes.php',
					'subtabs' => NULL);
		}

		if ($_zp_loggedin & ADMIN_RIGHTS) {
			//NOTE: the following listed variables will be assumed by the admin:plugins script
			list($plugin_subtabs, $plugin_default, $pluginlist, $plugin_paths, $plugin_member, $classXlate, $pluginDetails) = getPluginTabs();
			$zenphoto_tabs['plugins'] = array('text' => gettext("plugins"),
					'link' => WEBPATH . "/" . ZENFOLDER . '/admin-tabs/plugins.php',
					'subtabs' => $plugin_subtabs,
					'ordered' => true,
			);
			zp_register_filter('admin_tabs', 'backup_subtab', -200);
			$zenphoto_tabs['overview']['subtabs'][gettext('Installation information')] = '/' . ZENFOLDER . '/utilities/installation_analysis.php?tab=installstats';
		}

		if ($_zp_loggedin & ADMIN_RIGHTS) {
			list($subtabs, $default, $new) = getLogTabs();
			$zenphoto_tabs['logs'] = array('text' => gettext("logs"),
					'link' => WEBPATH . "/" . ZENFOLDER . '/admin-tabs/logs.php?page=logs',
					'subtabs' => $subtabs,
					'alert' => $new,
					'default' => $default);
			$zenphoto_tabs['overview']['subtabs'][gettext('Database Reference')] = "/" . ZENFOLDER . '/utilities/database_reference.php?tab=databaseref';
		}

		if (!$_zp_current_admin_obj->getID()) {
			$filelist = safe_glob(SERVERPATH . "/" . BACKUPFOLDER . '/*.zdb');
			if (count($filelist) > 0) {
				$zenphoto_tabs['admin']['subtabs']['restore'] = 'utilities/backup_restore.php?tab=backup';
			}
		}

		$zenphoto_tabs = zp_apply_filter('admin_tabs', $zenphoto_tabs);
		foreach ($zenphoto_tabs as $tab => $value) {
			if (is_null($value)) {
				unset($zenphoto_tabs[$tab]);
			}
		}

		if (isset($zenphoto_tabs['admin']['subtabs']) && count($zenphoto_tabs['admin']['subtabs']) == 1) {
			$zenphoto_tabs['admin']['subtabs'] = NULL;
		}

		//	so as to make it generally available as we make much use of it
		if (OFFSET_PATH != 2) {
			require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/colorbox_js.php');
		}
	}
	loadLocalOptions(0, $_zp_gallery->getCurrentTheme());
}
?>
