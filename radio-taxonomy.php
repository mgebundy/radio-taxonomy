<?php
/*
Plugin Name: Radio Taxonomy
Plugin URI: http://www.bundy.ca/radio-taxonomy
Description: Make your taxonomies into radio buttons. Easy!
Version: 0.1
Author: Mitchell Bundy
Author URI: http://www.bundy.ca/
*/
/*  Copyright 2008  Mitchell Bundy  (email : mitch@bundy.ca)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
	
	Links to the author's website should remain where they are and cannot be
	removed without permission from the author.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

include_once('walker.php');

if (!class_exists("RadioTaxonomyMB")) {
class RadioTaxonomyMB {
	
	function __construct() {
		add_action('init', array($this, 'set_taxonomies'));
		add_action('admin_menu', array($this, 'meta_boxes'));
	}
	
	function set_taxonomies() {
		global $wp_taxonomies;
		$this->taxonomies = get_taxonomies(array('show_ui' => 'radio'), 'objects');
		
		// Now that we know which taxonomies we're dealing with, let's set show_ui to false so that we can use our own!
		foreach ($this->taxonomies as $tax) {
			$wp_taxonomies[$tax->name]->show_ui = false;
		}
	}
	
	function meta_box($post, $metabox) {
		do_action('radio-taxonomy_box');
		echo '<div class="inside">
		<ul class="categorychecklist">';
		// show_none set? This doesn't do much but show a radio button
		// TODO : actually have this checked when no term is selected
		if ($this->taxonomies[$metabox['args']['taxonomy']]->show_none) {
			echo '<li><label class="selectit"><input value="" type="radio" name="tax_input['.$metabox['args']['taxonomy'].'][]"'.(apply_filters('radio-taxonomy_none-checked', false, $metabox) ? ' checked="checked"' : '').'> ';
			echo apply_filters('radio-taxonomy_none-text', __('None'), $metabox);
			echo '</label></li>';
		}
		$this->category_radio_list($post->ID,$metabox['args']['taxonomy']);
		echo '</ul></div>';
	}
	
	function category_radio_list($post_id, $taxonomy) {
		wp_terms_checklist($post_id, array('taxonomy' => $taxonomy, 'checked_ontop' => false, 'walker' => new Walker_Category_RadioList));
	}
	
	function meta_boxes() {
		// Create the new meta boxes
		foreach ($this->taxonomies as $tax) {
			foreach ($tax->object_type as $post_type) {
				add_meta_box(
					$tax->name.'-div', // id of the <div> we'll add
					$tax->labels->singular_name, //title
					array(&$this,'meta_box'), // callback function that will echo the box content
					$post_type, // where to add the box: on "post", "page", or "link" page
					'side',
					'low',
					array('taxonomy' => $tax->name, 'post_type' => $post_type)
				);
			}
		}
	}
}
new RadioTaxonomyMB;
}
?>