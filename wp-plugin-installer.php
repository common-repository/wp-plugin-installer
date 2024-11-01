<?php
/*
Plugin Name: WP Plugin Installer
Plugin URI: http://www.developersmind.com/wp-plugins/wp-plugin-installer
Description: Adds advanced plugin installation options to WordPress. Allow you install the development version of a plugin.
Version: 0.1
Author: Pete Mall
Author URI: http://developersmind.com
*/

/*  Copyright 2009  Pete Mall  (email : pete@jointforcestech.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class jfts_wp_plugin_installer {
	/**
	 * Overides the return from plugins_api().
	 *
	 * Checks if the action was 'plugin_information' and returns the development version link if requested.
	 *
	 * @author Pete Mall <pete@jointforcestech.com>
	 * @since 0.1
	 * @return array array of 'plugin_infromation'
	 */
	function plugins_api($res, $action, $args) {
		if ( 'plugin_information' == $action ) {
			if ( strncmp($args->slug, 'DEV_VERSION', 8) == 0 ) {
				$args->slug = strtok($args->slug, 'DEV_VERSION');
				$res = plugins_api( $action, $args );
				$link = explode( $args->slug,$res->download_link );
				$res->download_link = $link[0].$args->slug.'.zip';
				$res->version = 'Development Version';
			}
		}
		return $res;
	}

	/**
	 * Adds the link to install the development version of a plugin.
	 *
	 * Adds a 'DEV_VERSION' prefix to the plugin slug and a link to install the development version of the plugin on the plugin install page.
	 *
	 * @author Pete Mall <pete@jointforcestech.com>
	 * @since 0.1
	 * @return none
	 */
	function install_plugin_information() {
		$slg = stripslashes( $_REQUEST['plugin'] );
		$slug = 'DEV_VERSION'.$slg;
		$url = plugins_api('plugin_information', array('slug' => $slug ))->download_link;
		$installed_plugin = get_plugins('/' . $slg);
		if ( empty($installed_plugin) ) {
			if ( current_user_can('install_plugins') && '200' ==  wp_remote_retrieve_response_code(wp_remote_get($url)) ) :
				?><div align="center"><p class="action-button" style="width:200;"><a href="<?php echo wp_nonce_url(admin_url('update.php?action=install-plugin&plugin=' . $slug), 'install-plugin_' . $slug) ?>" target="_parent"><?php _e('Install Development Version') ?></a></p></div><br/><?php
			else:
				?><div align="center"><p class="action-button" style="width:400;">Development Version of this plugin is not available</p></div><br/><?php
			endif;
		} else {
			?><div align="center"><p class="action-button" style="width:500;">Please uninstall the plugin if you would like to install the development version.</p></div><br/><?php
		}
	}

}

/**
 * Check if the plugin class exists and instantiate it. Hook into 'plugins_api_result' to add the download link for the development version.
 * Hook into 'install_plugins_pre_plugin-information' to add the link for installing the development version.
 */
if (class_exists("jfts_wp_plugin_installer")) {
	$wp_plugin_installer = new jfts_wp_plugin_installer();
	add_filter('plugins_api_result', array(&$wp_plugin_installer, 'plugins_api'), 10, 3);
	add_action('install_plugins_pre_plugin-information', array(&$wp_plugin_installer, 'install_plugin_information'));
}

?>