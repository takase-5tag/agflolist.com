<?php
/*
Plugin Name: SCF / DearFlip CSV Import Export
Description: 投稿タイプを選択してCSVエクスポート・インポート。SCF/カスタムフィールド自動取得、タクソノミー紐づけ、DearFlip(dflip)専用インポート、books⇔DearFlip自動連携対応。v1.7ではインポート前プレビュー、既存投稿の上書き/スキップ/新規作成選択に対応。
Version: 1.8.7
Author: 5tag
*/

if (!defined('ABSPATH')) exit;

class SCF_DFlip_CSV_Import_Export
{

    const PAGE_SLUG = 'scf-dflip-csv-import-export';

    public function __construct()
    {
        add_action('admin_menu', [$this, 'admin_menu']);
        add_action('admin_post_scf_dflip_csv_export', [$this, 'export_csv']);
        add_action('admin_post_scf_dflip_csv_import', [$this, 'import_csv']);
        add_filter('get_post_metadata', [$this, 'maybe_return_media_url_on_front'], 10, 5);
    }

    public function admin_menu()
    {
        add_management_page(
            'SCF / DearFlip CSV',
            'SCF / DearFlip CSV',
            'manage_options',
            self::PAGE_SLUG,
            [$this, 'page']
        );
    }

    public function page()
    {
        if (!current_user_can('manage_options')) return;

        $selected_post_type = isset($_GET['selected_post_type']) ? sanitize_key($_GET['selected_post_type']) : 'books';
        $post_types = $this->get_public_post_types();
        $meta_keys = $selected_post_type ? $this->get_meta_keys_by_post_type($selected_post_type) : [];
        $taxonomies = $selected_post_type ? $this->get_taxonomies_by_post_type($selected_post_type) : [];
?>
        <div class="wrap">
            <h1>SCF / DearFlip CSV Import Export</h1>

            <?php if (!empty($_GET['imported'])) : ?>
                <div class="notice notice-success">
                    <p>インポートが完了しました。</p>
                    <?php if (!empty($_GET['result_key'])) :
                        $result = get_transient(sanitize_text_field($_GET['result_key']));
                        if (is_array($result)) : ?>
                            <p>新規作成：<?php echo intval($result['created'] ?? 0); ?>件 / 更新：<?php echo intval($result['updated'] ?? 0); ?>件 / スキップ：<?php echo intval($result['skipped'] ?? 0); ?>件 / エラー：<?php echo intval($result['errors'] ?? 0); ?>件</p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <h2>1. 投稿タイプ選択</h2>
            <form method="get" action="">
                <input type="hidden" name="page" value="<?php echo esc_attr(self::PAGE_SLUG); ?>">
                <p>
                    <select name="selected_post_type">
                        <?php foreach ($post_types as $type => $obj) : ?>
                            <option value="<?php echo esc_attr($type); ?>" <?php selected($selected_post_type, $type); ?>>
                                <?php echo esc_html($obj->label . '（' . $type . '）'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button class="button">この投稿タイプの項目を表示</button>
                </p>
            </form>

            <hr>

            <h2>2. エクスポート</h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('scf_dflip_csv_export'); ?>
                <input type="hidden" name="action" value="scf_dflip_csv_export">
                <input type="hidden" name="post_type" value="<?php echo esc_attr($selected_post_type); ?>">

                <p><strong>対象投稿タイプ：</strong><?php echo esc_html($selected_post_type); ?></p>

                <?php if ($selected_post_type !== 'dflip') : ?>
                    <div style="background:#fff;border:1px solid #ccd0d4;padding:12px;margin:15px 0;max-width:700px;">
                        <strong>アイキャッチ画像</strong><br>
                        <code>featured_image</code> 列として自動でCSVに出力します。インポート時も自動でアイキャッチに設定されます。
                    </div>
                <?php endif; ?>

                <p>
                    <label><input type="radio" name="export_type" value="all" checked> 全てをエクスポート</label><br>
                    <label><input type="radio" name="export_type" value="ids"> 投稿IDを指定してエクスポート</label>
                </p>

                <p>
                    <label>対象投稿ID（カンマ区切り）</label><br>
                    <input type="text" name="post_ids" style="width:400px;" placeholder="例：12,15,30">
                </p>

                <?php if ($selected_post_type === 'dflip') : ?>
                    <div style="background:#fff;border:1px solid #ccd0d4;padding:12px;margin:15px 0;">
                        <strong>DearFlip専用モード</strong><br>
                        <code>_dflip_data</code> から <code>pdf_source</code> と <code>pdf_thumb</code> を取り出してCSVに出力します。
                    </div>
                    <input type="hidden" name="meta_keys[]" value="_dflip_pdf_source">
                <?php else : ?>
                    <h3>出力するカスタムフィールド</h3>
                    <?php if (!empty($meta_keys)) : ?>
                        <p><label><input type="checkbox" id="check_all_meta" checked> カスタムフィールドをすべて選択</label></p>
                        <div style="background:#fff;border:1px solid #ccd0d4;padding:12px;max-width:700px;">
                            <?php foreach ($meta_keys as $key) : ?>
                                <label style="display:block;margin-bottom:6px;">
                                    <input type="checkbox" class="meta_key_check" name="meta_keys[]" value="<?php echo esc_attr($key); ?>" checked>
                                    <?php echo esc_html($key); ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <p>この投稿タイプには出力候補のカスタムフィールドが見つかりませんでした。</p>
                    <?php endif; ?>

                    <h3>出力するカテゴリー / タクソノミー</h3>
                    <?php if (!empty($taxonomies)) : ?>
                        <p><label><input type="checkbox" id="check_all_tax" checked> タクソノミーをすべて選択</label></p>
                        <div style="background:#fff;border:1px solid #ccd0d4;padding:12px;max-width:700px;">
                            <?php foreach ($taxonomies as $tax => $obj) : ?>
                                <label style="display:block;margin-bottom:6px;">
                                    <input type="checkbox" class="tax_check" name="taxonomies[]" value="<?php echo esc_attr($tax); ?>" checked>
                                    <?php echo esc_html($obj->label . '（tax_' . $tax . '）'); ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <p>この投稿タイプに紐づくタクソノミーは見つかりませんでした。</p>
                    <?php endif; ?>
                <?php endif; ?>

                <p><button class="button button-primary">CSVエクスポート</button></p>
            </form>

            <hr>

            <h2>3. インポート</h2>
            <form method="post" enctype="multipart/form-data" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('scf_dflip_csv_import'); ?>
                <input type="hidden" name="action" value="scf_dflip_csv_import">

                <p>
                    <label>インポート先投稿タイプ</label><br>
                    <select name="post_type">
                        <?php foreach ($post_types as $type => $obj) : ?>
                            <option value="<?php echo esc_attr($type); ?>" <?php selected($selected_post_type, $type); ?>>
                                <?php echo esc_html($obj->label . '（' . $type . '）'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </p>

                <p>
                    <label><input type="checkbox" name="import_as_dflip" value="1" <?php checked($selected_post_type, 'dflip'); ?>> DearFlip形式として取り込む</label><br>
                    <small>CSVの <code>prints</code> または <code>pdf_source</code> をDearFlipのPDFに設定します。</small>
                </p>

                <div style="background:#fff;border:1px solid #ccd0d4;padding:12px;max-width:850px;margin:15px 0;">
                    <strong>books ⇔ DearFlip 自動連携</strong>
                    <p style="margin-bottom:8px;">
                        <label>
                            <input type="checkbox" name="pair_books_dflip" value="1" <?php checked($selected_post_type, 'books'); ?>>
                            books投稿も作成/更新し、<code>digital_chord</code> に新しいDearFlip IDを自動保存する
                        </label>
                    </p>
                    <small>
                        <code>books</code> のCSVを本番へ入れる場合はこちらをONにしてください。<br>
                        <strong>digital_img / prints を含むbooks CSVのときだけ有効</strong>です。dflip CSVを選んだ場合はbooksを作らずDearFlipのみ取り込みます。<br>
                        <code>prints</code> からDearFlip投稿を作成し、作成されたIDで <code>[dflip id=&quot;新ID&quot;]</code> を <code>digital_chord</code> に保存します。
                    </small>
                </div>

                <p>
                    <label>URL置換（デモURL → 本番URLなど）</label><br>
                    <input type="text" name="replace_from" style="width:360px;" placeholder="例：https://demo.5tag.jp/odp.infos">
                    →
                    <input type="text" name="replace_to" style="width:360px;" placeholder="例：https://odp.info">
                </p>

                <div style="background:#fff;border:1px solid #ccd0d4;padding:12px;max-width:850px;margin:15px 0;">
                    <strong>画像/PDF/ファイルURLの保存形式</strong>
                    <p style="margin-bottom:8px;">
                        <label>
                            <input type="radio" name="media_save_mode" value="url" checked>
                            URLのまま保存する（推奨）
                        </label><br>
                        <small><code>get_field('digital_img')</code> や <code>get_field('prints')</code> をそのままURLとして出力するテンプレート向けです。</small>
                    </p>
                    <p style="margin-bottom:8px;">
                        <label>
                            <input type="radio" name="media_save_mode" value="copy_url">
                            メディアライブラリへコピーして、新しいURLを保存する
                        </label><br>
                        <small>デモ環境のファイルを本番へ取り込みたい場合に使用します。テンプレート修正は不要です。</small>
                    </p>
                    <p style="margin-bottom:8px;">
                        <label>
                            <input type="radio" name="media_save_mode" value="id">
                            メディアライブラリへコピーして、添付IDを保存する
                        </label><br>
                        <small>SCF側を「ID保存」で運用している場合のみ使用してください。テンプレート側で <code>wp_get_attachment_url()</code> が必要になる場合があります。</small>
                    </p>
                    <p style="margin-bottom:0;">
                        <label>
                            <input type="radio" name="media_save_mode" value="hybrid">
                            メディアライブラリへコピーして、管理画面は選択済み・表側はURLで返す（おすすめ）
                        </label><br>
                        <small>SCF管理画面では画像/ファイルが選択済みに見えます。テンプレート側の <code>get_field('digital_img')</code> / <code>get_field('prints')</code> はURLとして扱えます。</small>
                    </p>
                </div>

                <div style="background:#fff;border:1px solid #ccd0d4;padding:12px;max-width:850px;margin:15px 0;">
                    <strong>既存投稿がある場合の処理</strong>
                    <p style="margin-bottom:8px;">
                        <label><input type="radio" name="existing_post_mode" value="preview" checked> インポート前にプレビューして選択する（おすすめ）</label><br>
                        <label><input type="radio" name="existing_post_mode" value="update"> 既存投稿を上書き更新する</label><br>
                        <label><input type="radio" name="existing_post_mode" value="skip"> 既存投稿はスキップする</label><br>
                        <label><input type="radio" name="existing_post_mode" value="create"> 既存投稿があっても新規作成する（スラッグに -2 などを付与）</label>
                    </p>
                    <small>同じスラッグの投稿が本番にある場合、プレビュー画面で「更新 / スキップ / 新規作成」を1件ずつ選べます。</small>
                </div>

                <p><input type="file" name="csv_file" accept=".csv" required></p>

                <p>
                    <button class="button button-primary" name="preview_import" value="1">インポート前プレビュー</button>
                    <button class="button" name="run_import_direct" value="1">プレビューせず実行</button>
                </p>
            </form>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const allMeta = document.getElementById('check_all_meta');
                if (allMeta) {
                    allMeta.addEventListener('change', function() {
                        document.querySelectorAll('.meta_key_check').forEach(function(el) {
                            el.checked = allMeta.checked;
                        });
                    });
                }
                const allTax = document.getElementById('check_all_tax');
                if (allTax) {
                    allTax.addEventListener('change', function() {
                        document.querySelectorAll('.tax_check').forEach(function(el) {
                            el.checked = allTax.checked;
                        });
                    });
                }
            });
        </script>
    <?php
    }

