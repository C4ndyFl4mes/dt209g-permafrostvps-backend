<?php


/**
 * Registrerar menyplatser för temat.
 */
function register_menus(): void {
    register_nav_menus(
        array(
            'sidebar-menu' => __( 'Sidebar Menu' )
        )
    );
}
add_action( 'init', 'register_menus' );


/**
 * Hämtar menydata baserat på menyplats.
 *
 * @param array $data - array med menyplatsen.
 * @return array|WP_Error - array med menydata eller WP_Error om platsen inte finns.
 */
function get_menu_by_location($data): array | WP_Error {
    $location = $data['location'];
    $locations = get_nav_menu_locations();

    if (!isset($locations[$location])) {
        return new WP_Error('invalid_location', 'Menu location does not exist. Invalid: ' . $location, ['status' => 404]);
    }

    $menu = wp_get_nav_menu_object($locations[$location]);
    $menu_items = wp_get_nav_menu_items($menu->term_id);

    return array_map(function($item) {

        // Hämtar slug för sidor och inlägg.
        $slug = $item->post_name;
        if ($item->object === 'page' || $item->object === 'post') {
            $linked_post = get_post($item->object_id);
            if ($linked_post) {
                $slug = $linked_post->post_name;
            }
        }

        return [
            'id' => $item->ID,
            'title' => $item->title,
            'slug' => $slug,
            'page_id' => ($item->object === 'page' || $item->object === 'post') ? (int) $item->object_id : null
        ];
    }, $menu_items);
}