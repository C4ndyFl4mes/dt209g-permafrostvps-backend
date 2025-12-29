<?php

require_once get_template_directory() . '/wp-editor/hydration.php';

/**
 * Organiserar tier data från en flat array till en hierarkisk struktur.
 *
 * @param array $tierData - flat array med tier data.
 * @return array - organiserad array med tier data.
 */
function organize_tiers($tierData): array
{
    $temp = [];

    foreach ($tierData as $key => $value) {
        // Matcher nycklar som följer mönstret "t{nummer}_{fält}".
        if (preg_match('/^(t(\d+))_(.+)$/', $key, $matches)) {
            $tierIndex = (int)$matches[2] - 1; // (t\d+) (tier nummer, 1-baserat till 0-baserat)
            $fieldName = $matches[3]; // (.+) (fältnamn)

            if (!isset($temp[$tierIndex])) {
                $temp[$tierIndex] = [];
            }

            $temp[$tierIndex][$fieldName] = $value;
        }
    }

    // Sorterar tiers baserat på deras index och återställer array-nycklarna.
    ksort($temp);
    $organized = array_values($temp);
    return $organized;
}

/**
 * Returnerar en array av hydrator-funktioner för olika sektionstyper.
 * 
 * @return array - array av hydrator-funktioner.
 */
function get_section_hydrators(): array
{
    return [
        'banner' => 'banner_hydrator',
        'tiercards' => 'tiercards_hydrator',
        'text' => 'text_hydrator',
        'header' => 'header_hydrator',
        'support' => 'support_hydrator',
        'news' => 'news_hydrator',
    ];
}

/**
 * Hämtar sektioner kopplade till en specifik sida.
 *
 * @param array $arg - array med 'slug'.
 * @return array|WP_Error - array av sektioner eller WP_Error om inga sektioner hittas.
 */
function get_sections($arg): array | WP_Error
{
    $slug = $arg['slug'] ?? '';
    $page = get_page_by_path($slug, OBJECT, 'page');

    if (!$page) {
        return new WP_Error('page_not_found', 'Page not found', ['status' => 404]);
    }

    $page_id = $page->ID;

    $sections = get_posts([
        'post_type' => 'section',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'meta_key' => 'attached_page',
        'meta_value' => $page_id,
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

        $outputFields = get_fields($sectionID) ?? [];

        // Använder hydratorn för sektionstypen om den finns.
        if (isset($hydrators[$type])) {
            $outputFields = $hydrators[$type]($outputFields, $outputFields['attached_page'] ?? null);
        }

        unset($outputFields['section_type']);
        unset($outputFields['attached_page']);

        return [
            'id' => $sectionID,
            'type' => $type,
            'data' => $outputFields
        ];
    }, $sections);
}
