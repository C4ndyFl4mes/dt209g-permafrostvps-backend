<?php

require_once get_template_directory() . '/wp-editor/tier-set.php';


/**
 * Registrerar den anpassade posttypen "section".
 */
add_action('init', function () {
    register_post_type('section', [
        'label' => 'Sections',
        'public' => false,
        'show_ui' => true,
        'show_in_rest' => true,
        'menu_icon' => 'dashicons-welcome-widgets-menus',
        'supports' => ['title', 'page-attributes']
    ]);
});

/**
 * Organiserar tier data från en flat array till en hierarkisk struktur.
 *
 * @param array $tierData - flat array med tier data.
 * @return array - organiserad array med tier data.
 */
function organize_tiers($tierData): array
{
    $organized = [];
    
    foreach ($tierData as $key => $value) {
        // Gör om från t1_name till ['t1']['name']. 
        if (preg_match('/^(t\d+)_(.+)$/', $key, $matches)) {
            $tierNum = $matches[1]; // (t\d+) (tier nummer)
            $fieldName = $matches[2]; // (.+) (fältnamn)
            
            if (!isset($organized[$tierNum])) {
                $organized[$tierNum] = [];
            }
            
            $organized[$tierNum][$fieldName] = $value;
        }
    }
    
    return $organized;
}

/**
 * Returnerar en array av hydrator-funktioner för olika sektionstyper.
 *
 * 'tiercards' sektionstypen hämtar tier-set inlägget och organiserar dess fältdata.
 * 
 * @return array - array av hydrator-funktioner.
 */
function get_section_hydrators(): array
{
    return [
        'tiercards' => function ($id, $acf) {
            $tiers = get_posts([
                'post_type'      => 'tier-set',
                'posts_per_page' => -1,
                'post_status'    => 'publish'
            ]);

            $tierData = !empty($tiers) ? get_fields($tiers[0]->ID) : [];
            $organizedTiers = organize_tiers($tierData);

            return array_merge($acf, [
                'tierset' => $organizedTiers,
            ]);
        }
    ];
}

/**
 * Hämtar sektioner kopplade till en specifik sida.
 *
 * @param array $data - array med 'page_id' nyckel.
 * @return array|WP_Error - array av sektioner eller WP_Error om inga sektioner hittas.
 */
function get_sections($data): array | WP_Error
{
    $sections = get_posts([
        'post_type' => 'section',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'meta_key' => 'attached_page',
        'meta_value' => $data['page_id'],
        'orderby' => 'menu_order',
        'order' => 'ASC'
    ]);

    if (empty($sections)) {
        return new WP_Error('no_sections', 'No sections found for this page.', ['status' => 404]);
    }

    $hydrators = get_section_hydrators();

    return array_map(function ($section) use ($hydrators) {
        $sectionID = $section->ID;
        $type = get_field('section_type', $sectionID);
        $acf = get_fields($sectionID) ?? [];

        if (isset($hydrators[$type])) {
            $acf = $hydrators[$type]($sectionID, $acf);
        }

        return [
            'id' => $sectionID,
            'type' => $type,
            'data' => $acf
        ];
    }, $sections);
}
