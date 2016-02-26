<?php

/**
 * File : WordPress Navigation Menus
 *
 * @package Boilerplate
 */

namespace Boilerplate;

/**
 * Class : Navigation Menus
 *
 * @link http://codeseekah.com/2012/03/01/custom-post-type-archives-in-wordpress-menus-2/
 * @link https://gist.github.com/davidmh/8050982
 */

class Menus
{
	static $obj_archive = 'boilerplate-archive';
	static $post_types  = [];

	public static function init()
	{
		// Appearance / Menus
		add_action( 'admin_head-nav-menus.php', [ get_called_class(), 'add_meta_boxes' ] );

		// Menu Panel
		add_filter( 'wp_get_nav_menu_items',    [ get_called_class(), 'get_items'               ], 10, 3 );
		add_filter( 'wp_setup_nav_menu_item',   [ get_called_class(), 'setup_item'              ] );
//		add_filter( 'wp_update_nav_menu_item',  [ get_called_class(), 'update_item'             ], 10, 3 );
		add_filter( 'wp_nav_menu_objects',      [ get_called_class(), 'item_classes_by_context' ], 10, 2);
		add_filter( 'wp_nav_trail_objects',     [ get_called_class(), 'item_classes_by_context' ], 10, 2);

		// Menu Icons Plugin
		// add_filter( 'menu_icons_disable_settings', '__return_true' );
	}

	/**
	 * Add custom meta boxes to the nav menu editor
	 */

	public static function add_meta_boxes()
	{
		static::$post_types = get_post_types( [ 'has_archive' => true ], 'object' );

		if ( ! empty( static::$post_types ) ) {
			add_meta_box( 'add-archives', __('Archives', 'boilerplate'), [ get_called_class(), 'archives_meta_box' ], 'nav-menus', 'side', 'default' );
		}
	}

	/**
	 * Archives Menu
	 *
	 * @param  mixed  $object   Current object.
	 * @param  array  $metabox  Box settings.
	 */

	public static function archives_meta_box( $object, $metabox )
	{
		global $nav_menu_selected_id;

		if ( empty( static::$post_types ) ) {
			echo '<p>' . __('No items.') . '</p>';
			return;
		}

		$walker = new \Walker_Nav_Menu_Checklist([]);

?>
		<div id="<?php echo static::$obj_archive; ?>" class="<?php echo static::$obj_archive; ?>div">
			<div id="tabs-panel-<?php echo static::$obj_archive; ?>" class="tabs-panel tabs-panel-active">
				<ul id="<?php echo static::$obj_archive; ?>-checklist" class="categorychecklist form-no-clear"><?php

					echo walk_nav_menu_tree( array_map( 'wp_setup_nav_menu_item', static::$post_types ), 0, (object) [ 'walker' => $walker ] );

				?></ul>
			</div><!-- /.tabs-panel -->
		</div>
		<p class="button-controls">
			<span class="add-to-menu">
				<input type="submit"<?php wp_nav_menu_disabled_check( $nav_menu_selected_id ); ?> class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e('Add to Menu'); ?>" name="add-<?php echo static::$obj_archive; ?>-menu-item" id="submit-<?php echo static::$obj_archive; ?>" />
				<span class="spinner"></span>
			</span>
		</p>
<?php

	}

	/**
	 * Filter the navigation menu items being returned.
	 *
	 * @param  array   $items  An array of menu item post objects.
	 * @param  object  $menu   The menu object.
	 * @param  array   $args   An array of arguments used to retrieve menu item objects.
	 * @return array   $items
	 */

	public static function get_items( $items, $menu, $args )
	{
		foreach ( $items as &$item ) {
			if ( $item->type === static::$obj_archive ) {

				$obj_type = get_post_type_object( $item->object );

				if ( ! $obj_type ) {
					continue;
				}

				if ( 'post' === $obj_type->name ) {
					$page_id = get_option('page_for_posts');

					if ( ! empty( $page_id ) ) {
						$item->title = get_the_title( $page_id );
					}
				}
				elseif ( ! empty( $obj_type->labels->page_for_items_setting ) ) {
					$options = get_option('boilerplate', []);
					$setting = $obj_type->labels->page_for_items_setting;

					if ( ! empty( $options[ $setting ] ) ) {
						$item->title = get_the_title( pll_get_post( $options[ $setting ] ) );
					}
				}

				$item->url = get_post_type_archive_link( $item->object );

				/**
				 * @deprecated The _wp_menu_item_classes_by_context() function
				 *             resets this property.
				 */
/*
				if ( get_query_var( 'post_type' ) == $item->object ) {
					$item->classes[] = 'current-menu-item';
					$item->current = true;
				}
*/
			}
		}

		return $items;
	}

	/**
	 * Decorates a 'boilerplate-archive' menu item object with
	 * the shared navigation menu item properties.
	 *
	 * Properties to decorate Archive:
	 * - object_id:     Deprecated: The type of object represented, such as "post," "boilerplate-statements", or "boilerplate-faq."
	 * - type:          The family of objects originally represented, "boilerplate-archive."
	 * - object:        The type of object represented, such as "post," "boilerplate-statements", or "boilerplate-faq."
	 * - type_label:    "Archive"
	 *
	 * @param   object  $item  The menu item to modify.
	 * @return  object  $item  The menu item with standard menu item properties.
	 */