    private function get_public_post_types()
    {
        $post_types = get_post_types(['show_ui' => true], 'objects');
        if (!isset($post_types['dflip'])) {
            $post_types['dflip'] = (object)[
                'name' => 'dflip',
                'label' => 'DearFlip Books'
            ];
        }
        return $post_types;
    }

    private function get_taxonomies_by_post_type($post_type)
    {
        $taxonomies = get_object_taxonomies($post_type, 'objects');
        unset($taxonomies['post_format']);
        return $taxonomies;
    }

    private function get_meta_keys_by_post_type($post_type)
    {
        global $wpdb;

        $exclude = [
            '_edit_lock',
            '_edit_last',
            '_thumbnail_id',
            '_wp_old_slug',
            '_wp_page_template',
            '_wp_trash_meta_status',
            '_wp_trash_meta_time',
            '_dflip_data'
        ];

        $keys = $wpdb->get_col($wpdb->prepare(" 
            SELECT DISTINCT pm.meta_key
            FROM {$wpdb->postmeta} pm
            INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
            WHERE p.post_type = %s
            ORDER BY pm.meta_key ASC
        ", $post_type));

        $keys = array_filter($keys, function ($key) use ($exclude) {
            if (in_array($key, $exclude, true)) return false;
            if (strpos($key, '_') === 0) return false;
            return true;
        });

        return array_values($keys);
    }

    public function export_csv()
    {
        if (!current_user_can('manage_options')) wp_die('権限がありません。');
        check_admin_referer('scf_dflip_csv_export');

        $post_type = sanitize_key($_POST['post_type'] ?? 'post');
        $export_type = sanitize_text_field($_POST['export_type'] ?? 'all');
        $meta_keys = isset($_POST['meta_keys']) ? array_map('sanitize_text_field', (array)$_POST['meta_keys']) : [];
        $taxonomies = isset($_POST['taxonomies']) ? array_map('sanitize_key', (array)$_POST['taxonomies']) : [];

        // books移行では digital_chord は本番側の新しいDearFlip IDで自動生成するため、
        // CSVへ出力しません。古いID混入とCSV列崩れを防ぎます。
        if ($post_type === 'books') {
            $meta_keys = array_values(array_filter($meta_keys, function ($key) {
                return $key !== 'digital_chord' && $key !== 'dflip_id';
            }));
        }

        $args = [
            'post_type'      => $post_type,
            'post_status'    => 'any',
            'posts_per_page' => -1,
            'orderby'        => 'ID',
            'order'          => 'ASC',
        ];

        if ($export_type === 'ids' && !empty($_POST['post_ids'])) {
            $ids = array_filter(array_map('intval', explode(',', sanitize_text_field($_POST['post_ids']))));
            if ($ids) {
                $args['post__in'] = $ids;
                $args['orderby'] = 'post__in';
            }
        }

        $posts = get_posts($args);

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename=' . $post_type . '-export-' . date('Ymd-His') . '.csv');

        $fp = fopen('php://output', 'w');
        echo "\xEF\xBB\xBF";

        if ($post_type === 'dflip') {
            $header = ['post_id', 'post_title', 'post_name', 'post_status', 'menu_order', 'post_content', 'pdf_source', 'pdf_thumb'];
        } else {
            $tax_headers = array_map(function ($tax) {
                return 'tax_' . $tax;
            }, $taxonomies);
            $header = array_merge(['post_id', 'post_title', 'post_name', 'post_status', 'menu_order', 'post_content', 'featured_image'], $tax_headers, $meta_keys);
        }

        fputcsv($fp, $header);

        foreach ($posts as $post) {
            if ($post_type === 'dflip') {
                $dflip = get_post_meta($post->ID, '_dflip_data', true);
                $pdf_source = is_array($dflip) && !empty($dflip['pdf_source']) ? $dflip['pdf_source'] : '';
                $pdf_thumb = is_array($dflip) && !empty($dflip['pdf_thumb']) ? $dflip['pdf_thumb'] : '';

                fputcsv($fp, [
                    $post->ID,
                    $post->post_title,
                    $post->post_name,
                    $post->post_status,
                    intval($post->menu_order),
                    $post->post_content,
                    $this->normalize_export_value($pdf_source),
                    $this->normalize_export_value($pdf_thumb),
                ]);
                continue;
            }

            $row = [
                $post->ID,
                $post->post_title,
                $post->post_name,
                $post->post_status,
                intval($post->menu_order),
                $post->post_content,
                $this->get_featured_image_for_export($post->ID),
            ];

            foreach ($taxonomies as $tax) {
                $row[] = $this->get_terms_for_export($post->ID, $tax);
            }

            foreach ($meta_keys as $key) {
                $value = $this->get_meta_value_for_export($post->ID, $key);
                $row[] = $this->normalize_export_value($value);
            }

            fputcsv($fp, $row);
        }

        fclose($fp);
        exit;
    }

    private function get_featured_image_for_export($post_id)
    {
        $thumbnail_id = get_post_thumbnail_id($post_id);
        if (!$thumbnail_id) return '';

        $url = wp_get_attachment_url($thumbnail_id);
        return $url ? $url : '';
    }

    private function get_terms_for_export($post_id, $taxonomy)
    {
        $terms = get_the_terms($post_id, $taxonomy);
        if (empty($terms) || is_wp_error($terms)) return '';

        $names = [];
        foreach ($terms as $term) {
            $names[] = $term->name;
        }
        return implode('|', $names);
    }

    private function get_meta_value_for_export($post_id, $key)
    {
        // SCFの繰り返しフィールドは同じmeta_keyで複数行保存されます。
        // get_post_meta(..., true) だと1件目だけになるため、CSV出力時は全件取得します。
        $all_values = get_post_meta($post_id, $key, false);

        if (count($all_values) <= 1) {
            return get_post_meta($post_id, $key, true);
        }

        $repeat_multiple_data = $this->get_scf_repeat_multiple_data($post_id);
        if (!empty($repeat_multiple_data[$key]) && is_array($repeat_multiple_data[$key])) {
            $grouped = [];
            $offset = 0;
            foreach ($repeat_multiple_data[$key] as $count) {
                $count = max(0, intval($count));
                $grouped[] = array_slice($all_values, $offset, $count);
                $offset += $count;
            }
            return $grouped;
        }

        return $all_values;
    }

    private function get_scf_repeat_multiple_data($post_id)
    {
        $prefix = class_exists('SCF_Config') && defined('SCF_Config::PREFIX') ? SCF_Config::PREFIX : '_smart-cf-';
        $data = get_post_meta($post_id, $prefix . 'repeat-multiple-data', true);
        return is_array($data) ? $data : [];
    }

    private function update_scf_repeat_multiple_data($post_id, $key, $counts)
    {
        $prefix = class_exists('SCF_Config') && defined('SCF_Config::PREFIX') ? SCF_Config::PREFIX : '_smart-cf-';
        $meta_key = $prefix . 'repeat-multiple-data';
        $data = get_post_meta($post_id, $meta_key, true);
        if (!is_array($data)) $data = [];

        if (empty($counts)) {
            unset($data[$key]);
        } else {
            $data[$key] = array_map('intval', $counts);
        }

        if ($data) {
            update_post_meta($post_id, $meta_key, $data);
        } else {
            delete_post_meta($post_id, $meta_key);
        }
    }

    private function normalize_export_value($value)
    {
        // SCFの画像/ファイル系は添付IDで保存されていることが多いため、
        // CSV出力時はURLへ変換します。配列・繰り返しフィールド内のIDも再帰的に変換します。
        if (is_numeric($value)) {
            $url = wp_get_attachment_url((int)$value);
            if ($url) return $url;
        }

        if (is_array($value)) {
            $converted = $this->normalize_export_array_recursive($value);
            return wp_json_encode($converted, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return $value;
    }

    private function normalize_export_array_recursive($value)
    {
        if (is_array($value)) {
            $converted = [];
            foreach ($value as $k => $v) {
                $converted[$k] = $this->normalize_export_array_recursive($v);
            }
            return $converted;
        }

        if (is_numeric($value)) {
            $url = wp_get_attachment_url((int)$value);
            if ($url) return $url;
        }

        return $value;
    }

    public function import_csv()
    {
        if (!current_user_can('manage_options')) wp_die('権限がありません。');
        check_admin_referer('scf_dflip_csv_import');

        if (!empty($_POST['scf_dflip_preview_token'])) {
            $token = sanitize_text_field($_POST['scf_dflip_preview_token']);
            $payload = get_transient('scf_dflip_csv_preview_' . $token);
            if (empty($payload) || empty($payload['rows']) || empty($payload['settings'])) {
                wp_die('プレビューデータの有効期限が切れました。もう一度CSVを選択してください。');
            }

            $row_actions = isset($_POST['row_action']) ? array_map('sanitize_text_field', (array)$_POST['row_action']) : [];
            $result = $this->process_import_rows($payload['rows'], $payload['settings'], $row_actions);
            delete_transient('scf_dflip_csv_preview_' . $token);

            $notice_key = 'scf_dflip_csv_result_' . wp_generate_uuid4();
            set_transient($notice_key, $result, 10 * MINUTE_IN_SECONDS);
            wp_redirect(admin_url('tools.php?page=' . self::PAGE_SLUG . '&selected_post_type=' . $payload['settings']['selected_post_type'] . '&imported=1&result_key=' . rawurlencode($notice_key)));
            exit;
        }

        if (empty($_FILES['csv_file']['tmp_name'])) wp_die('CSVファイルがありません。');

        $settings = $this->build_import_settings_from_request();
        $rows = $this->read_csv_rows($_FILES['csv_file']['tmp_name'], $settings);

        if (empty($rows)) {
            wp_die('インポートできる行がありません。CSVの列数やタイトル列を確認してください。');
        }

        if ($settings['existing_post_mode'] === 'preview' && empty($_POST['run_import_direct'])) {
            $this->render_import_preview($rows, $settings);
            exit;
        }

        $result = $this->process_import_rows($rows, $settings, []);
        $notice_key = 'scf_dflip_csv_result_' . wp_generate_uuid4();
        set_transient($notice_key, $result, 10 * MINUTE_IN_SECONDS);

        wp_redirect(admin_url('tools.php?page=' . self::PAGE_SLUG . '&selected_post_type=' . $settings['selected_post_type'] . '&imported=1&result_key=' . rawurlencode($notice_key)));
        exit;
    }

    private function build_import_settings_from_request()
    {
        $post_type = sanitize_key($_POST['post_type'] ?? 'post');
        $media_save_mode = sanitize_text_field($_POST['media_save_mode'] ?? 'url');
        if (!in_array($media_save_mode, ['url', 'copy_url', 'id', 'hybrid'], true)) {
            $media_save_mode = 'url';
        }

        $existing_post_mode = sanitize_text_field($_POST['existing_post_mode'] ?? 'preview');
        if (!in_array($existing_post_mode, ['preview', 'update', 'skip', 'create'], true)) {
            $existing_post_mode = 'preview';
        }

        return [
            'post_type' => $post_type,
            'import_as_dflip' => !empty($_POST['import_as_dflip']) || $post_type === 'dflip',
            'replace_from' => trim(wp_unslash($_POST['replace_from'] ?? '')),
            'replace_to' => trim(wp_unslash($_POST['replace_to'] ?? '')),
            'media_save_mode' => $media_save_mode,
            'pair_books_dflip_requested' => !empty($_POST['pair_books_dflip']),
            'existing_post_mode' => $existing_post_mode,
            'selected_post_type' => $post_type,
        ];
    }

    private function read_csv_rows($file_path, $settings)
    {
        $content = file_get_contents($file_path);

        if ($content === false || $content === '') {
            wp_die('CSVファイルを読み込めません。');
        }

        // 文字コード対策：Excel保存CSV / TSV / UTF-16 / Shift-JIS(CP932) をUTF-8へ寄せます。
        if (substr($content, 0, 2) === "\xFF\xFE") {
            $content = mb_convert_encoding(substr($content, 2), 'UTF-8', 'UTF-16LE');
        } elseif (substr($content, 0, 2) === "\xFE\xFF") {
            $content = mb_convert_encoding(substr($content, 2), 'UTF-8', 'UTF-16BE');
        } else {
            $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);

            if (function_exists('mb_detect_encoding')) {
                $detected = mb_detect_encoding($content, ['UTF-8', 'SJIS-win', 'CP932', 'EUC-JP', 'ISO-2022-JP'], true);
                if ($detected && strtoupper($detected) !== 'UTF-8') {
                    $content = mb_convert_encoding($content, 'UTF-8', $detected);
                }
            }
        }

        // NULLバイト・不可視BOMを除去し、改行コードを統一
        $content = str_replace("\0", '', $content);
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        $content = str_replace(["\r\n", "\r"], "\n", $content);

        $lines = explode("\n", $content);

        // 先頭の空行やExcel由来の空白行を飛ばして、実ヘッダー行を探す
        $header_line = '';
        $header_line_index = -1;
        foreach ($lines as $i => $line) {
            $line = preg_replace('/^\xEF\xBB\xBF/', '', (string)$line);
            $line = trim($line);

            // 空行、カンマだけ、タブだけの行はヘッダー扱いしない
            if ($line === '') continue;
            if (trim($line, ",\t; \xC2\xA0") === '') continue;

            $header_line = $line;
            $header_line_index = $i;
            break;
        }

        if ($header_line_index < 0 || $header_line === '') {
            wp_die('CSVヘッダーがありません。ファイルの先頭行が空欄になっていないか確認してください。');
        }

        // 区切り文字を自動判定（CSV / TSV / セミコロン区切り）
        $delimiter_candidates = [
            ','  => substr_count($header_line, ','),
            "\t" => substr_count($header_line, "\t"),
            ';'  => substr_count($header_line, ';'),
        ];
        arsort($delimiter_candidates);
        $delimiter = array_key_first($delimiter_candidates);
        if (max($delimiter_candidates) === 0) {
            $delimiter = ',';
        }

        $header = str_getcsv($header_line, $delimiter);
        $header = array_map(function ($value) {
            $value = $this->clean_header((string)$value);
            $value = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $value);
            $value = str_replace("\0", '', $value);
            return trim($value);
        }, $header);

        // 末尾の空ヘッダーだけ削除
        while (!empty($header) && trim((string)end($header)) === '') {
            array_pop($header);
        }

        if (empty($header) || count(array_filter($header, function ($v) { return trim((string)$v) !== ''; })) === 0) {
            wp_die(
                'CSVのヘッダーを確認できませんでした。<br><br>' .
                '<strong>読み取った先頭行：</strong><br><code>' . esc_html($header_line) . '</code><br><br>' .
                'Excelの場合は「CSV UTF-8（コンマ区切り）」で保存し直してからお試しください。'
            );
        }

        // post_title が全角・大文字違い・空白混入で見つからない場合に備えて正規化
        $normalized_header = [];
        foreach ($header as $h) {
            $key = trim((string)$h);
            $key = str_replace(['　', ' '], '', $key);
            $lower = strtolower($key);

            if (in_array($lower, ['posttitle', 'title', 'タイトル', '投稿タイトル'], true)) {
                $normalized_header[] = 'post_title';
            } elseif (in_array($lower, ['postname', 'slug', 'スラッグ'], true)) {
                $normalized_header[] = 'post_name';
            } elseif (in_array($lower, ['postid', 'id', '投稿id'], true)) {
                $normalized_header[] = 'post_id';
            } else {
                $normalized_header[] = $h;
            }
        }
        $header = $normalized_header;

        if (!in_array('post_title', $header, true)) {
            wp_die(
                'CSVに post_title 列がありません。<br><br>' .
                '検出した列：<code>' . esc_html(implode(' / ', $header)) . '</code><br><br>' .
                'エクスポートしたCSVをそのまま使う場合は、1行目に <code>post_title</code> が含まれているか確認してください。'
            );
        }

        $is_books_csv = $this->is_books_csv_header($header);
        $settings['is_books_csv'] = $is_books_csv;
        $settings['pair_books_dflip'] = !empty($settings['pair_books_dflip_requested']) && $is_books_csv && post_type_exists('books');
        $settings['selected_post_type'] = $settings['pair_books_dflip'] ? 'books' : $settings['post_type'];

        $rows = [];
        $skipped_rows = [];

        for ($i = $header_line_index + 1; $i < count($lines); $i++) {
            $line_number = $i + 1;
            $line = (string)$lines[$i];

            if (trim($line) === '') continue;
            if (trim($line, ",\t; \xC2\xA0") === '') continue;

            $row = str_getcsv($line, $delimiter);

            // 完全な空行は無視
            if (count($row) === 1 && trim((string)$row[0]) === '') {
                continue;
            }

            // 末尾の余分な空列を削除
            while (count($row) > count($header) && trim((string)end($row)) === '') {
                array_pop($row);
            }

            // 列が不足している場合は空文字で補完
            if (count($row) < count($header)) {
                $row = array_pad($row, count($header), '');
            }

            // それでも列が多い場合は、余った分を最後の列へ結合して救済
            if (count($row) > count($header)) {
                $head = array_slice($row, 0, count($header) - 1);
                $tail = array_slice($row, count($header) - 1);
                $row = array_merge($head, [implode($delimiter, $tail)]);
                $skipped_rows[] = $line_number . '行目（列数が多かったため、最後の列へ結合して取り込み）';
            }

            $data = array_combine($header, $row);
            if ($data === false) {
                $skipped_rows[] = $line_number . '行目（列の組み合わせに失敗）';
                continue;
            }

            $data = $this->replace_urls_in_data($data, $settings['replace_from'], $settings['replace_to'], $settings['media_save_mode']);

            $title = isset($data['post_title']) ? trim((string)$data['post_title']) : '';
            if ($title === '') {
                $skipped_rows[] = $line_number . '行目（post_titleが空欄）';
                continue;
            }

            $rows[] = $data;
        }

        $this->last_import_settings = $settings;

        if (empty($rows)) {
            $message =
                'インポートできる行がありません。<br><br>' .
                '<strong>検出した区切り文字：</strong> ' . esc_html($delimiter === "\t" ? 'タブ' : $delimiter) . '<br>' .
                '<strong>ヘッダー行：</strong> ' . intval($header_line_index + 1) . '行目<br>' .
                '<strong>ヘッダー列数：</strong> ' . intval(count($header)) . '<br>' .
                '<strong>検出した列：</strong><br><code>' . esc_html(implode(' / ', $header)) . '</code>';

            if (!empty($skipped_rows)) {
                $message .= '<br><br><strong>確認内容：</strong><br><pre style="white-space:pre-wrap;">' . esc_html(implode("\n", array_slice($skipped_rows, 0, 30))) . '</pre>';
            }

            wp_die($message);
        }

        return $rows;
    }

    private function render_import_preview($rows, $settings)
    {
        if (!empty($this->last_import_settings)) {
            $settings = $this->last_import_settings;
        }

        $token = wp_generate_uuid4();
        set_transient('scf_dflip_csv_preview_' . $token, [
            'rows' => $rows,
            'settings' => $settings,
        ], HOUR_IN_SECONDS);

        $post_types = $this->get_public_post_types();
    ?>
        <div class="wrap">
            <h1>インポート前プレビュー</h1>
            <p>同じスラッグの投稿がある場合は、各行ごとに処理を選択できます。</p>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('scf_dflip_csv_import'); ?>
                <input type="hidden" name="action" value="scf_dflip_csv_import">
                <input type="hidden" name="scf_dflip_preview_token" value="<?php echo esc_attr($token); ?>">

                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th style="width:50px;">#</th>
                            <th>タイトル</th>
                            <th>スラッグ</th>
                            <th>判定</th>
                            <th style="width:220px;">処理</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $i => $data) :
                            $title = sanitize_text_field($data['post_title'] ?? '');
                            $slug = sanitize_title($data['post_name'] ?? $title);
                            $target_post_type = !empty($settings['pair_books_dflip']) ? 'books' : $settings['post_type'];
                            $existing_id = $this->find_existing_post($target_post_type, $slug, 0);
                            $status_text = $existing_id ? '既存投稿あり（ID: ' . intval($existing_id) . '）' : '新規作成';
                            $default_action = $existing_id ? 'update' : 'create';
                        ?>
                            <tr>
                                <td><?php echo intval($i + 1); ?></td>
                                <td><?php echo esc_html($title); ?></td>
                                <td><code><?php echo esc_html($slug); ?></code></td>
                                <td><?php echo esc_html($status_text); ?></td>
                                <td>
                                    <?php if ($existing_id) : ?>
                                        <select name="row_action[<?php echo intval($i); ?>]">
                                            <option value="update" <?php selected($default_action, 'update'); ?>>更新する</option>
                                            <option value="skip">スキップする</option>
                                            <option value="create">新規作成する</option>
                                        </select>
                                    <?php else : ?>
                                        <input type="hidden" name="row_action[<?php echo intval($i); ?>]" value="create">
                                        新規作成
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <p style="margin-top:16px;">
                    <button class="button button-primary">この内容でインポート実行</button>
                    <a class="button" href="<?php echo esc_url(admin_url('tools.php?page=' . self::PAGE_SLUG . '&selected_post_type=' . $settings['selected_post_type'])); ?>">戻る</a>
                </p>
            </form>
        </div>
<?php
    }

    private function process_import_rows($rows, $settings, $row_actions = [])
    {
        $post_type = sanitize_key($settings['post_type'] ?? 'post');
        $import_as_dflip = !empty($settings['import_as_dflip']) || $post_type === 'dflip';
        $media_save_mode = $settings['media_save_mode'] ?? 'url';
        $pair_books_dflip = !empty($settings['pair_books_dflip']);
        $existing_post_mode = $settings['existing_post_mode'] ?? 'update';

        $result = [
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => 0,
        ];

        foreach ($rows as $index => $data) {
            $title = sanitize_text_field($data['post_title'] ?? '');
            if ($title === '') {
                $result['skipped']++;
                continue;
            }

            $slug = sanitize_title($data['post_name'] ?? $title);
            $status = sanitize_text_field($data['post_status'] ?? 'publish');
            $old_post_id = !empty($data['post_id']) ? intval($data['post_id']) : 0;
            $menu_order = $this->get_menu_order_for_import($data, $index);
            $row_action = $row_actions[$index] ?? $existing_post_mode;
            if ($row_action === 'preview') $row_action = 'update';
            if (!in_array($row_action, ['update', 'skip', 'create'], true)) $row_action = 'update';

            if ($pair_books_dflip) {
                $books_post_type = 'books';
                $books_existing_id = $this->find_existing_post($books_post_type, $slug, 0);

                if ($books_existing_id && $row_action === 'skip') {
                    $result['skipped']++;
                    continue;
                }

                $books_slug = ($books_existing_id && $row_action === 'create') ? $this->make_unique_slug($slug, $books_post_type) : $slug;
                $books_postarr = [
                    'post_type'    => $books_post_type,
                    'post_title'   => $title,
                    'post_name'    => $books_slug,
                    'post_status'  => $status ?: 'publish',
                    'menu_order'   => $menu_order,
                    'post_content' => wp_kses_post($data['post_content'] ?? ''),
                ];

                if ($books_existing_id && $row_action === 'update') {
                    $books_postarr['ID'] = $books_existing_id;
                    $books_post_id = wp_update_post($books_postarr, true);
                    $result['updated']++;
                } else {
                    $books_post_id = wp_insert_post($books_postarr, true);
                    $result['created']++;
                }

                if (is_wp_error($books_post_id) || !$books_post_id) {
                    $result['errors']++;
                    continue;
                }

                $pdf_url = $data['pdf_source'] ?? '';
                if (!$pdf_url && !empty($data['prints'])) $pdf_url = $data['prints'];

                $thumb_url = '';
                if (!empty($data['digital_img'])) $thumb_url = $data['digital_img'];
                if (!empty($data['pdf_thumb'])) $thumb_url = $data['pdf_thumb'];

                $dflip_slug = $books_slug;
                $dflip_existing_id = $this->find_existing_post('dflip', $dflip_slug, 0);
                if ($dflip_existing_id && $row_action === 'create') {
                    $dflip_slug = $this->make_unique_slug($dflip_slug, 'dflip');
                    $dflip_existing_id = 0;
                }

                $dflip_postarr = [
                    'post_type'    => 'dflip',
                    'post_title'   => $title,
                    'post_name'    => $dflip_slug,
                    'post_status'  => $status ?: 'publish',
                    'menu_order'   => $menu_order,
                    'post_content' => '',
                ];

                if ($dflip_existing_id && $row_action === 'update') {
                    $dflip_postarr['ID'] = $dflip_existing_id;
                    $dflip_post_id = wp_update_post($dflip_postarr, true);
                } else {
                    $dflip_post_id = wp_insert_post($dflip_postarr, true);
                }

                if (is_wp_error($dflip_post_id) || !$dflip_post_id) {
                    $result['errors']++;
                    continue;
                }

                if ($media_save_mode === 'copy_url' || $media_save_mode === 'hybrid') {
                    $pdf_url = $this->copy_media_and_get_url($pdf_url, $dflip_post_id);
                    $thumb_url = $this->copy_media_and_get_url($thumb_url, $dflip_post_id);
                    if (!empty($data['prints'])) $data['prints'] = $pdf_url;
                    if (!empty($data['digital_img'])) $data['digital_img'] = $thumb_url;
                }

                if (!empty($pdf_url)) {
                    $this->save_dflip_data($dflip_post_id, $title, $dflip_slug, $pdf_url, $thumb_url, $status ?: 'publish');
                }

                $this->save_taxonomies_from_csv($books_post_id, $data);
                $this->save_featured_image_from_csv($books_post_id, $data, $media_save_mode);
                $this->clear_product_gallery_meta_before_import($books_post_id, $data);

                foreach ($data as $key => $value) {
                    if (in_array($key, ['post_id', 'post_title', 'post_name', 'post_status', 'menu_order', 'post_content', 'featured_image'], true)) continue;
                    if (strpos($key, 'tax_') === 0) continue;
                    if ($key === 'digital_chord') continue;
                    if ($key === 'pdf_source' || $key === 'pdf_thumb') continue;
                    $this->save_meta_value($books_post_id, $key, $value, $media_save_mode);
                }

                if (!empty($pdf_url)) {
                    update_post_meta($books_post_id, 'digital_chord', '[dflip id="' . intval($dflip_post_id) . '"]');
                    update_post_meta($books_post_id, 'dflip_id', intval($dflip_post_id));
                }

                $this->force_scf_acf_resave_after_import($books_post_id);
                continue;
            }

            $existing_id = $this->find_existing_post($post_type, $slug, $old_post_id);
            if ($existing_id && $row_action === 'skip') {
                $result['skipped']++;
                continue;
            }

            $final_slug = ($existing_id && $row_action === 'create') ? $this->make_unique_slug($slug, $post_type) : $slug;
            $postarr = [
                'post_type'    => $post_type,
                'post_title'   => $title,
                'post_name'    => $final_slug,
                'post_status'  => $status ?: 'publish',
                'menu_order'   => $menu_order,
                'post_content' => wp_kses_post($data['post_content'] ?? ''),
            ];

            if ($existing_id && $row_action === 'update') {
                $postarr['ID'] = $existing_id;
                $post_id = wp_update_post($postarr, true);
                $result['updated']++;
            } else {
                $post_id = wp_insert_post($postarr, true);
                $result['created']++;
            }

            if (is_wp_error($post_id) || !$post_id) {
                $result['errors']++;
                continue;
            }

            if ($import_as_dflip) {
                $pdf_url = $data['pdf_source'] ?? '';
                if (!$pdf_url && !empty($data['prints'])) $pdf_url = $data['prints'];

                $thumb_url = '';
                if (!empty($data['digital_img'])) $thumb_url = $data['digital_img'];
                if (!empty($data['pdf_thumb'])) $thumb_url = $data['pdf_thumb'];

                if ($media_save_mode === 'copy_url' || $media_save_mode === 'hybrid') {
                    $pdf_url = $this->copy_media_and_get_url($pdf_url, $post_id);
                    $thumb_url = $this->copy_media_and_get_url($thumb_url, $post_id);
                }

                $this->save_taxonomies_from_csv($post_id, $data);
                $this->save_dflip_data($post_id, $title, $final_slug, $pdf_url, $thumb_url, $status ?: 'publish');
                continue;
            }

            $this->save_taxonomies_from_csv($post_id, $data);
            $this->save_featured_image_from_csv($post_id, $data, $media_save_mode);
            $this->clear_product_gallery_meta_before_import($post_id, $data);

            foreach ($data as $key => $value) {
                if (in_array($key, ['post_id', 'post_title', 'post_name', 'post_status', 'menu_order', 'post_content', 'featured_image'], true)) continue;
                if (strpos($key, 'tax_') === 0) continue;
                $this->save_meta_value($post_id, $key, $value, $media_save_mode);
            }

            $this->force_scf_acf_resave_after_import($post_id);
        }

        return $result;
    }


    private function clear_product_gallery_meta_before_import($post_id, $data)
    {
        $post_id = intval($post_id);
        if (!$post_id || !is_array($data)) return;

        // 上書きインポート時は、既存の product_gallery 関連metaが残っていると
        // 旧行 + 新行が混在し、画像だけ2重表示になることがあります。
        // CSV内にギャラリー系の列がある場合だけ、先に完全クリアしてから再構築します。
        $gallery_keys = [
            'product_gallery',
            'gallery_image',
            'youtube_id',
            'product_youtube',
        ];

        $has_gallery_data = false;
        foreach ($gallery_keys as $gallery_key) {
            if (array_key_exists($gallery_key, $data)) {
                $has_gallery_data = true;
                break;
            }
        }

        if (!$has_gallery_data) return;

        // 親・子の通常metaを削除
        foreach ($gallery_keys as $gallery_key) {
            delete_post_meta($post_id, $gallery_key);
            delete_post_meta($post_id, '_' . $gallery_key);
            $this->update_scf_repeat_multiple_data($post_id, $gallery_key, []);
            $this->unmark_hybrid_meta_key($post_id, $gallery_key);
        }

        // ACF/Secure Custom Fields形式の行metaを削除
        for ($i = 0; $i < 500; $i++) {
            delete_post_meta($post_id, 'product_gallery_' . $i . '_gallery_image');
            delete_post_meta($post_id, '_product_gallery_' . $i . '_gallery_image');
            delete_post_meta($post_id, 'product_gallery_' . $i . '_youtube_id');
            delete_post_meta($post_id, '_product_gallery_' . $i . '_youtube_id');
            delete_post_meta($post_id, 'product_gallery_' . $i . '_product_youtube');
            delete_post_meta($post_id, '_product_gallery_' . $i . '_product_youtube');
        }

        clean_post_cache($post_id);
        wp_cache_delete($post_id, 'post_meta');
    }

    private function force_scf_acf_resave_after_import($post_id)
    {
        $post_id = intval($post_id);
        if (!$post_id) return;

        // ACF/SCF の繰り返しフィールドは、管理画面で「更新」を押すと
        // 親フィールドの行数・キャッシュ・内部メタが整うことがあります。
        // インポート直後にも同等の整形を自動で走らせます。
        $this->rebuild_product_gallery_repeater($post_id);

        clean_post_cache($post_id);
        wp_cache_delete($post_id, 'post_meta');

        // ここで wp_update_post() を呼ぶと、SCF/ACF の save_post フックが再度走り、
        // 画像だけ通常meta側へ追加されて2重表示になるケースがあるため実行しません。
        // product_gallery の必要metaは rebuild_product_gallery_repeater() で直接1セットに再構築します。
    }

    private function rebuild_product_gallery_repeater($post_id)
    {
        $post_id = intval($post_id);
        if (!$post_id) return;

        $rows = [];

        // 1) 親リピーター product_gallery がJSON/配列で入っている場合を優先
        $parent_value = get_post_meta($post_id, 'product_gallery', true);
        if (is_string($parent_value) && $this->is_json(trim($parent_value))) {
            $parent_value = json_decode(trim($parent_value), true);
        }
        if (is_array($parent_value) && $this->is_list_array($parent_value)) {
            foreach ($parent_value as $row) {
                if (!is_array($row)) continue;
                $image = $row['gallery_image'] ?? '';
                $youtube = $row['youtube_id'] ?? ($row['product_youtube'] ?? '');
                if ($image !== '' || $youtube !== '') {
                    $rows[] = [
                        'gallery_image' => $image,
                        'youtube_id'    => $youtube,
                    ];
                }
            }
        }

        // 2) ACF形式 product_gallery_0_gallery_image で入っている場合
        if (empty($rows)) {
            $i = 0;
            while ($i < 200) {
                $image = get_post_meta($post_id, 'product_gallery_' . $i . '_gallery_image', true);
                $youtube = get_post_meta($post_id, 'product_gallery_' . $i . '_youtube_id', true);
                if ($youtube === '') {
                    $youtube = get_post_meta($post_id, 'product_gallery_' . $i . '_product_youtube', true);
                }

                if ($image === '' && $youtube === '') {
                    // 途中の空行を考慮して、連続5行空なら終了
                    $empty_streak = 0;
                    for ($j = $i; $j < $i + 5; $j++) {
                        $chk1 = get_post_meta($post_id, 'product_gallery_' . $j . '_gallery_image', true);
                        $chk2 = get_post_meta($post_id, 'product_gallery_' . $j . '_youtube_id', true);
                        if ($chk1 === '' && $chk2 === '') $empty_streak++;
                    }
                    if ($empty_streak >= 5) break;
                    $i++;
                    continue;
                }

                $rows[] = [
                    'gallery_image' => $image,
                    'youtube_id'    => $youtube,
                ];
                $i++;
            }
        }

        // 3) SCF/Smart Custom Fields 形式で子フィールドだけ複数meta保存されている場合
        if (empty($rows)) {
            $images = get_post_meta($post_id, 'gallery_image', false);
            $youtubes = get_post_meta($post_id, 'youtube_id', false);

            if (empty($youtubes)) {
                $youtubes = get_post_meta($post_id, 'product_youtube', false);
            }

            $max = max(count($images), count($youtubes));

            for ($i = 0; $i < $max; $i++) {
                $image = $images[$i] ?? '';
                $youtube = $youtubes[$i] ?? '';

                if ($image === '' && $youtube === '') continue;

                $rows[] = [
                    'gallery_image' => $image,
                    'youtube_id'    => $youtube,
                ];
            }
        }

        if (empty($rows)) return;

        // 旧版で重複登録された行をここで整理します。
        $rows = $this->dedupe_repeater_rows($rows);
        if (empty($rows)) return;

        // 画像URLは可能なら本番の添付IDへ寄せる。ACF画像フィールドはID保存のケースが多いため。
        foreach ($rows as $i => $row) {
            $rows[$i]['gallery_image'] = $this->normalize_image_value_for_repeater($row['gallery_image'], $post_id);
            $rows[$i]['youtube_id'] = is_string($row['youtube_id']) ? $row['youtube_id'] : '';
        }

        // URL時点では別物に見えても、添付IDへ変換後に同一画像になるケースがあるため、
        // 正規化後にも再度重複除去します。
        $rows = $this->dedupe_repeater_rows($rows);
        if (empty($rows)) return;

        // ACF / Secure Custom Fields のフィールドキーを取得。
        // update_field() は新規メタの場合、フィールド名より field_xxx キーの方が確実です。
        $parent_field_key = $this->find_acf_field_key_by_name('product_gallery');
        $image_field_key  = $this->find_acf_field_key_by_name('gallery_image', $parent_field_key);
        $youtube_field_key = $this->find_acf_field_key_by_name('youtube_id', $parent_field_key);
        if (!$youtube_field_key) {
            $youtube_field_key = $this->find_acf_field_key_by_name('product_youtube', $parent_field_key);
        }

        // 重複の原因になる通常meta側の子フィールドを削除してから、
        // ACF/Secure Custom Fields形式だけを正として再構築します。
        $this->cleanup_product_gallery_duplicate_meta($post_id);

        // ACF/Secure Custom Fields形式のメタを直接構築
        delete_post_meta($post_id, 'product_gallery');
        update_post_meta($post_id, 'product_gallery', count($rows));
        if ($parent_field_key) {
            update_post_meta($post_id, '_product_gallery', $parent_field_key);
        }

        // 古い行を少し広めに掃除
        for ($i = 0; $i < 200; $i++) {
            delete_post_meta($post_id, 'product_gallery_' . $i . '_gallery_image');
            delete_post_meta($post_id, '_product_gallery_' . $i . '_gallery_image');
            delete_post_meta($post_id, 'product_gallery_' . $i . '_youtube_id');
            delete_post_meta($post_id, '_product_gallery_' . $i . '_youtube_id');
            delete_post_meta($post_id, 'product_gallery_' . $i . '_product_youtube');
            delete_post_meta($post_id, '_product_gallery_' . $i . '_product_youtube');
        }

        foreach ($rows as $i => $row) {
            update_post_meta($post_id, 'product_gallery_' . $i . '_gallery_image', $row['gallery_image']);
            if ($image_field_key) {
                update_post_meta($post_id, '_product_gallery_' . $i . '_gallery_image', $image_field_key);
            }

            update_post_meta($post_id, 'product_gallery_' . $i . '_youtube_id', $row['youtube_id']);
            if ($youtube_field_key) {
                update_post_meta($post_id, '_product_gallery_' . $i . '_youtube_id', $youtube_field_key);
            }
        }

        // update_field / SCF::update / acf/save_post はここでは呼びません。
        // 旧版ではこれらが通常meta側にも子フィールドを追加し、2重表示の原因になるケースがありました。
        // 上で ACF/Secure Custom Fields 互換の post_meta を直接1セットだけ再構築しています。
    }


    private function dedupe_repeater_rows($rows)
    {
        $clean = [];
        $image_index = [];
        $youtube_seen = [];

        foreach ((array)$rows as $row) {
            if (!is_array($row)) continue;

            $image = $row['gallery_image'] ?? '';
            $youtube = $row['youtube_id'] ?? ($row['product_youtube'] ?? '');
            $youtube = is_string($youtube) ? trim($youtube) : '';
            $image_key = $this->make_gallery_image_dedupe_key($image);

            // 画像がある行は、YouTubeの有無に関係なく「同じ画像は1行」に統合します。
            // 旧版で ACF形式 + 通常meta形式の両方が残ると、同じ画像だけ2重になるためです。
            if ($image_key !== '') {
                if (isset($image_index[$image_key])) {
                    $idx = $image_index[$image_key];
                    if ($clean[$idx]['youtube_id'] === '' && $youtube !== '') {
                        $clean[$idx]['youtube_id'] = $youtube;
                    }
                    continue;
                }

                $image_index[$image_key] = count($clean);
                $clean[] = [
                    'gallery_image' => $image,
                    'youtube_id'    => $youtube,
                ];
                continue;
            }

            // 画像が無いYouTube単体行はYouTube URL/IDで重複排除します。
            if ($youtube !== '') {
                $youtube_key = md5($youtube);
                if (isset($youtube_seen[$youtube_key])) continue;
                $youtube_seen[$youtube_key] = true;

                $clean[] = [
                    'gallery_image' => '',
                    'youtube_id'    => $youtube,
                ];
            }
        }

        return $clean;
    }

    private function make_gallery_image_dedupe_key($image)
    {
        if (is_array($image)) {
            if (!empty($image['ID'])) return 'id:' . intval($image['ID']);
            if (!empty($image['id'])) return 'id:' . intval($image['id']);
            if (!empty($image['url'])) $image = $image['url'];
            else return md5(wp_json_encode($image));
        }

        if (is_numeric($image)) {
            return 'id:' . intval($image);
        }

        $image = trim((string)$image);
        if ($image === '') return '';

        // URLがデモ/本番で異なっても、同じファイル名なら同一画像として扱います。
        if (filter_var($image, FILTER_VALIDATE_URL)) {
            $path = parse_url($image, PHP_URL_PATH);
            if ($path) {
                return 'file:' . strtolower(basename($path));
            }
        }

        return 'raw:' . $image;
    }

    private function cleanup_product_gallery_duplicate_meta($post_id)
    {
        $post_id = intval($post_id);
        if (!$post_id) return;

        // 旧バージョンで通常metaとして保存された子フィールドを削除。
        // これが残ると、管理画面更新後やテンプレート側の救済処理で2重表示になる場合があります。
        foreach (['gallery_image', 'youtube_id', 'product_youtube'] as $child_key) {
            delete_post_meta($post_id, $child_key);
            delete_post_meta($post_id, '_' . $child_key);
            $this->update_scf_repeat_multiple_data($post_id, $child_key, []);
            $this->unmark_hybrid_meta_key($post_id, $child_key);
        }
    }

    private function normalize_image_value_for_repeater($value, $post_id)
    {
        if (is_array($value)) {
            if (!empty($value['ID'])) return intval($value['ID']);
            if (!empty($value['id'])) return intval($value['id']);
            if (!empty($value['url'])) $value = $value['url'];
        }

        if (is_numeric($value)) {
            return intval($value);
        }

        if (!is_string($value) || trim($value) === '') {
            return '';
        }

        $url = trim($value);
        if (!filter_var($url, FILTER_VALIDATE_URL) || !$this->looks_like_media_url($url)) {
            return $value;
        }

        $attachment_id = 0;
        if (function_exists('attachment_url_to_postid')) {
            $attachment_id = intval(attachment_url_to_postid($url));
        }

        if (!$attachment_id) {
            $attachment_id = $this->media_sideload($url, $post_id);
        }

        return $attachment_id ? $attachment_id : esc_url_raw($url);
    }

    private function find_acf_field_key_by_name($field_name, $parent_field_key = '')
    {
        $field_name = sanitize_key($field_name);
        if ($field_name === '') return '';

        global $wpdb;

        $parent_id = 0;
        if ($parent_field_key) {
            $parent_id = intval($wpdb->get_var($wpdb->prepare(
                "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'acf-field' AND post_name = %s LIMIT 1",
                $parent_field_key
            )));
        }

        if ($parent_id) {
            $key = $wpdb->get_var($wpdb->prepare(
                "SELECT post_name FROM {$wpdb->posts} WHERE post_type = 'acf-field' AND post_parent = %d AND post_excerpt = %s LIMIT 1",
                $parent_id,
                $field_name
            ));
            if ($key) return $key;
        }

        $key = $wpdb->get_var($wpdb->prepare(
            "SELECT post_name FROM {$wpdb->posts} WHERE post_type = 'acf-field' AND post_excerpt = %s ORDER BY ID DESC LIMIT 1",
            $field_name
        ));

        return $key ? $key : '';
    }

    private function get_menu_order_for_import($data, $index)
    {
        // CSVに menu_order 列がある場合はその値を優先。
        // 空欄の場合はCSVの並び順どおりに 1,2,3... を自動設定します。
        if (array_key_exists('menu_order', $data) && trim((string)$data['menu_order']) !== '') {
            return intval($data['menu_order']);
        }
        return intval($index) + 1;
    }

    private function make_unique_slug($base_slug, $post_type)
    {
        $base_slug = sanitize_title($base_slug);
        if ($base_slug === '') $base_slug = 'imported-post';

        $slug = $base_slug;
        $i = 2;
        while (get_page_by_path($slug, OBJECT, $post_type)) {
            $slug = $base_slug . '-' . $i;
            $i++;
        }
        return $slug;
    }

    private function clean_header($header)
    {
        $header = preg_replace('/^\xEF\xBB\xBF/', '', $header);
        return trim($header);
    }

    private function is_books_csv_header($header)
    {
        $header = array_map('strval', (array)$header);

        // books移行CSVの判定。digital_img / prints が入っているCSVだけbooks⇔DearFlip連携を有効にします。
        // dflip CSV（pdf_sourceだけのCSV）はbooks投稿を作成しません。
        return in_array('digital_img', $header, true)
            || in_array('prints', $header, true)
            || in_array('digital_chord', $header, true)
            || in_array('tax_books_cate', $header, true);
    }

    private function replace_urls_in_data($data, $from, $to, $media_save_mode = 'url')
    {
        if ($from === '' || $to === '') return $data;

        foreach ($data as $k => $v) {
            if (!is_string($v) || $v === '') {
                continue;
            }

            // メディアをコピー/ID保存/ハイブリッド保存する場合、
            // 画像・PDFなどのURLはコピー元URL（デモURL）のまま保持します。
            // ここで本番URLへ置換すると、本番にまだファイルが無く404になります。
            if (in_array($media_save_mode, ['copy_url', 'id', 'hybrid'], true)) {
                $trimmed = trim($v);

                if (filter_var($trimmed, FILTER_VALIDATE_URL) && $this->looks_like_media_url($trimmed)) {
                    $data[$k] = $v;
                    continue;
                }

                if ($this->is_json($trimmed)) {
                    $decoded = json_decode($trimmed, true);
                    $decoded = $this->replace_urls_recursive_preserve_media($decoded, $from, $to);
                    $data[$k] = wp_json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    continue;
                }
            }

            $data[$k] = str_replace($from, $to, $v);
        }
        return $data;
    }

    private function replace_urls_recursive_preserve_media($value, $from, $to)
    {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = $this->replace_urls_recursive_preserve_media($v, $from, $to);
            }
            return $value;
        }

