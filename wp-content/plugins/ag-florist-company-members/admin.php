<?php
if (!defined('ABSPATH')) {
  exit;
}

add_action('admin_menu', function () {
  add_menu_page(
    '企業別掛け率管理',
    '企業別掛け率',
    AGCM_MANAGE_CAP,
    'agcm-companies',
    'agcm_admin_page',
    'dashicons-groups',
    4.9
  );
});
add_action('admin_enqueue_scripts', function ($hook) {
  if (isset($_GET['page']) && $_GET['page'] === 'agcm-companies') {
    wp_enqueue_style('agcm-admin', AGCM_URL . 'assets/admin.css', array(), AGCM_VERSION);
    wp_enqueue_script('agcm-qr', AGCM_URL . 'assets/qr.js', array(), AGCM_VERSION, true);
    wp_enqueue_script('agcm-admin', AGCM_URL . 'assets/admin.js', array('agcm-qr'), AGCM_VERSION, true);
  }
});
function agcm_admin_url($id = 0, $message = '')
{
  $args = array('page' => 'agcm-companies');
  if ($id) {
    $args['company_id'] = (int)$id;
  }
  if ($message) {
    $args['agcm_notice'] = $message;
  }
  return add_query_arg($args, admin_url('admin.php'));
}
function agcm_admin_redirect($id, $notice)
{
  wp_safe_redirect(agcm_admin_url($id, $notice));
  exit;
}
function agcm_require_admin($action)
{
  if (!current_user_can(AGCM_MANAGE_CAP)) {
    wp_die('この操作の権限がありません。', '', array('response' => 403));
  }
  check_admin_referer($action);
}
add_action('admin_post_agcm_save_company', function () {
  agcm_require_admin('agcm_save_company');
  $id = isset($_POST['company_id']) ? absint($_POST['company_id']) : 0;
  if ($id && !agcm_company($id)) {
    wp_die('企業データが見つかりません。');
  }
  $name = isset($_POST['company_name']) && is_string($_POST['company_name'])
    ? sanitize_text_field(wp_unslash($_POST['company_name'])) : '';
  if ($name === '') {
    agcm_admin_redirect($id, 'name_required');
  }
  if ($id) {
    $res = wp_update_post(array('ID' => $id, 'post_title' => $name), true);
  } else {
    $res = wp_insert_post(array(
      'post_type' => 'agcm_company',
      'post_status' => 'private',
      'post_title' => $name,
      'post_author' => get_current_user_id()
    ), true);
  }
  if (is_wp_error($res) || !$res) {
    wp_die('企業情報を保存できませんでした。');
  }
  $id = (int)$res;
  if (!get_post_meta($id, '_agcm_token', true)) {
    update_post_meta($id, '_agcm_token', agcm_token());
  }
  update_post_meta($id, '_agcm_enabled', isset($_POST['enabled']) ? '1' : '0');
  // Only accept SKU keys from the product list, not arbitrary posted prices or keys.
  $posted = isset($_POST['rates']) && is_array($_POST['rates']) ? wp_unslash($_POST['rates']) : array();
  $rates = array();
  $existing_rates = $id ? get_post_meta($id, '_agcm_rates', true) : array();
  if (!is_array($existing_rates)) {
    $existing_rates = array();
  }
  foreach (agcm_products() as $product) {
    $key = agcm_rate_key($product['post_id'], $product['sku']);
    // MBFF rate is fixed by the server; ignore submitted fields and
    // preserve any pre-existing historical entry for a future rollback.
    if (function_exists('agcm_is_mbff_product') && agcm_is_mbff_product($product['post_id'])) {
      if (isset($existing_rates[$key]) && is_numeric($existing_rates[$key])) {
        $rates[$key] = $existing_rates[$key];
      }
      continue;
    }
    $raw = $posted[sha1($key)] ?? ''; // HTML field names cannot safely contain arbitrary SKU brackets.
    if (!is_scalar($raw)) {
      continue;
    }
    $value = trim((string)$raw);
    if ($value === '' || !is_numeric($value)) {
      continue;
    }
    $percent = (float)$value;
    // Unconfigured is 100%; 0-100 accepted, one decimal place maximum.
    if ($percent < 0 || $percent > 100) {
      continue;
    }
    if ($percent < 100) {
      $rates[$key] = round($percent, 1);
    }
  }
  update_post_meta($id, '_agcm_rates', $rates);
  agcm_admin_redirect($id, 'saved');
});
add_action('admin_post_agcm_rotate_invite', function () {
  agcm_require_admin('agcm_rotate_invite');
  $id = isset($_POST['company_id']) ? absint($_POST['company_id']) : 0;
  if (!agcm_company($id)) {
    wp_die('企業データが見つかりません。');
  }
  update_post_meta($id, '_agcm_token', agcm_token());
  agcm_admin_redirect($id, 'rotated');
});
add_action('admin_post_agcm_assign_member', function () {
  agcm_require_admin('agcm_assign_member');
  $id = isset($_POST['company_id']) ? absint($_POST['company_id']) : 0;
  $member_id = isset($_POST['member_id']) ? absint($_POST['member_id']) : 0;
  $mode = (isset($_POST['mode']) && $_POST['mode'] === 'remove') ? 'remove' : 'assign';
  if (!agcm_company($id) || !$member_id || !function_exists('wel_get_member')) {
    wp_die('指定内容を確認してください。');
  }
  $member = wel_get_member($member_id);
  if (!$member || !is_array($member)) {
    agcm_admin_redirect($id, 'member_missing');
  }
  global $usces;
  if (!$usces || !method_exists($usces, 'set_member_meta_value')) {
    wp_die('Welcartの会員機能が利用できません。');
  }
  if ($mode === 'remove') {
    if (agcm_member_company_id($member_id) !== $id) {
      wp_die('所属する企業が一致しません。');
    }
    $usces->set_member_meta_value('agcm_company_id', '', $member_id);
    // Do not clear csmb_company (older invoices can use it).
    agcm_admin_redirect($id, 'unassigned');
  }
  $usces->set_member_meta_value('agcm_company_id', (string)$id, $member_id);
  $usces->set_member_meta_value('agcm_member_type', 'company', $member_id);
  $usces->set_member_meta_value('csmb_company', get_the_title($id), $member_id);
  agcm_admin_redirect($id, 'assigned');
});
function agcm_company_members($id)
{
  global $wpdb;
  $table = $wpdb->prefix . 'usces_member_meta';
  // Prepared query: postmeta != Welcart member_meta.
  $ids = $wpdb->get_col($wpdb->prepare("SELECT DISTINCT member_id FROM {$table} WHERE meta_key = %s AND meta_value = %s ORDER BY member_id DESC LIMIT 500", 'agcm_company_id', (string)(int)$id));
  return is_array($ids) ? array_map('intval', $ids) : array();
}
/** Use Welcart's stored login e-mail, not a sometimes incomplete member array. */
function agcm_member_email($member_id, $member = array())
{
  global $wpdb;
  $table = $wpdb->prefix . 'usces_member';
  $value = $wpdb->get_var($wpdb->prepare(
    "SELECT mem_email FROM {$table} WHERE ID = %d LIMIT 1",
    (int)$member_id
  ));
  if (is_string($value) && is_email($value)) {
    return $value;
  }
  foreach (array('mailaddress1', 'email', 'mem_email') as $key) {
    if (!empty($member[$key]) && is_email($member[$key])) {
      return (string)$member[$key];
    }
  }
  return '';
}

