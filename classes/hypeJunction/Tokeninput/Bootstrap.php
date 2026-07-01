<?php

namespace hypeJunction\Tokeninput;

use Elgg\Includer;
use Elgg\PluginBootstrap;

/**
 * Plugin bootstrap for elgg_tokeninput.
 */
class Bootstrap extends PluginBootstrap {

	/**
	 * Get plugin root
	 * @return string
	 */
	protected function getRoot() {
		return $this->plugin->getPath();
	}

	/**
	 * {@inheritdoc}
	 */
	public function load() {
		Includer::requireFileOnce($this->getRoot() . '/autoloader.php');
		Includer::requireFileOnce($this->getRoot() . '/lib/tokeninput.php');
	}

	/**
	 * {@inheritdoc}
	 */
	public function boot() {
	}

	/**
	 * {@inheritdoc}
	 */
	public function init() {
		// jquery.tokeninput is a classic jQuery plugin imported by components/
		// tokeninput.mjs. It was registered as a 'views' simplecache entry named
		// 'jquery.tokeninput.js' (.js, not .mjs), which Elgg 7 does NOT add to the
		// importmap, and the bower-asset file was never installed. Register the
		// vendored bundle by absolute URL instead (theme exposes window.jQuery).
		\elgg_register_esm('jquery.tokeninput', \elgg_normalize_url('mod/elgg_tokeninput/vendors/jquery-tokeninput/jquery.tokeninput.min.js'));
	}

	/**
	 * {@inheritdoc}
	 */
	public function ready() {
	}

	/**
	 * {@inheritdoc}
	 */
	public function shutdown() {
	}

	/**
	 * {@inheritdoc}
	 */
	public function activate() {
	}

	/**
	 * {@inheritdoc}
	 */
	public function deactivate() {
	}

	/**
	 * {@inheritdoc}
	 */
	public function upgrade() {
	}
}