        if (!is_string($value) || $value === '') {
            return $value;
        }

        $trimmed = trim($value);
        if (filter_var($trimmed, FILTER_VALIDATE_URL) && $this->looks_like_media_url($trimmed)) {
            return $value;
        }

        return str_replace($from, $to, $value);
    }

    private function find_existing_post($post_type, $slug, $old_post_id = 0)
    {
        if ($old_post_id && get_post($old_post_id) && get_post_type($old_post_id) === $post_type) {
            return $old_post_id;
        }

        $found = get_page_by_path($slug, OBJECT, $post_type);
        return $found ? (int)$found->ID : 0;
    }

    private function save_taxonomies_from_csv($post_id, $data)
    {
        foreach ($data as $key => $value) {
            if (strpos($key, 'tax_') !== 0) continue;

            $taxonomy = substr($key, 4);
            if (!taxonomy_exists($taxonomy)) continue;

            $value = trim((string)$value);
            if ($value === '') {
                wp_set_object_terms($post_id, [], $taxonomy, false);
                continue;
            }

            $term_names = array_filter(array_map('trim', explode('|', $value)));
            $term_ids = [];

            foreach ($term_names as $term_name) {
                $term = term_exists($term_name, $taxonomy);
                if (!$term) {
                    $term = wp_insert_term($term_name, $taxonomy);
                }
                if (!is_wp_error($term)) {
                    $term_ids[] = is_array($term) ? intval($term['term_id']) : intval($term);
                }
            }

            wp_set_object_terms($post_id, $term_ids, $taxonomy, false);
        }
    }

    private function save_featured_image_from_csv($post_id, $data, $media_save_mode = 'url')
    {
        $image_url = '';
        foreach (['featured_image', 'thumbnail', 'post_thumbnail'] as $key) {
            if (!empty($data[$key])) {
                $image_url = trim((string)$data[$key]);
                break;
            }
        }

        if ($image_url === '') {
            return;
        }

        $attachment_id = 0;

        if (is_numeric($image_url)) {
            $maybe_id = intval($image_url);
            if (get_post($maybe_id) && get_post_type($maybe_id) === 'attachment') {
                $attachment_id = $maybe_id;
            }
        }

        if (!$attachment_id && filter_var($image_url, FILTER_VALIDATE_URL)) {
            // すでに本番メディアにあるURLなら再利用します。
            if (function_exists('attachment_url_to_postid')) {
                $attachment_id = intval(attachment_url_to_postid($image_url));
            }

            // 見つからない場合はメディアライブラリへコピーします。
            if (!$attachment_id && $this->looks_like_media_url($image_url)) {
                $attachment_id = $this->media_sideload($image_url, $post_id);
            }
        }

        if ($attachment_id) {
            set_post_thumbnail($post_id, $attachment_id);
        }
    }

    private function save_meta_value($post_id, $key, $value, $media_save_mode = 'url')
    {
        // テキストエリアの改行・連続スペースを保持するため、最初に trim / sanitize_text_field をかけない。
        $raw_value = (string)$value;
        $check_value = trim($raw_value);

        if ($raw_value === '') {
            delete_post_meta($post_id, $key);
            $this->unmark_hybrid_meta_key($post_id, $key);
            return;
        }

        if ($this->is_json($check_value)) {
            $decoded = json_decode($check_value, true);
            $decoded = $this->prepare_meta_import_value_recursive($decoded, $post_id, $media_save_mode);

            if ($this->looks_like_scf_repeat_value($decoded)) {
                $this->save_scf_repeat_meta_value($post_id, $key, $decoded);
            } else {
                $this->update_meta_compatible($post_id, $key, $decoded);
            }

            if ($media_save_mode === 'hybrid') {
                $this->mark_hybrid_meta_key($post_id, $key);
            } else {
                $this->unmark_hybrid_meta_key($post_id, $key);
            }
            return;
        }

        if (filter_var($check_value, FILTER_VALIDATE_URL) && $this->looks_like_media_url($check_value)) {
            if ($media_save_mode === 'id' || $media_save_mode === 'hybrid') {
                $attachment_id = $this->media_sideload($check_value, $post_id);
                if ($attachment_id) {
                    $this->update_meta_compatible($post_id, $key, $attachment_id);
                    if ($media_save_mode === 'hybrid') {
                        $this->mark_hybrid_meta_key($post_id, $key);
                    } else {
                        $this->unmark_hybrid_meta_key($post_id, $key);
                    }
                } else {
                    $this->update_meta_compatible($post_id, $key, esc_url_raw($check_value));
                    $this->unmark_hybrid_meta_key($post_id, $key);
                }
                return;
            }

            if ($media_save_mode === 'copy_url') {
                $this->update_meta_compatible($post_id, $key, $this->copy_media_and_get_url($check_value, $post_id));
                $this->unmark_hybrid_meta_key($post_id, $key);
                return;
            }

            // デフォルト：URLのまま保存。テンプレート側の修正不要。
            $this->update_meta_compatible($post_id, $key, esc_url_raw($check_value));
            $this->unmark_hybrid_meta_key($post_id, $key);
            return;
        }

        // sanitize_text_field は改行を半角スペースへ変換するため使わない。
        // wp_kses_post は改行・連続スペースを保持しつつ、危険なタグ/属性は除去する。
        $this->update_meta_compatible($post_id, $key, wp_kses_post($raw_value));
        $this->unmark_hybrid_meta_key($post_id, $key);
    }



    private function looks_like_scf_repeat_value($value)
    {
        if (!is_array($value) || empty($value)) return false;
        if (!$this->is_list_array($value)) return false;

        // CSV出力時に複数meta行をJSON化したものは、SCFの繰り返しフィールドとして復元します。
        // 例：画像の繰り返し [123,456]、YouTubeの繰り返し ["...","..."]、チェックボックスの繰り返し [["a"],["b","c"]]
        return true;
    }

    private function is_list_array($array)
    {
        if (!is_array($array)) return false;
        $i = 0;
        foreach (array_keys($array) as $key) {
            if ($key !== $i) return false;
            $i++;
        }
        return true;
    }

    private function save_scf_repeat_meta_value($post_id, $key, $values)
    {
        delete_post_meta($post_id, $key);
        $this->update_scf_repeat_multiple_data($post_id, $key, []);

        $counts = [];

        foreach ($values as $row_value) {
            // 多値フィールド（checkbox等）が繰り返し内にある場合。
            if (is_array($row_value) && $this->is_list_array($row_value)) {
                $counts[] = count($row_value);
                foreach ($row_value as $single_value) {
                    add_post_meta($post_id, $key, $single_value);
                }
                continue;
            }

            // 通常の繰り返しフィールド（画像/テキスト/YouTube等）。
            $counts[] = 1;
            add_post_meta($post_id, $key, $row_value);
        }

        // checkbox等の多値フィールドだけ SCF の分割情報が必要。
        $needs_multiple_data = false;
        foreach ($counts as $count) {
            if ($count !== 1) {
                $needs_multiple_data = true;
                break;
            }
        }

        if ($needs_multiple_data) {
            $this->update_scf_repeat_multiple_data($post_id, $key, $counts);
        }

        if (class_exists('SCF') && method_exists('SCF', 'update')) {
            try {
                SCF::update($key, $values, $post_id);
            } catch (Throwable $e) {
            } catch (Exception $e) {
            }
        }
    }

    private function prepare_meta_import_value_recursive($value, $post_id, $media_save_mode = 'url')
    {
        if (is_array($value)) {
            $converted = [];
            foreach ($value as $k => $v) {
                $converted[$k] = $this->prepare_meta_import_value_recursive($v, $post_id, $media_save_mode);
            }
            return $converted;
        }

        if (!is_string($value)) {
            return $value;
        }

        $check_value = trim($value);

        if (!filter_var($check_value, FILTER_VALIDATE_URL) || !$this->looks_like_media_url($check_value)) {
            return $value;
        }

        if ($media_save_mode === 'id' || $media_save_mode === 'hybrid') {
            $attachment_id = $this->media_sideload($check_value, $post_id);
            return $attachment_id ? $attachment_id : esc_url_raw($check_value);
        }

        if ($media_save_mode === 'copy_url') {
            return $this->copy_media_and_get_url($check_value, $post_id);
        }

        return esc_url_raw($check_value);
    }

    private function update_meta_compatible($post_id, $key, $value)
    {
        update_post_meta($post_id, $key, $value);

        // SCF/Smart Custom Fields は管理画面表示用に SCF::update を通した方が、
        // 画像フィールド・繰り返しフィールドで「選択済み」として復元されやすい。
        // SCF が無い環境でも通常の post_meta 保存は先に完了しています。
        if (class_exists('SCF') && method_exists('SCF', 'update')) {
            try {
                SCF::update($key, $value, $post_id);
            } catch (Throwable $e) {
                // SCF側で受け取れない形式でも、通常の post_meta は保存済みなので処理継続。
            } catch (Exception $e) {
                // PHP5系互換用。
            }
        }
    }

    private function mark_hybrid_meta_key($post_id, $key)
    {
        $keys = get_post_meta($post_id, '_scf_dflip_url_front_keys', true);
        if (!is_array($keys)) $keys = [];
        if (!in_array($key, $keys, true)) {
            $keys[] = $key;
        }
        update_post_meta($post_id, '_scf_dflip_url_front_keys', array_values(array_unique($keys)));
    }

    private function unmark_hybrid_meta_key($post_id, $key)
    {
        $keys = get_post_meta($post_id, '_scf_dflip_url_front_keys', true);
        if (!is_array($keys) || empty($keys)) return;
        $keys = array_values(array_filter($keys, function ($saved_key) use ($key) {
            return $saved_key !== $key;
        }));
        if ($keys) {
            update_post_meta($post_id, '_scf_dflip_url_front_keys', $keys);
        } else {
            delete_post_meta($post_id, '_scf_dflip_url_front_keys');
        }
    }

    public function maybe_return_media_url_on_front($value, $object_id, $meta_key, $single, $meta_type)
    {
        if ($meta_type !== 'post') return $value;
        if (is_admin()) return $value;
        if (!$single) return $value;
        if (!$object_id || $meta_key === '' || strpos($meta_key, '_') === 0) return $value;

        if (function_exists('get_metadata_raw')) {
            $hybrid_keys = get_metadata_raw('post', $object_id, '_scf_dflip_url_front_keys', true);
        } else {
            remove_filter('get_post_metadata', [$this, 'maybe_return_media_url_on_front'], 10);
            $hybrid_keys = get_post_meta($object_id, '_scf_dflip_url_front_keys', true);
            add_filter('get_post_metadata', [$this, 'maybe_return_media_url_on_front'], 10, 5);
        }

        if (!is_array($hybrid_keys) || !in_array($meta_key, $hybrid_keys, true)) {
            return $value;
        }

        if (function_exists('get_metadata_raw')) {
            $raw = get_metadata_raw('post', $object_id, $meta_key, true);
        } else {
            remove_filter('get_post_metadata', [$this, 'maybe_return_media_url_on_front'], 10);
            $raw = get_post_meta($object_id, $meta_key, true);
            add_filter('get_post_metadata', [$this, 'maybe_return_media_url_on_front'], 10, 5);
        }

        if (is_numeric($raw)) {
            $url = wp_get_attachment_url((int)$raw);
            if ($url) return $url;
        }

        return $value;
    }

    private function is_json($value)
    {
        if (!is_string($value) || $value === '') return false;
        json_decode($value, true);
        return json_last_error() === JSON_ERROR_NONE && in_array(substr(trim($value), 0, 1), ['[', '{'], true);
    }

    private function looks_like_media_url($url)
    {
        $path = parse_url($url, PHP_URL_PATH);
        if (!$path) return false;
        return (bool)preg_match('/\.(jpg|jpeg|png|gif|webp|svg|pdf|zip|doc|docx|xls|xlsx|ppt|pptx)$/i', $path);
    }

    private function copy_media_and_get_url($url, $post_id)
    {
        if (!$url || !filter_var($url, FILTER_VALIDATE_URL) || !$this->looks_like_media_url($url)) {
            return esc_url_raw($url);
        }

        $attachment_id = $this->media_sideload($url, $post_id);
        if ($attachment_id) {
            $new_url = wp_get_attachment_url($attachment_id);
            return $new_url ? esc_url_raw($new_url) : esc_url_raw($url);
        }
        return esc_url_raw($url);
    }

    private function media_sideload($url, $post_id)
    {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $tmp = download_url($url, 30);
        if (is_wp_error($tmp)) return 0;

        $path = parse_url($url, PHP_URL_PATH);
        $filename = $path ? basename($path) : 'download-file';
        $filename = sanitize_file_name(urldecode($filename));

        $file_array = [
            'name'     => $filename,
            'tmp_name' => $tmp,
        ];

        $attachment_id = media_handle_sideload($file_array, $post_id);

        if (is_wp_error($attachment_id)) {
            @unlink($tmp);
            return 0;
        }

        return (int)$attachment_id;
    }

    private function save_dflip_data($post_id, $title, $slug, $pdf_url, $thumb_url = '', $status = 'publish')
    {
        $dflip_data = [
            'source_type'         => 'pdf',
            'pdf_source'          => esc_url_raw($pdf_url),
            'pdf_thumb'           => esc_url_raw($thumb_url),
            'pages'               => [
                [
                    'url'      => '',
                    'hotspots' => [],
                ],
            ],
            'viewerType'          => 'global',
            'webgl'               => 'global',
            'hard'                => 'global',
            'bg_color'            => '',
            'bg_image'            => '',
            'duration'            => '',
            'height'              => '',
            'texture_size'        => 'global',
            'auto_sound'          => 'global',
            'enable_download'     => 'global',
            'page_mode'           => 'global',
            'single_page_mode'    => 'global',
            'controls_position'   => 'global',
            'direction'           => '1',
            'autoplay'            => 'global',
            'autoplay_duration'   => '',
            'autoplay_start'      => 'global',
            'page_size'           => '0',
            'auto_outline'        => 'false',
            'auto_thumbnail'      => 'false',
            'overwrite_outline'   => 'false',
            'outline'             => [],
            'title'               => $title,
            'slug'                => $slug,
            'status'              => $status,
        ];

        update_post_meta($post_id, '_dflip_data', $dflip_data);
    }
}

new SCF_DFlip_CSV_Import_Export();
