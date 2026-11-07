<?php
/**
 * Plugin Name: Welcart 商品画像移行アシスタント（49商品用）
 * Description: FTP 配置済みの画像をメディア登録し、商品ごとに画像を選択して Welcart 2.6+ へ適用する一時的な移行ツールです。
 * Version: 1.2.0
 * Requires PHP: 7.4
 * Author: Migration Helper
 */
if ( ! defined('ABSPATH') ) { exit; }

class WIM_Image_Migration_Helper {
    const SLUG = 'wim-image-migration';
    private static $manifest = null;
    private static $products = null;
    private static $post_id_hints = null;

    public static function setup() {
        add_action('admin_menu', array(__CLASS__, 'admin_menu'));
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue'));
        add_action('wp_ajax_wim_import_media', array(__CLASS__, 'import_media'));
        add_action('wp_ajax_wim_save_images', array(__CLASS__, 'save_images'));
        add_action('wp_ajax_wim_fix_descriptions', array(__CLASS__, 'fix_descriptions'));
        add_action('wp_ajax_wim_diagnose', array(__CLASS__, 'diagnose'));
    }
    private static function read_json($file) {
        $path = plugin_dir_path(__FILE__) . $file;
        if ( ! is_readable($path) ) { return array(); }
        $val = json_decode((string) file_get_contents($path), true);
        return is_array($val) ? $val : array();
    }
    private static function manifest() {
        if (self::$manifest === null) { self::$manifest = self::read_json('manifest.json'); }
        return self::$manifest;
    }
    private static function products() {
        if (self::$products === null) { self::$products = self::read_json('products.json'); }
        return self::$products;
    }
    private static function authorized() {
        return current_user_can('manage_options');
    }
    public static function admin_menu() {
        add_management_page('Welcart 商品画像移行', 'Welcart 画像移行', 'manage_options', self::SLUG, array(__CLASS__, 'page'));
    }
    public static function enqueue($hook) {
        if ($hook !== 'tools_page_' . self::SLUG) { return; }
        wp_enqueue_media();
        wp_enqueue_script('jquery');
    }
    /**
     * 2026-10-19 の移行先サイト Welcart CSV と照合した Post ID を優先。
     * ID の使い回しや商品入替えに備え、毎回Welcartの商品コードで検証します。
     * 旧形式の _itemCode メタは新方式で存在する保証がないため参照しません。
     */
    private static function find_post_id($code) {
        if ( ! function_exists('wel_get_item') ) { return 0; }
        if (self::$post_id_hints === null) {
            self::$post_id_hints = self::read_json('post-ids.json');
        }
        $candidates = array();
        if (isset(self::$post_id_hints[$code])) {
            $candidates[] = absint(self::$post_id_hints[$code]);
        }
        if (function_exists('wel_get_id_by_item_code')) {
            $candidates[] = (int) wel_get_id_by_item_code($code, false);
        }
        foreach (array_unique($candidates) as $id) {
            if (!$id || !get_post($id)) { continue; }
            $item = wel_get_item($id, false);
            if (is_array($item) && isset($item['itemCode']) && (string) $item['itemCode'] === (string) $code) {
                return $id;
            }
        }
        return 0;
    }
    private static function rel_is_safe($relative) {
        return is_string($relative)
            && $relative !== ''
            && strpos($relative, '..') === false
            && strpos($relative, "\\") === false
            && $relative[0] !== '/'
            && preg_match('~^20[0-9]{2}/[0-9]{2}/.+\\.(?:jpg|jpeg|png|gif|webp)$~iu', $relative);
    }
    private static function media_id_by_path($relative) {
        if ( ! self::rel_is_safe($relative) ) { return 0; }
        $ids = get_posts(array(
            'post_type' => 'attachment', 'post_status' => 'any', 'posts_per_page' => 1,
            'fields' => 'ids', 'no_found_rows' => true,
            'meta_query' => array(array('key' => '_wp_attached_file', 'value' => $relative, 'compare' => '=')),
        ));
        return empty($ids) ? 0 : (int) $ids[0];
    }
    private static function jpg_preview($relative) {
        if ( ! self::rel_is_safe($relative) ) { return ''; }
        $up = wp_get_upload_dir();
        $url = trailingslashit($up['baseurl']) . implode('/', array_map('rawurlencode', explode('/', $relative)));
        return esc_url($url);
    }
    public static function page() {
        if ( ! self::authorized() ) { wp_die('権限がありません。'); }
        $items = self::products();
        $manifest = self::manifest();
        $up = wp_get_upload_dir();
        $matched = 0;
        foreach ($items as $item) {
            if (self::find_post_id((string) $item['code'])) { ++$matched; }
        }
        $existing = 0;
        foreach ($manifest as $relative) {
            if (self::rel_is_safe($relative) && is_file(trailingslashit($up['basedir']) . $relative)) { ++$existing; }
        }
        ?>
        <div class="wrap wim">
            <h1>Welcart 商品画像移行アシスタント</h1>
            <p><strong>対象サイト：</strong><?php echo esc_html(home_url('/')); ?> ／ <strong>商品：</strong><?php echo count($items); ?>件 ／ <strong>商品コード照合：</strong><?php echo (int) $matched; ?> / <?php echo count($items); ?>件 ／ <strong>登録対象の元画像：</strong><?php echo count($manifest); ?>点</p>
            <?php if ( ! function_exists('wel_get_item') || ! function_exists('wel_update_item_data') || ! function_exists('wel_get_id_by_item_code') ) : ?>
                <div class="notice notice-error"><p>Welcart 2.6以降の必要な関数が見つかりません。Welcartの有効化状態をご確認ください。操作できません。</p></div>
            <?php else : ?>
            <div class="notice notice-warning"><p><strong>本番操作前にDBと uploads をバックアップしてください。</strong>最初は商品を1件選んで画像が正しく表示されるか確認してください。説明内の参考画像はメイン画像とは限りません。</p></div>
            <?php if ($matched !== count($items)) : ?><div class="notice notice-warning"><p>商品コードの照合は <?php echo (int) $matched; ?> / <?php echo count($items); ?> 件です。赤字の商品についてPost IDと商品コードをご確認ください。照合できない商品には画像を適用できません。</p></div><?php endif; ?>
            <h2>① FTP画像をWordPressメディアに登録</h2>
            <p>画像の配置確認：<?php echo (int) $existing; ?> / <?php echo count($manifest); ?>点。アップロード先は <code><?php echo esc_html(trailingslashit($up['basedir'])); ?>YYYY/MM/</code> です。</p>
            <p>WordPressが自動作成した縮小画像を除き、元画像のみを登録します。既に同じパスでメディア登録済みの場合はスキップします。</p>
            <button class="button button-primary" id="wim-import">メディア一括登録を開始</button>
            <span id="wim-progress" aria-live="polite"></span>
            <pre id="wim-log" style="max-height:160px;overflow:auto;background:#fff;padding:10px;display:none"></pre>
            <h2>② 商品に画像を適用・状態を診断（v1.2）</h2>
            <p>反映されない場合は「画像の状態を診断」を押してください。Welcartに保存されている画像IDと、実際に取得できるメイン画像IDを照合できます（診断はデータを変更しません）。<br>「画像を選ぶ」でメディアを複数選択できます。<strong>最初の画像がメイン画像</strong>です。既に適用された画像は、明示的に変更するまで保持されます。</p>
            <table class="widefat striped"><thead><tr><th style="width:30%">商品</th><th style="width:29%">現在の画像</th><th>説明内の参考画像（メイン写真とは限りません）</th><th style="width:135px">操作</th></tr></thead><tbody>
            <?php foreach ($items as $item) :
                $code = (string) $item['code']; $post_id = self::find_post_id($code);
                $data = $post_id && function_exists('wel_get_item') ? wel_get_item($post_id, false) : false;
                $ids = is_array($data) && isset($data['itemPicts']) && is_array($data['itemPicts']) ? array_map('intval', $data['itemPicts']) : array();
            ?>
                <tr data-code="<?php echo esc_attr($code); ?>">
                    <td><strong><?php echo esc_html($item['name']); ?></strong><br><code><?php echo esc_html($code); ?></code><br>
                    <?php if ($post_id) : ?><small>商品Post ID：<?php echo (int) $post_id; ?></small><?php else : ?><strong style="color:#c00">商品コードが見つかりません</strong><?php endif; ?>
                    </td>
                    <td class="wim-current">
                        <?php if ($ids) : foreach($ids as $id) { echo wp_get_attachment_image($id, array(68,68)); } else : echo '<em>未設定</em>'; endif; ?>
                        <div class="wim-count">適用画像数：<span><?php echo count($ids); ?></span></div>
                    </td>
                    <td class="wim-examples">
                        <?php if (!empty($item['desc_paths'])) : foreach (array_slice($item['desc_paths'],0,7) as $relative) : ?>
                            <a href="<?php echo self::jpg_preview($relative); ?>" target="_blank" rel="noopener noreferrer" title="<?php echo esc_attr($relative); ?>"><img loading="lazy" src="<?php echo self::jpg_preview($relative); ?>" alt="説明内の画像例" /></a>
                        <?php endforeach; else : ?><em>説明文に画像なし</em><?php endif; ?>
                    </td>
                    <td>
                        <button type="button" class="button wim-select" <?php disabled(!$post_id); ?> data-code="<?php echo esc_attr($code); ?>">画像を選ぶ</button>
                        <button type="button" class="button wim-diag" <?php disabled(!$post_id); ?> data-code="<?php echo esc_attr($code); ?>" style="margin-top:6px">画像の状態を診断</button>
                        <button class="button-link-delete wim-clear" <?php disabled(!$post_id || !$ids); ?> data-code="<?php echo esc_attr($code); ?>" style="display:block;margin-top:9px">画像を解除</button>
                        <div class="wim-result" aria-live="polite"></div><pre class="wim-diagnosis" style="display:none;white-space:pre-wrap;word-break:break-word;background:#f5f5f5;padding:8px;max-width:330px;font-size:11px;"></pre>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody></table>
            <h2>③ 商品説明内の古い画像URLを変更（必要に応じて）</h2>
            <p>商品説明に残っている <code>https://flolistjapan.jp/wp-content/uploads/</code> を、今回のサイトのアップロードURLへ置換します。対象は上記49商品の説明本文のみです。商品画像の紐付けとは別の処理です。</p>
            <button type="button" class="button" id="wim-fix-description">49商品の説明内画像URLを置換</button>
            <span id="wim-fix-result"></span>
            <p><strong>完了後：</strong>商品ページを数件確認し、不要になったらプラグインを停止・削除してください。</p>
            <?php endif; ?>
        </div>
        <style>
        .wim td{vertical-align:top}.wim .wim-current img,.wim .wim-examples img{display:inline-block;width:65px;height:65px;object-fit:contain;background:#fff;border:1px solid #ddd;margin-right:4px;margin-bottom:4px}.wim .wim-count{margin-top:5px;color:#555}.wim .wim-result{font-size:12px;margin-top:7px}.wim code{word-break:break-word}.wim #wim-progress{margin-left:12px;font-weight:600}.wim em{color:#777}
        </style>
        <script>
        jQuery(function($){
            var ajax = <?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>;
            var nonce = <?php echo wp_json_encode(wp_create_nonce('wim_migration_nonce')); ?>;
            var total = <?php echo (int) count($manifest); ?>;
            function notify($row, message, bad){ $row.find('.wim-result').text(message).css('color', bad?'#b32d2e':'#117a28'); }
            $('#wim-import').on('click',function(){
                var $btn=$(this), offset=0, failed=0, done=0, skipped=0;
                $btn.prop('disabled',true); $('#wim-log').empty().show();
                function next(){
                    $.post(ajax,{action:'wim_import_media',nonce:nonce,offset:offset})
                    .done(function(res){
                        if(!res.success){ $('#wim-progress').text('停止：'+(res.data||'エラー')); $btn.prop('disabled',false); return; }
                        var d=res.data; offset=d.next;done+=d.added;skipped+=d.skipped;failed+=d.errors.length;
                        d.errors.forEach(function(err){ $('#wim-log').append($('<div>').text(err)); });
                        $('#wim-progress').text(Math.min(offset,total)+' / '+total+'件処理　新規登録'+done+'件・登録済み'+skipped+'件・要確認'+failed+'件');
                        if(offset<total){next();}else{$btn.prop('disabled',false);$('#wim-progress').append('　完了。次に商品画像を選択してください。');}
                    }).fail(function(){ $('#wim-progress').text('通信エラー。再度「メディア一括登録」を押すと登録済みはスキップされます。');$btn.prop('disabled',false); });
                } next();
            });
            $(document).on('click','.wim-select',function(){
                var $row=$(this).closest('tr'), code=$(this).data('code');
                var frame=wp.media({title:code+' の商品画像を選択（選択順＝表示順）',button:{text:'この画像を適用'},library:{type:'image'},multiple:true});
                frame.on('select',function(){
                    var ids=frame.state().get('selection').map(function(att){return att.get('id');});
                    if(!ids.length){return;}
                    var count=parseInt($row.find('.wim-count span').text(),10)||0;
                    if(count && !window.confirm('既存の画像'+count+'件を、選択した'+ids.length+'件で置き換えますか？')){return;}
                    notify($row,'保存中…');
                    $.post(ajax,{action:'wim_save_images',nonce:nonce,code:code,ids:ids})
                     .done(function(res){
                        if(!res.success){notify($row, '失敗：'+(res.data||''),true);return;}
                        var $images=$row.find('.wim-current');$images.empty();
                        res.data.images.forEach(function(img){$('<img>').attr('src',img).appendTo($images);});
                        $('<div>').addClass('wim-count').html('適用画像数：<span>'+res.data.images.length+'</span>').appendTo($images);
                        $row.find('.wim-clear').prop('disabled',false);notify($row, (res.data.main_id === res.data.images_ids[0] ? 'Welcartに保存・メイン画像の認識を確認しました。画面更新で商品マスターを確認してください。' : '保存されましたが、Welcartがメイン画像を認識していません。下の「画像の状態を診断」を押してください。'), res.data.main_id !== res.data.images_ids[0]);
                     }).fail(function(){notify($row,'通信エラー',true);});
                });frame.open();
            });
            $(document).on('click','.wim-diag',function(){
                var $row=$(this).closest('tr'), code=$(this).data('code'), $btn=$(this), $pre=$row.find('.wim-diagnosis');
                $btn.prop('disabled',true); $pre.show().text('診断中…');
                $.post(ajax,{action:'wim_diagnose',nonce:nonce,code:code})
                .done(function(res){
                    if(!res.success){$pre.text('診断エラー：'+(res.data||''));return;}
                    var d=res.data, lines=[];
                    lines.push('商品Post ID：'+d.post_id+' / '+d.code);
                    lines.push('Welcartの商品画像ID：'+(d.item_picts.length?d.item_picts.join(', '):'未設定'));
                    lines.push('Welcartメイン画像ID（Post ID指定）：'+(d.main_id||'なし'));
                    lines.push('Welcartメイン画像ID（商品コード指定）：'+(d.main_code_id||'なし'));
                    lines.push('WordPressアイキャッチID：'+(d.thumbnail_id||'なし'));
                    if(d.image_checks.length){
                        d.image_checks.forEach(function(im){lines.push('画像ID '+im.id+'／添付ファイル：'+(im.is_attachment?'あり':'なし')+'／画像URL：'+(im.url||'なし')+'／サーバーファイル：'+(im.file_exists?'あり':'なし'));});
                    }
                    lines.push('判定：'+d.judgment);
                    $pre.text(lines.join('\n'));
                    $row.find('.wim-count span').text(d.item_picts.length);
                }).fail(function(xhr){$pre.text('通信エラー：HTTP '+xhr.status);})
                .always(function(){$btn.prop('disabled',false);});
            });
            $(document).on('click','.wim-clear',function(){
                if(!confirm('この商品の商品画像の関連付けを解除しますか？メディアファイル自体は削除しません。')){return;}
                var $row=$(this).closest('tr'), code=$(this).data('code');
                $.post(ajax,{action:'wim_save_images',nonce:nonce,code:code,ids:[]})
                 .done(function(res){
                    if(!res.success){notify($row,'失敗：'+(res.data||''),true);return;}
                    $row.find('.wim-current').html('<em>未設定</em><div class="wim-count">適用画像数：<span>0</span></div>');$row.find('.wim-clear').prop('disabled',true);notify($row,'関連付けを解除しました。');
                 }).fail(function(){notify($row,'通信エラー',true);});
            });
            $('#wim-fix-description').on('click',function(){
                if(!confirm('DBバックアップは取得済みですか？上記49商品の説明内URLを変更します。'))return;
                var $b=$(this).prop('disabled',true);
                $.post(ajax,{action:'wim_fix_descriptions',nonce:nonce}).done(function(res){
                    $('#wim-fix-result').text(res.success?'置換完了：'+res.data.updated+'商品（該当URLがなければ変更しません）':'処理失敗：'+res.data);
                }).fail(function(){$('#wim-fix-result').text('通信エラー');}).always(function(){$b.prop('disabled',false);});
            });
        });
        </script>
        <?php
    }
    private static function ajax_check() {
        if ( ! self::authorized() ) { wp_send_json_error('管理者権限が必要です。', 403); }
        check_ajax_referer('wim_migration_nonce', 'nonce');
    }
    public static function import_media() {
        self::ajax_check();
        if ( ! function_exists('wel_get_item') ) { wp_send_json_error('Welcartが見つかりません。'); }
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        $paths = self::manifest(); $offset = isset($_POST['offset']) ? max(0, absint($_POST['offset'])) : 0;
        $batch = array_slice($paths,$offset,6); $up=wp_upload_dir();
        $added=0;$skipped=0;$errors=array();
        foreach ($batch as $relative) {
            if ( ! self::rel_is_safe($relative) ) { $errors[]='不正なパス：'.(string) $relative; continue; }
            $base = realpath($up['basedir']);
            $path = realpath(trailingslashit($up['basedir']) . $relative);
            if (!$path || !$base || strpos($path, $base . DIRECTORY_SEPARATOR) !== 0 || !is_file($path)) {
                $errors[]='FTPにファイルがありません：'.$relative;continue;
            }
            if (self::media_id_by_path($relative)) {++$skipped;continue;}
            $ft = wp_check_filetype(basename($path));
            if (empty($ft['type']) || strpos($ft['type'],'image/')!==0) { $errors[]='画像形式が未対応：'.$relative;continue; }
            $post = array('post_mime_type'=>$ft['type'], 'post_title'=>sanitize_text_field(pathinfo(basename($relative),PATHINFO_FILENAME)), 'post_content'=>'', 'post_status'=>'inherit');
            $attachment_id=wp_insert_attachment($post,$path,0,true);
            if (is_wp_error($attachment_id)) {$errors[]='登録失敗：'.$relative.' '.$attachment_id->get_error_message();continue;}
            $meta=wp_generate_attachment_metadata($attachment_id,$path);
            if (is_array($meta) && $meta) {wp_update_attachment_metadata($attachment_id,$meta);}
            ++$added;
        }
        wp_send_json_success(array('next'=>$offset+count($batch),'added'=>$added,'skipped'=>$skipped,'errors'=>$errors));
    }
    public static function save_images() {
        self::ajax_check();
        if (!function_exists('wel_get_item') || !function_exists('wel_update_item_data')) {wp_send_json_error('Welcart画像保存関数がありません。');}
        $code = isset($_POST['code']) ? sanitize_text_field(wp_unslash($_POST['code'])) : '';
        $known=array_column(self::products(),'code');
        if (!in_array($code,$known,true)) { wp_send_json_error('対象外の商品です。'); }
        $id=self::find_post_id($code);
        if (!$id) {wp_send_json_error('移行先に商品が見つかりません。');}
        $raw=isset($_POST['ids']) && is_array($_POST['ids']) ? wp_unslash($_POST['ids']) : array();
        if (count($raw)>30) {wp_send_json_error('画像は30件以下にしてください。');}
        $ids=array_values(array_unique(array_filter(array_map('absint',$raw))));
        foreach ($ids as $media_id) {
            if (get_post_type($media_id)!=='attachment' || !wp_attachment_is_image($media_id) || !get_attached_file($media_id)) {
                wp_send_json_error('画像IDが正しくありません：'.$media_id);
            }
        }
        $item=wel_get_item($id,false);
        if (!is_array($item) || !isset($item['itemPicts'])) {wp_send_json_error('Welcartの商品情報内に itemPicts がありません。');}
        $item['itemPicts']=$ids;
        wel_update_item_data($item,$id);
        clean_post_cache($id);
        // Welcart の取得関数で画像IDを再確認する。保存されなければ成功扱いにしない。
        $stored=wel_get_item($id,false);
        $stored_ids=is_array($stored) && isset($stored['itemPicts']) && is_array($stored['itemPicts']) ? array_values(array_map('intval',$stored['itemPicts'])) : array();
        if ($stored_ids !== $ids) {wp_send_json_error('Welcart画像の保存結果が一致しません。バージョン固有の仕様確認が必要です。');}
        if ($ids && !has_post_thumbnail($id)) {set_post_thumbnail($id,$ids[0]);}
        $images=array();foreach($ids as $media_id){$thumb=wp_get_attachment_image_url($media_id,'thumbnail');$images[]=$thumb?$thumb:wp_get_attachment_url($media_id);}
        $main_id = function_exists('wel_get_main_pict_id') ? (int) wel_get_main_pict_id($id, false) : 0;
        wp_send_json_success(array('images'=>$images, 'images_ids'=>$ids, 'main_id'=>$main_id));
    }
    /** 管理者限定・読取専用：Welcart 側の実際の画像参照を診断する。 */
    public static function diagnose() {
        self::ajax_check();
        $code = isset($_POST['code']) ? sanitize_text_field(wp_unslash($_POST['code'])) : '';
        if (!in_array($code, array_column(self::products(), 'code'), true)) {
            wp_send_json_error('対象外の商品コードです。');
        }
        $post_id = self::find_post_id($code);
        if (!$post_id) { wp_send_json_error('商品の照合ができません。'); }
        $item = wel_get_item($post_id, false);
        $ids = is_array($item) && isset($item['itemPicts']) && is_array($item['itemPicts'])
            ? array_values(array_filter(array_map('absint', $item['itemPicts']))) : array();
        $main_id = function_exists('wel_get_main_pict_id') ? (int) wel_get_main_pict_id($post_id, false) : 0;
        $main_code_id = function_exists('wel_get_main_pict_id_by_code')
            ? (int) wel_get_main_pict_id_by_code($code, false) : 0;
        $checks = array();
        foreach (array_slice($ids, 0, 10) as $image_id) {
            $path = get_attached_file($image_id);
            $checks[] = array(
                'id' => $image_id,
                'is_attachment' => (get_post_type($image_id) === 'attachment' && wp_attachment_is_image($image_id)),
                'file_exists' => is_string($path) && $path !== '' && is_file($path),
                'url' => (string) wp_get_attachment_url($image_id),
            );
        }
        if (empty($ids)) {
            $judgment = '商品画像が未設定です。画像を選ぶ→適用してから再診断してください。';
        } elseif (!$checks[0]['is_attachment'] || !$checks[0]['file_exists']) {
            $judgment = '画像IDに対応するメディア登録か実ファイルがありません。メディア一括登録とファイル配置を確認してください。';
        } elseif ($main_id === $ids[0] && $main_code_id === $ids[0]) {
            $judgment = 'Welcartは先頭の画像をメイン画像と認識しています。画像が表示されない場合は商品一覧の再読込・画像URL・表示処理を確認してください。';
        } else {
            $judgment = '商品画像IDは存在しますが、Welcartのメイン画像取得値が不一致です。保存処理・キャッシュ・Welcart機能の設定を確認してください。';
        }
        wp_send_json_success(array(
            'post_id' => $post_id,
            'code' => $code,
            'item_picts' => $ids,
            'main_id' => $main_id,
            'main_code_id' => $main_code_id,
            'thumbnail_id' => (int) get_post_thumbnail_id($post_id),
            'image_checks' => $checks,
            'judgment' => $judgment,
        ));
    }
    public static function fix_descriptions() {
        self::ajax_check();
        $up=wp_upload_dir(); $new=trailingslashit($up['baseurl']);
        $old=array('https://flolistjapan.jp/wp-content/uploads/','http://flolistjapan.jp/wp-content/uploads/','https://www.flolistjapan.jp/wp-content/uploads/','http://www.flolistjapan.jp/wp-content/uploads/');
        $updated=0;$errors=array();
        foreach (self::products() as $item) {
            $id=self::find_post_id($item['code']);if (!$id) {continue;}
            $post=get_post($id);if (!$post) {continue;}
            $after=str_replace($old,$new,$post->post_content);
            if ($after!==$post->post_content) {
                $result=wp_update_post(wp_slash(array('ID'=>$id,'post_content'=>$after)),true);
                if (is_wp_error($result)) {$errors[]=$item['code'];}else{++$updated;}
            }
        }
        if ($errors) {wp_send_json_error('一部の商品が更新できません：'.implode(', ', $errors));}
        wp_send_json_success(array('updated'=>$updated));
    }
}
WIM_Image_Migration_Helper::setup();
