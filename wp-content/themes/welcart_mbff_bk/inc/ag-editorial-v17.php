<?php

/**
 * AG FLOLIST / 代表メッセージ + ジャーナル初期設定 (2026-11-07)
 * functions.php から読み込む。管理者が管理画面を開いたときに初回のみ4記事を登録。
 * 商品カテゴリー・Welcart会員情報・注文処理には触れない。
 */
if (!defined('ABSPATH')) {
    exit;
}

function ag17_register_editorial_content()
{
    // CPT UI側ですでに登録されている場合は重複定義しない。
    if (!post_type_exists('ag_journal')) {
        register_post_type('ag_journal', array(
            'label' => 'ジャーナル',
            'labels' => array(
                'name' => 'ジャーナル',
                'singular_name' => 'ジャーナル記事',
                'add_new_item' => 'ジャーナル記事を追加',
                'edit_item' => 'ジャーナル記事を編集',
            ),
            'public' => true,
            'publicly_queryable' => true,
            'show_in_rest' => true,
            'show_ui' => true,
            'menu_icon' => 'dashicons-edit-page',
            'menu_position' => 6,
            'supports' => array('title', 'editor', 'excerpt', 'thumbnail', 'revisions'),
            'has_archive' => false, // /journal/ は既存の固定ページで管理
            'rewrite' => array('slug' => 'journal/article', 'with_front' => false),
            'show_in_nav_menus' => true,
        ));
    }
    if (!taxonomy_exists('ag_journal_topic')) {
        register_taxonomy('ag_journal_topic', array('ag_journal'), array(
            'labels' => array('name' => 'ジャーナルカテゴリー', 'singular_name' => 'ジャーナルカテゴリー'),
            'public' => true,
            'hierarchical' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_rest' => true,
            'rewrite' => array('slug' => 'journal/category', 'with_front' => false),
        ));
    } else {
        register_taxonomy_for_object_type('ag_journal_topic', 'ag_journal');
    }
}
add_action('init', 'ag17_register_editorial_content', 20);
add_action('after_setup_theme', function () {
    add_theme_support('post-thumbnails', array('post', 'ag_journal'));
});

/** 初期投稿用のアイキャッチ写真をWPメディアへ登録（初回だけ）。*/
function ag17_import_editorial_thumbnail($filename, $post_id)
{
    $base = get_template_directory() . '/assets/ag-editorial/';
    $file = $base . basename($filename);
    if (!is_file($file) || !is_readable($file)) return 0;
    $existing = get_posts(array(
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        'fields' => 'ids',
        'meta_key' => '_ag17_seed_asset',
        'meta_value' => basename($filename),
        'posts_per_page' => 1,
    ));
    if ($existing) return (int) $existing[0];
    $data = file_get_contents($file);
    if ($data === false) return 0;
    $upload = wp_upload_bits('ag17-' . basename($filename), null, $data);
    if (!empty($upload['error']) || empty($upload['file'])) return 0;
    $attachment_id = wp_insert_attachment(array(
        'post_mime_type' => 'image/webp',
        'post_title' => sanitize_text_field(pathinfo($filename, PATHINFO_FILENAME)),
        'post_status' => 'inherit',
    ), $upload['file'], $post_id, true);
    if (is_wp_error($attachment_id) || !$attachment_id) return 0;
    require_once ABSPATH . 'wp-admin/includes/image.php';
    $metadata = wp_generate_attachment_metadata($attachment_id, $upload['file']);
    if (is_array($metadata)) wp_update_attachment_metadata($attachment_id, $metadata);
    update_post_meta($attachment_id, '_ag17_seed_asset', basename($filename));
    return (int)$attachment_id;
}

