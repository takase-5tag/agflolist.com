<?php
error_log('Test log message from test_log.php - error_log');
trigger_error("This is a test error message from trigger_error()", E_USER_WARNING); // 警告レベルのエラーを発生させる
trigger_error("This is a test notice message from trigger_error()", E_USER_NOTICE); // 注意レベルのメッセージを発生させる
echo 'Check your PHP error logs or wp-content/debug.log. If still nothing, check server PHP settings.';
