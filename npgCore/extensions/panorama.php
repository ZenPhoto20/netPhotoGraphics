<?php
/*
 * This plugin is based on {@link https://github.com/terrymun/paver <b>Paver</b>} by terrymun
 *
 * The plugin will display exceptionally wide images as a scrollable panorama. Images which
 * fit into the image container will not be forced into panorama mode, so the function
 * can replace <code>printDefaultSizedImage()</code> in your <i>image.php</i> script.
 *
 * Typical usage:
 *
 * <code>&nbsp;&nbsp;if(class_exists('panorama') {	//	the plugin is enabled</code><br>
 * <code>&nbsp;&nbsp;&nbsp;&nbsp;panorama::image();</code><br>
 * <code>&nbsp;&nbsp;} else {</code><br>
 * <code>&nbsp;&nbsp;&nbsp;&nbsp;printDefaultSizedImage(getImageTitle());</code><br>
 * <code>&nbsp;&nbsp;}</code>
 *
 *
 * The <code>image<code> function has optional parameters: <i><code>alt&nbsp;text</code></i>, <i><code>class</code></i>,
 * <i><code>id</code></i>, and <i><code>image&nbsp;object</code></i>. if <i><code>image&nbsp;object</code></i> is not supplied the
 * current image object will be used. If <i><code>alt&nbsp;text</code></i> is not supplied the image
 * title will be used.
 *
 * @author Stephen Billard (sbillard)
 *
 * @package plugins/panorama
 * @pluginCategory media
 *
 * @Copyright 2020 by Stephen L Billard for use in {@link https://%GITHUB% netPhotoGraphics} and derivatives
 */

$plugin_is_filter = 9 | THEME_PLUGIN;
$plugin_description = gettext('A plugin to display photo images as a panorama.');

$option_interface = 'panorama';

npgFilters::register('theme_head', 'panorama::head');

class panorama {

	function __construct() {
		if (OFFSET_PATH == 2) {
			setOptionDefault('panorama_height', 400);
			setOptionDefault('panorama_start', 50);
			setOptionDefault('panorama_overflow', 200);
		}
	}

	function getOptionsSupported() {
		return array(
				gettext('Panorama height') => array('key' => 'panorama_height', 'type' => OPTION_TYPE_NUMBER,
						'order' => 1,
						'desc' => sprintf(gettext('The image will be resized so that its <span style="color:blue">%1$s</span> pixels high when displayed. The image width will be scaled proportionately.'), getOption('panorama_height'))),
				gettext('Start position') => array('key' => 'panorama_start', 'type' => OPTION_TYPE_CUSTOM,
						'limits' => array('min' => 0, 'max' => 100, 'step' => 1),
						'order' => 2,
						'desc' => gettext('Indicate the start position of the panorama by positioning the slider.')),
				gettext('Minimum overflow') => array('key' => 'panorama_overflow', 'type' => OPTION_TYPE_NUMBER,
						'order' => 3,
						'desc' => sprintf(gettext('The excess width the panorama must have, in pixels, before the image is considered panoramic. In other words, this option allows the image\'s computed width to exceed that of its parent container by <span style="color:blue">%1$s</span> pixels before the image is panned. Nobody wants a panorama that can barely be panned, right?'), getOption('panorama_overflow')))
		);
	}

	function handleOption($key, $v) {
		putSlider('<span style="float:left">' . gettext('image left') . '</span><span style="float:right">' . gettext('image right') . '</span><br />', $key, 0, 100, $v, FALSE);
	}

	static function head() {
		scriptLoader(CORE_SERVERPATH . PLUGIN_FOLDER . '/panorama/jquery.paver.min.js');
		scriptLoader(CORE_SERVERPATH . PLUGIN_FOLDER . '/panorama/jquery.ba-throttle-debounce.min.js');
		scriptLoader(CORE_SERVERPATH . PLUGIN_FOLDER . '/panorama/paver.css');
		?>
		<style>
			.nPG_panorama {
				overflow-x: hidden;
				overflow-y: hidden;
			}
		</style>
		<script type="text/javascript">
			$(document).ready(function () {
				$(function () {
					// Paver
					$('div.panorama').paver({
						failureMessage: '<?php echo gettext('Scroll left/right to pan through panorama.'); ?>',
						minimumOverflow: <?php echo getOption('panorama_overflow');
		?>,
						startPosition: <?php echo getOption('panorama_start') / 100; ?>
					}
					);
				});
			});
		</script>
		<?php
	}

	static function image($title = NULL, $class = NULL, $id = NULL, $image = NULL) {
		global $_current_image, $_gallery;
		if (is_null($image)) {
			$image = $_current_image;
		}
		if (is_null($image)) {
			return false;
		}
		if (empty($title)) {
			$title = $image->getTitle();
		}
		$extra = '';
		if ($class) {
			$extra = 'class="' . $class . '" ';
		}
		if ($id) {
			$extra .= 'id=' . $id;
		}

		$h = $image->getHeight();
		$w = $image->getWidth();
		$height = getOption('panorama_height');
		$width = (int) ($height / $h * $w);

		$height = getOption('panorama_height');
		$img_link = $image->getCustomImage(NULL, $width, $height, NULL, NULL, NULL, NULL);
		if (strpos($img_link, 'i.php') !== FALSE) { //	image processor link, cache the image
			require_once(dirname(__DIR__) . '/lib-image.php');
			imageProcessing::cacheFromImageProcessorURI($img_link);
			$img_link = $image->getCustomImage(NULL, $width, $height, NULL, NULL, NULL, NULL);
		}
		?>
		<div class="panorama" data-paver>
			<img src="<?php echo $img_link ?>" alt="<?php echo $title ?>" <?php echo trim($extra); ?>/>
		</div>

		<?php
	}

}