	public static function setup_item( $item )
	{
		// Is Post Type Object
		if ( isset( $item->has_archive ) && $item->has_archive && isset( $item->name ) && post_type_exists( $item->name ) ) {
			if ( ! isset( $item->ID ) ) {
				$item->ID = 0;
			}
			$item->db_id            = 0;
			$item->object_id        = $item->ID;
			$item->object           = ( empty( $item->object ) ? get_post_meta( $item->ID, '_menu_item_type', true ) : $item->name );
			$item->post_parent      = 0;
			$item->menu_item_parent = ( empty( $item->menu_item_parent ) ? get_post_meta( $item->ID, '_menu_item_menu_item_parent', true ) : $item->menu_item_parent );
			$item->type             = ( empty( $item->type ) ? get_post_meta( $item->ID, '_menu_item_type', true ) : static::$obj_archive ); // 'post_type' or 'custom'
			$item->type_label       = __('Archive');
			$item->title            = sprintf( __('%s Archive','boilerplate'), $item->labels->name );
			$item->url              = get_post_type_archive_link( $item->name );
			$item->description      = apply_filters( 'nav_menu_description', '' );
			$item->attr_title       = apply_filters( 'nav_menu_attr_title', '' );
			$item->classes          = [];
			$item->target           = '';
			$item->xfn              = '';
		}

		/**
		 * Filter a navigation menu item object.
		 *
		 * @param object $item The menu item object.
		 */
		return $item;
	}

	/**
	 * Add the class property classes for the current context, for custom items.
	 *
	 * Set up the $item variables because `_wp_menu_item_classes_by_context()`
	 * does not offer any hooks for custom objects.
	 *
	 * @see {method} WordPress\wp_nav_menu()
	 * @see {method} WordPress\_wp_menu_item_classes_by_context()
	 *
	 * @param array   $items  The menu items, sorted by each menu item's menu order.
	 * @param object  $args   An object containing wp_nav_menu() arguments.
	 */

	public static function item_classes_by_context( $items = [], $args )
	{
		global $wp_query, $wp_rewrite;

		$queried_object    = $wp_query->get_queried_object();
		$queried_object_id = (int) $wp_query->queried_object_id;

		$active_object = '';
		$active_parent_item_ids   = [];
		$active_ancestor_item_ids = [];

		foreach ( (array) $items as $key => $item ) {

			// If the menu item corresponds to the currently-queried post object's parent
			if ( ! $item->current && isset( $item->object_id ) && isset( $queried_object->post_parent ) && $item->object_id == $queried_object->post_parent && 'post_type' == $item->type && $wp_query->is_singular ) {
				$items[ $key ]->current_item_parent = true;
				$_anc_id = (int) $item->db_id;

				while (
					( $_anc_id = get_post_meta( $_anc_id, '_menu_item_menu_item_parent', true ) ) &&
					! in_array( $_anc_id, $active_ancestor_item_ids )
				) {
					$active_ancestor_item_ids[] = $_anc_id;
				}

				$active_parent_item_ids[] = (int) $item->db_id;
			}

			// Is Post Type Object
			if ( $item->type === static::$obj_archive && post_type_exists( $item->object ) ) {

				$classes   = (array) $item->classes;
				$classes[] = 'menu-item';
				$classes[] = 'menu-item-type-' . $item->type;
				$classes[] = 'menu-item-object-' . $item->object;

				if ( get_query_var('post_type') == $item->object || 'post' === $item->object && is_home() ) {
					//$classes[] = 'current-menu-item';
					$classes[] = 'is-current';
					$items[ $key ]->current = true;
					$active_parent_item_ids[] = (int) $item->menu_item_parent;
					$active_object = $item->object;
				}

				$items[ $key ]->classes = array_unique( $classes );
			}

		}

		$active_ancestor_item_ids = array_filter( array_unique( $active_ancestor_item_ids ) );
		$active_parent_item_ids   = array_filter( array_unique( $active_parent_item_ids ) );

		if ( ! empty( $active_ancestor_item_ids ) || ! empty( $active_parent_item_ids ) ) {

			// set parent's class
			foreach ( (array) $items as $key => $parent_item ) {
				$classes = [];

				if ( in_array( intval( $parent_item->db_id ), $active_ancestor_item_ids ) ) {
					$classes[] = 'current-menu-ancestor';
					$items[ $key ]->current_item_ancestor = true;
				}

				if ( in_array( $parent_item->db_id, $active_parent_item_ids ) ) {
					$classes[] = 'current-menu-parent';
					$items[ $key ]->current_item_parent = true;
				}

				if ( ! empty( $classes ) ) {
					$items[ $key ]->classes = array_unique( array_merge( $parent_item->classes, $classes ) );
				}
			}

		}

		return $items;
	}

}

Menus::init();
