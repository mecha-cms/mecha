<?php

define('ROOT', rtrim(__DIR__, '\\/'));
define('DS', DIRECTORY_SEPARATOR);

session_start();

$errors = array();

if($_SERVER['REQUEST_METHOD'] == 'POST') {

    if( ! isset($_SESSION['token']) || ! isset($_POST['token'])) {
        $errors[] = '<p class="message message-error cf">Invalid token.</p>';
    } else {
        if((string) $_SESSION['token'] !== (string) $_POST['token']) {
            $errors[] = '<p class="message message-error cf">Invalid token.</p>';
        }
    }

    $token = sha1(uniqid(mt_rand(), true));
    $_SESSION['token'] = $token;

    if(empty($_POST['name'])) $errors[] = '<p class="message message-error cf"><i class="fa fa-exclamation-triangle"></i> What&rsquo;s your name?</p>';
    if(empty($_POST['username'])) $errors[] = '<p class="message message-error cf"><i class="fa fa-exclamation-triangle"></i> What&rsquo;s your username? Mecha need that.</p>';
    if(empty($_POST['password'])) $errors[] = '<p class="message message-error cf"><i class="fa fa-exclamation-triangle"></i> What&rsquo;s your password? Mecha need that' . (empty($_POST['username']) ? ' too' : "") . '.</p>';
    if( ! empty($_POST['username']) && preg_match('#[^a-z0-9]#i', $_POST['username'])) $errors[] = '<p class="message message-error cf"><i class="fa fa-exclamation-triangle"></i> Username should contains only letters and numbers.</p>';
    if( ! empty($_POST['password']) && preg_match('#[^a-z0-9]#i', $_POST['password'])) $errors[] = '<p class="message message-error cf"><i class="fa fa-exclamation-triangle"></i> Password should contains only letters and numbers.</p>';

    $_SESSION['meet_mecha'] = $_POST;

    if(count($errors) === 0) {
        $user_file = ROOT . DS . 'system' . DS . 'log' . DS . 'users.txt';
        $data  = $_POST['username'] . ': ';
        $data .= $_POST['password'] . ' (';
        $data .= $_POST['name'] . ':pilot)';
        if( ! file_exists($user_file)) file_put_contents($user_file, $data);
        $_SESSION['mecha_notification'] = '<div class="message message-success cf"><p><i class="fa fa-thumbs-up"></i> Okay. Now you can login with this details:</p><p><strong>Username:</strong> ' . $_POST['username'] . '<br><strong>Password:</strong> ' . $_POST['password'] . '</p></div>';
        chmod(ROOT . DS . 'cabinet' . DS . 'assets', 0777);
        chmod(ROOT . DS . 'cabinet' . DS . 'plugins', 0777);
        chmod(ROOT . DS . 'cabinet' . DS . 'articles', 0766);
        chmod(ROOT . DS . 'cabinet' . DS . 'pages', 0766);
        chmod(ROOT . DS . 'cabinet' . DS . 'custom', 0766);
        chmod(ROOT . DS . 'cabinet' . DS . 'responses', 0766);
        chmod(ROOT . DS . 'cabinet' . DS . 'scraps', 0766);
        chmod(ROOT . DS . 'cabinet' . DS . 'states', 0766);
        chmod(ROOT . DS . 'system' . DS . 'log' . DS . 'users.txt', 0600);
        unlink(ROOT . DS . 'install.php');
        unset($_SESSION['meet_mecha']);
        unset($_SESSION['token']);
        header('Location: http://' . $_SERVER['HTTP_HOST'] . '/' . trim(dirname($_SERVER['SCRIPT_NAME']), '\\/') . '/manager/login');
        exit;
    }

} else {

    $token = sha1(uniqid(mt_rand(), true));
    $_SESSION['token'] = $token;

}

?>
<!DOCTYPE html>
<html dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Hi!</title>
    <link href="favicon.ico" rel="shortcut icon" type="image/x-icon">
    <link href="cabinet/shields/normal/shell/atom.css" rel="stylesheet">
    <link href="//netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">
    <style>
    html,body {overflow:auto}
    body {padding:5%}
    form {
      width:360px;
      padding:1.3846153846153846em;
      margin:0 auto;
      border:1px solid;
      background-color:white;
    }
    form h3 {margin:0 0 18px}
    </style>
  </head>
  <body>
    <form method="post">
      <input name="token" type="hidden" value="<?php echo isset($token) ? $token : ""; ?>">
      <h3>First Meet</h3>
      <?php $cache = isset($_SESSION['meet_mecha']) ? $_SESSION['meet_mecha'] : array('name' => "", 'username' => "", 'password' => ""); echo ! empty($errors) ? '<div class="messages">' . implode("", $errors) . '</div>' : ""; ?>
      <label class="grid-group no-gap">
        <span class="grid span-2 form-label"><span>Your name</span></span>
        <span class="grid span-4"><input name="name" type="text" class="input-block" value="<?php echo isset($cache['name']) ? $cache['name'] : ""; ?>" autofocus></span>
      </label>
      <label class="grid-group no-gap">
        <span class="grid span-2 form-label"><span>Username</span></span>
        <span class="grid span-4"><input name="username" type="text" class="input-block" value="<?php echo isset($cache['username']) ? $cache['username'] : ""; ?>"></span>
      </label>
      <label class="grid-group no-gap">
        <span class="grid span-2 form-label"><span>Password</span></span>
        <span class="grid span-4"><input name="password" type="text" class="input-block" value="<?php echo isset($cache['password']) ? $cache['password'] : ""; ?>"></span>
      </label>
      <div class="grid-group no-gap">
        <span class="grid span-2"></span>
        <span class="grid span-4"><button class="btn btn-primary btn-install" type="submit"><i class="fa fa-user"></i> Install</button></span>
      </div>
    </form>
  </body>
</html>