function ag17_seed_editorial_content()
{
    if (!current_user_can('manage_options') || get_option('ag17_editorial_seed_complete')) return;
    if (!post_type_exists('ag_journal') || !taxonomy_exists('ag_journal_topic')) return;

    $topics = array(
        'salon-management' => 'サロン経営',
        'beauty-health' => '美容と健康',
        'products' => '商品を知る',
        'people-work' => '人と仕事',
        'news' => 'お知らせ',
    );
    foreach ($topics as $slug => $name) {
        if (!term_exists($slug, 'ag_journal_topic')) {
            wp_insert_term($name, 'ag_journal_topic', array('slug' => $slug));
        }
    }

    // 既存固定ページがある場合は一切書き換えない。なければ公開済みで用意する。
    foreach (array('message' => '代表メッセージ', 'journal' => 'ジャーナル') as $slug => $title) {
        if (!get_page_by_path($slug, OBJECT, 'page')) {
            wp_insert_post(array('post_type' => 'page', 'post_status' => 'publish', 'post_title' => $title, 'post_name' => $slug));
        }
    }

    // 添付デザインに記載された文章のみ。記事の本文を新規創作しない。
    $seeds = array(
        array(
            'key' => 'feature-salon-sales',
            'title' => '売るのではなく、伝える。これからのサロン店販。',
            'excerpt' => 'お客様との信頼を育てながら、自然に商品が選ばれるサロンの考え方。',
            'term' => 'salon-management',
            'date' => '',
            'image' => 'journal-feature.webp',
            'feature' => true,
        ),
        array(
            'key' => 'staff-product-proposal',
            'title' => 'スタッフ全員で取り組む、商品提案の始め方。',
            'excerpt' => '',
            'term' => 'salon-management',
            'date' => '2026-11-01 09:00:00',
            'image' => 'journal-salon.webp',
            'feature' => false,
        ),
        array(
            'key' => 'inner-beauty',
            'title' => '美しさを内側から支える、インナーケアという選択。',
            'excerpt' => '',
            'term' => 'beauty-health',
            'date' => '2026-10-28 09:00:00',
            'image' => 'journal-inner.webp',
            'feature' => false,
        ),
        array(
            'key' => 'work-life',
            'title' => '美容の仕事を、長く楽しむために。',
            'excerpt' => '',
            'term' => 'people-work',
            'date' => '2026-10-20 09:00:00',
            'image' => 'journal-work.webp',
            'feature' => false,
        ),
    );
    $all_done = true;
    foreach ($seeds as $seed) {
        $found = get_posts(array(
            'post_type' => 'ag_journal',
            'post_status' => 'any',
            'fields' => 'ids',
            'meta_key' => '_ag17_seed_key',
            'meta_value' => $seed['key'],
            'posts_per_page' => 1,
        ));
        if ($found) {
            $post_id = (int)$found[0];
        } else {
            // 同タイトルの既存CPT記事があれば重複作成しない。
            $same_posts = get_posts(array('post_type' => 'ag_journal', 'post_status' => 'any', 'title' => $seed['title'], 'posts_per_page' => 1, 'fields' => 'ids'));
            if ($same_posts) {
                $post_id = (int)$same_posts[0];
            } else {
                $entry = array(
                    'post_type' => 'ag_journal',
                    'post_status' => 'publish',
                    'post_title' => $seed['title'],
                    'post_excerpt' => $seed['excerpt'],
                    'post_content' => $seed['excerpt'] ? '<p>' . esc_html($seed['excerpt']) . '</p>' : '',
                    'post_name' => sanitize_title($seed['key']),
                );
                if ($seed['date']) $entry['post_date'] = $seed['date'];
                $post_id = wp_insert_post($entry, true);
                if (is_wp_error($post_id) || !$post_id) {
                    $all_done = false;
                    continue;
                }
            }
            update_post_meta($post_id, '_ag17_seed_key', $seed['key']);
        }
        if ($seed['feature']) update_post_meta($post_id, '_ag17_featured', '1');
        $term = get_term_by('slug', $seed['term'], 'ag_journal_topic');
        if ($term && !is_wp_error($term) && !has_term('', 'ag_journal_topic', $post_id)) {
            wp_set_object_terms($post_id, array((int)$term->term_id), 'ag_journal_topic');
        }
        if (!has_post_thumbnail($post_id)) {
            $attachment = ag17_import_editorial_thumbnail($seed['image'], $post_id);
            if ($attachment) set_post_thumbnail($post_id, $attachment);
            else $all_done = false;
        }
    }
    if ($all_done) {
        update_option('ag17_editorial_seed_complete', '1', false);
        // 編集者の管理画面への初回アクセス時にURL書き換え規則を更新。
        flush_rewrite_rules(false);
    }
}
add_action('admin_init', 'ag17_seed_editorial_content', 30);