function agcm_admin_page()
{
  if (!current_user_can(AGCM_MANAGE_CAP)) {
    return;
  }
  $id = isset($_GET['company_id']) ? absint($_GET['company_id']) : 0;
  $company = $id ? agcm_company($id) : null;
  $notice = isset($_GET['agcm_notice']) && is_string($_GET['agcm_notice']) ? sanitize_key($_GET['agcm_notice']) : '';
  $notices = array(
    'saved' => '企業情報と掛け率を保存しました。',
    'rotated' => '登録URLを再発行しました。古いURLは無効になりました。',
    'assigned' => 'スタッフの所属企業を更新しました。',
    'unassigned' => 'スタッフの所属を解除しました。',
    'name_required' => '企業名を入力してください。',
    'member_missing' => 'Welcart会員IDを確認してください。'
  );
  $all = agcm_companies();
?>
  <div class="wrap agcm-admin">
    <div class="agcm-heading">
      <div>
        <p class="agcm-eyebrow">AG FLOLIST / BUSINESS MEMBERS</p>
        <h1>企業別会員・掛け率管理</h1>
        <p class="description">企業を登録すると、専用会員登録URLが発行されます。登録スタッフには企業別のSKU掛け率を適用します。</p>
      </div>
      <a href="<?php echo esc_url(agcm_admin_url()); ?>" class="button button-primary">＋ 新しい企業を登録</a>
    </div>
    <?php if (isset($notices[$notice])) : ?><div class="notice notice-<?php echo in_array($notice, array('name_required', 'member_missing'), true) ? 'error' : 'success'; ?> is-dismissible">
        <p><?php echo esc_html($notices[$notice]); ?></p>
      </div><?php endif; ?>
    <nav class="agcm-companies-nav" aria-label="企業の切り替え">
      <a class="<?php echo !$id ? 'is-selected' : ''; ?>" href="<?php echo esc_url(agcm_admin_url()); ?>">＋ 新規企業</a>
      <?php foreach ($all as $co) : ?><a class="<?php echo $id === (int)$co->ID ? 'is-selected' : ''; ?>" href="<?php echo esc_url(agcm_admin_url($co->ID)); ?>"><?php echo esc_html($co->post_title); ?></a><?php endforeach; ?>
    </nav>
    <?php if ($id && !$company) : ?><div class="notice notice-error">
        <p>企業が見つかりません。</p>
      </div><?php return;
          endif; ?>
    <?php if (!$company && $all) : ?>
      <div class="agcm-card">
        <h2>登録済み企業</h2>
        <div class="agcm-list">
          <?php foreach ($all as $co): ?>
            <a href="<?php echo esc_url(agcm_admin_url($co->ID)); ?>"><strong><?php echo esc_html($co->post_title); ?></strong>
              <span class="agcm-status <?php echo agcm_enabled($co->ID) ? 'is-active' : ''; ?>"><?php echo agcm_enabled($co->ID) ? '招待受付中' : '招待停止中'; ?></span><span>編集 →</span></a>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="agcm-form">
      <input type="hidden" name="action" value="agcm_save_company">
      <input type="hidden" name="company_id" value="<?php echo esc_attr($id); ?>">
      <?php wp_nonce_field('agcm_save_company'); ?>
      <section class="agcm-card">
        <div class="agcm-card-title"><span class="agcm-number">01</span>
          <h2>企業情報</h2>
        </div>
        <div class="agcm-field"><label for="agcm-name">企業名・サロン名</label><input type="text" required maxlength="150" id="agcm-name" name="company_name" value="<?php echo esc_attr($company ? $company->post_title : ''); ?>" placeholder="例：株式会社ABCサロン" class="regular-text"></div>
        <label class="agcm-check"><input type="checkbox" name="enabled" value="1" <?php checked(!$company || agcm_enabled($id)); ?>><span>新規スタッフの登録を受け付ける</span></label>
        <p class="description">チェックを外すと、その企業専用URLからの新規会員登録を停止します。既存スタッフの企業別価格設定は維持されます。</p>
      </section>
      <?php if ($company) : ?>
        <section class="agcm-card">
          <div class="agcm-card-title"><span class="agcm-number">02</span>
            <h2>企業専用の新規会員登録URL</h2>
          </div>
          <p>QRコードやスタッフ向け案内には下記URLを使用してください。通常の会員登録画面は公開されません。</p>
          <div class="agcm-url-row"><input id="agcm-url" type="text" readonly value="<?php echo esc_attr(agcm_invite_url($id)); ?>" aria-label="企業専用登録URL"><button type="button" class="button agcm-copy" data-copy-target="agcm-url">URLをコピー</button></div>
          <p class="description">※ URLは企業内の登録担当者にのみ共有してください。URLを知っている方は登録できるため、QRコードの公開掲示にはご注意ください。</p>
          <div class="agcm-qrbox">
            <div id="agcm-qr-preview" aria-label="企業専用登録QRコード"></div>
            <div><button type="button" id="agcm-build-qr" class="button">QRコードを生成</button><a id="agcm-qr-save" class="button" download="ag-FLOLIST-company-<?php echo esc_attr($id); ?>.svg" hidden>QRコードを保存（SVG）</a>
              <p class="description">QRコードはこのブラウザ内だけで生成します。登録用URLを外部サービスに送信しません。</p>
            </div>
          </div>
        </section>
      <?php endif; ?>
      <section class="agcm-card">
        <div class="agcm-card-title"><span class="agcm-number"><?php echo $company ? '03' : '02'; ?></span>
          <h2>商品別の掛け率設定</h2>
        </div>
        <p><strong>MBFF（カテゴリーID 5・15・16）は、QR会員・固定会員とも通常売価の70％に固定</strong>します。MBFF以外は従来どおり企業別SKU掛け率を設定できます。端数は1円未満切り捨てです。</p>
        <div class="agcm-product-table-wrap">
          <table class="widefat striped agcm-product-table">
            <thead>
              <tr>
                <th>商品名</th>
                <th>SKUコード</th>
                <th>通常価格</th>
                <th>掛け率</th>
                <th>企業販売価格（目安）</th>
              </tr>
            </thead>
            <tbody>
              <?php $products = agcm_products();
              foreach ($products as $product) :
                $key = agcm_rate_key($product['post_id'], $product['sku']);
                $is_mbff = function_exists('agcm_is_mbff_product') && agcm_is_mbff_product($product['post_id']);
                $rate = $is_mbff ? 70 : ($company ? agcm_rate($id, $product['post_id'], $product['sku']) : 100);
                $base = $product['price']; ?>
                <tr>
                  <td class="agcm-product-name"><?php echo esc_html($product['product']); ?></td>
                  <td><code><?php echo esc_html($product['sku']); ?></code></td>
                  <td>¥<?php echo esc_html(number_format($base)); ?></td>
                  <td>
                    <div class="agcm-rate-wrap">
                      <?php if ($is_mbff) : ?>
                        <strong class="agcm-fixed-mbff" aria-label="MBFF専用掛け率70％固定">70％（固定）</strong>
                      <?php else : ?>
                        <input type="number" class="agcm-rate" name="rates[<?php echo esc_attr(sha1($key)); ?>]" min="0" max="100" step="0.1" value="<?php echo esc_attr($rate); ?>" data-base="<?php echo esc_attr($base); ?>" aria-label="<?php echo esc_attr($product['product'] . ' の掛け率'); ?>"><span>％</span>
                      <?php endif; ?>
                    </div>
                  </td>
                  <td class="agcm-calculated" aria-live="polite">¥<?php echo esc_html(number_format(floor($base * $rate / 100))); ?></td>
                </tr>
              <?php endforeach; ?>
              <?php if (!$products) : ?><tr>
                  <td colspan="5">Welcartの商品が取得できませんでした。商品カテゴリー「item」とSKU登録をご確認ください。</td>
                </tr><?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>
      <div class="agcm-actions"><button type="submit" class="button button-primary button-hero"><?php echo $company ? '企業情報・掛け率を保存' : '企業を登録してURLを発行'; ?></button></div>
    </form>
    <?php if ($company) : ?>
      <section class="agcm-card">
        <div class="agcm-card-title"><span class="agcm-number">04</span>
          <h2>所属スタッフ</h2>
        </div>
        <p>QRコード経由で登録された会員は自動でこの企業に所属します。既存会員はWelcartの会員IDから追加できます。</p>
        <p class="description">表示金額は、下記の所属会員がWelcartでログインした場合に適用されます。WordPress管理者ログインのみでは適用されません。</p>
        <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" class="agcm-add-member">
          <input type="hidden" name="action" value="agcm_assign_member"><input type="hidden" name="mode" value="assign">
          <input type="hidden" name="company_id" value="<?php echo esc_attr($id); ?>"><?php wp_nonce_field('agcm_assign_member'); ?>
          <label for="agcm-mid">既存Welcart会員ID</label><input id="agcm-mid" type="number" name="member_id" min="1" required placeholder="例：123"><button class="button">所属企業に追加・移動</button>
        </form>
        <table class="widefat striped agcm-member-table">
          <thead>
            <tr>
              <th>会員ID</th>
              <th>会員氏名</th>
              <th>メールアドレス</th>
              <th>所属管理</th>
            </tr>
          </thead>
          <tbody>
            <?php $member_ids = agcm_company_members($id);
            foreach ($member_ids as $member_id) :
              $member = function_exists('wel_get_member') ? wel_get_member($member_id) : false;
              if (!$member) {
                continue;
              }
              $person = trim((string)($member['name1'] ?? '') . ' ' . (string)($member['name2'] ?? ''));
              $email = agcm_member_email($member_id, $member); ?>
              <tr>
                <td><?php echo esc_html($member_id); ?></td>
                <td><?php echo esc_html($person); ?></td>
                <td><?php echo esc_html($email); ?></td>
                <td>
                  <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post"><input type="hidden" name="action" value="agcm_assign_member"><input type="hidden" name="mode" value="remove"><input type="hidden" name="company_id" value="<?php echo esc_attr($id); ?>"><input type="hidden" name="member_id" value="<?php echo esc_attr($member_id); ?>"><?php wp_nonce_field('agcm_assign_member'); ?><button type="submit" class="button button-small" onclick="return confirm('この会員の企業所属を解除しますか？');">所属解除</button></form>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (!$member_ids) : ?><tr>
                <td colspan="4">この企業に所属するスタッフはまだ登録されていません。</td>
              </tr><?php endif; ?>
          </tbody>
        </table>
      </section>
      <section class="agcm-card agcm-danger">
        <div class="agcm-card-title">
          <h2>登録URLの再発行</h2>
        </div>
        <p>共有済みのURLが外部に漏れた場合は再発行してください。古いURLだけでなく、そのURLから発行された登録用セッションも無効になります。</p>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" onsubmit="return confirm('現在の企業専用URLを無効化して再発行しますか？');">
          <input type="hidden" name="action" value="agcm_rotate_invite"><input type="hidden" name="company_id" value="<?php echo esc_attr($id); ?>"><?php wp_nonce_field('agcm_rotate_invite'); ?><button class="button">URLを無効化して再発行</button>
        </form>
      </section>
    <?php endif; ?>
  </div>
<?php
}
