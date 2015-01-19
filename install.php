<?php

define('ROOT', rtrim(__DIR__, '\\/'));
define('DS', DIRECTORY_SEPARATOR);

session_save_path(ROOT . DS . 'system' . DS . 'log' . DS . 'sessions');
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

    if(trim($_POST['name']) === "") $errors[] = '<p class="message message-error cf"><i class="fa fa-exclamation-triangle"></i> What&rsquo;s your name?</p>';
    if(trim($_POST['email']) === "") {
        $errors[] = '<p class="message message-error cf"><i class="fa fa-exclamation-triangle"></i> What&rsquo;s your email? Mecha need that.</p>';
    } else {
        if( ! filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = '<p class="message message-error cf"><i class="fa fa-exclamation-triangle"></i> Invalid email address.</p>';
        }
    }
    if(trim($_POST['username']) === "") $errors[] = '<p class="message message-error cf"><i class="fa fa-exclamation-triangle"></i> What&rsquo;s your username? Mecha need that.</p>';
    if(trim($_POST['password']) === "") $errors[] = '<p class="message message-error cf"><i class="fa fa-exclamation-triangle"></i> What&rsquo;s your password? Mecha need that' . (trim($_POST['username']) === "" ? ' too' : "") . '.</p>';
    if(trim($_POST['username']) !== "" && preg_match('#[^a-z0-9\-\_]#i', $_POST['username'])) $errors[] = '<p class="message message-error cf"><i class="fa fa-exclamation-triangle"></i> Username can only contain letters, numbers, <code>-</code> and <code>_</code>.</p>';
    if(trim($_POST['password']) !== "" && preg_match('#[^a-z0-9\-\_]#i', $_POST['password'])) $errors[] = '<p class="message message-error cf"><i class="fa fa-exclamation-triangle"></i> Password can only contain letters, numbers, <code>-</code> and <code>_</code>.</p>';

    $_SESSION['meet_mecha'] = $_POST;

    if(count($errors) === 0) {
        $user_file = ROOT . DS . 'system' . DS . 'log' . DS . 'users.txt';
        $data  = $_POST['username'] . ': ';
        $data .= $_POST['password'] . ' (';
        $data .= $_POST['name'] . ':pilot) ';
        $data .= $_POST['email'];
        if( ! file_exists($user_file)) file_put_contents($user_file, $data);
        $_SESSION['mecha_notification'] = '<div class="message message-success cf"><p><i class="fa fa-thumbs-up"></i> Okay. Now you can login with these details&hellip;</p><p><strong>Username:</strong> ' . $_POST['username'] . '<br><strong>Password:</strong> ' . $_POST['password'] . '</p></div>';
        chmod(ROOT . DS . 'cabinet' . DS . 'assets', 0777);
        chmod(ROOT . DS . 'cabinet' . DS . 'plugins', 0777);
        chmod(ROOT . DS . 'cabinet' . DS . 'articles', 0766);
        chmod(ROOT . DS . 'cabinet' . DS . 'pages', 0766);
        chmod(ROOT . DS . 'cabinet' . DS . 'custom', 0766);
        chmod(ROOT . DS . 'cabinet' . DS . 'responses', 0766);
        chmod(ROOT . DS . 'cabinet' . DS . 'scraps', 0766);
        chmod(ROOT . DS . 'cabinet' . DS . 'states', 0766);
        chmod(ROOT . DS . 'system' . DS . 'log' . DS . 'users.txt', 0600);
        unlink(ROOT . DS . 'cabinet' . DS . 'articles' . DS . '.empty');
        unlink(ROOT . DS . 'cabinet' . DS . 'custom' . DS . '.empty');
        unlink(ROOT . DS . 'cabinet' . DS . 'pages' . DS . '.empty');
        unlink(ROOT . DS . 'cabinet' . DS . 'responses' . DS . '.empty');
        unlink(ROOT . DS . 'cabinet' . DS . 'scraps' . DS . '.empty');
        unlink(ROOT . DS . 'cabinet' . DS . 'states' . DS . '.empty');
        unlink(ROOT . DS . 'system' . DS . 'log' . DS . 'sessions' . DS . '.empty');
        unlink(ROOT . DS . 'install.php');
        unset($_SESSION['meet_mecha']);
        unset($_SESSION['token']);
        $base = trim(dirname($_SERVER['SCRIPT_NAME']), '\\/');
        header('Location: http://' . $_SERVER['HTTP_HOST'] . ( ! empty($base) ? '/' . $base . '/' : '/') . 'manager/login');
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
    <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
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
        <span class="grid span-2 form-label"><span>Name</span></span>
        <span class="grid span-4"><input name="name" type="text" class="input-block" value="<?php echo isset($cache['name']) ? $cache['name'] : ""; ?>" autofocus></span>
      </label>
      <label class="grid-group no-gap">
        <span class="grid span-2 form-label"><span>Email</span></span>
        <span class="grid span-4"><input name="email" type="email" class="input-block" value="<?php echo isset($cache['email']) ? $cache['email'] : ""; ?>"></span>
      </label>
      <label class="grid-group no-gap">
        <span class="grid span-2 form-label"><span>Username</span></span>
        <span class="grid span-4"><input name="username" type="text" class="input-block" value="<?php echo isset($cache['username']) ? $cache['username'] : ""; ?>"></span>
      </label>
      <label class="grid-group no-gap">
        <span class="grid span-2 form-label"><span>Password</span></span>
        <span class="grid span-4"><input name="password" type="password" class="input-block" value="<?php echo isset($cache['password']) ? $cache['password'] : ""; ?>"></span>
      </label>
      <div class="grid-group no-gap">
        <span class="grid span-2"></span>
        <span class="grid span-4"><button class="btn btn-action" type="submit"><i class="fa fa-user"></i> Install</button></span>
      </div>
    </form>
  </body>
</html>