<?php

/**
 * Hydrator-funktion för 'banner' sektionstypen.
 *
 * Hämtar banner-bilden från site configuration och organiserar dess fältdata.
 * 
 * @param array $acf - befintliga ACF-fält för sektionen.
 * @return array - uppdaterade ACF-fält med organiserad banner data.
 */
function banner_hydrator($acf, $page_id) {
    $site_config = get_site_config();
    $banner_unfiltered = $site_config['layout_settings']['banner_image'] ?? [];
    $banner = [
        'title' => $banner_unfiltered['title'] ?? '',
        'alt' => $banner_unfiltered['alt'] ?? '',
        'caption' => $banner_unfiltered['caption'] ?? '',
        'sizes' => [
            'small' => $banner_unfiltered['sizes']['banner-small'] ?? '',
            'large' => $banner_unfiltered['sizes']['banner-large'] ?? '',
        ]
        ];
    return array_merge($acf, [
        'banner_image' => $banner,
    ]);
}

/**
 * Hydrator-funktion för 'tiercards' sektionstypen.
 *
 * Hämtar tier-set inlägget och organiserar dess fältdata.
 * 
 * @param array $acf - befintliga ACF-fält för sektionen.
 * @return array - uppdaterade ACF-fält med organiserad tier data.
 */
function tiercards_hydrator($acf, $page_id)
{
    $tiers = get_posts([
        'post_type'      => 'tier-set',
        'posts_per_page' => -1,
        'post_status'    => 'publish'
    ]);

    $tierData = !empty($tiers) ? get_fields($tiers[0]->ID) : [];
    $organizedTiers = organize_tiers($tierData); // Funktionen finns i section.php.

    return array_merge($acf, [
        'tierset' => $organizedTiers,
    ]);
}

/**
 * Hydrator-funktion för 'text' sektionstypen.
 *
 * Hämtar och organiserar textinnehållet från ACF-fälten.
 * 
 * @param array $acf - befintliga ACF-fält för sektionen.
 * @return array - uppdaterade ACF-fält med organiserat textinnehåll.
 */
function text_hydrator(array $acf, int $pageId): array
{
    $key = $acf['text_key'] ?? null;

    if (!$key) {
        return $acf;
    }

    $content = get_field($key, $pageId);

    return [
        'key'     => $key,
        'content' => apply_filters('the_content', $content),
    ];
}

/**
 * Hydrator-funktion för 'header' sektionstypen.
 *
 * Hämtar sidans titel och lägger till den i ACF-fälten.
 * 
 * @param array $acf - befintliga ACF-fält för sektionen.
 * @return array - uppdaterade ACF-fält med sidans titel.
 */
function header_hydrator($acf, $page_id) {
    $acf['title'] = get_the_title($page_id);
    return $acf;
}

/**
 * Hydrator-funktion för 'support' sektionstypen.
 *
 * Hämtar alla 'support' inlägg och organiserar deras fältdata.
 * 
 * @param array $acf - befintliga ACF-fält för sektionen.
 * @return array - uppdaterade ACF-fält med organiserad support data.
 */
function support_hydrator($acf, $page_id) {
    $support = get_posts([
        'post_type'      => 'support',
        'posts_per_page' => -1,
        'post_status'    => 'publish'
    ]);

    $support_data = [];

    foreach ($support as $post) {
        $supportData = get_post($post->ID);
        $support_data[] = [
            'title' => $supportData->post_title,
            'content' => apply_filters('the_content', $supportData->post_content),
            'image' => [
                'title' => get_field('image', $post->ID)['title'] ?? '',
                'alt' => get_field('image', $post->ID)['alt'] ?? '',
                'caption' => get_field('image', $post->ID)['caption'] ?? '',
                'thumbnail' => get_field('image', $post->ID)['sizes']['thumbnail'] ?? '',
            ]
        ];
    }

    return array_merge($acf, [
        'support' => $support_data
    ]);
}

/**
 * Hydrator-funktion för 'news' sektionstypen.
 *
 * Hämtar alla inlägg av typen 'post' och organiserar deras fältdata.
 * 
 * @param array $acf - befintliga ACF-fält för sektionen.
 * @return array - uppdaterade ACF-fält med organiserad nyhetsdata.
 */
function news_hydrator($acf, $page_id) {
    $news_posts = get_posts([
        'post_type'      => 'post',
        'posts_per_page' => -1,
        'post_status'    => 'publish'
    ]);

    $news_data = [];
    foreach ($news_posts as $post) {
        $newsData = get_post($post->ID);
        $news_data[] = [
            'title' => $newsData->post_title,
            'excerpt' => get_the_excerpt($post->ID),
            'content' => apply_filters('the_content', $newsData->post_content),
            'date' => get_the_date('', $post->ID),
            'thumbnail' => get_the_post_thumbnail_url($post->ID, 'thumbnail'),
        ];
    }
    return array_merge($acf, [
        'news' => $news_data
    ]);
}