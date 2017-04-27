<?php

function make_directory($path, $permission) {
    if( ! is_dir($path)) {
        mkdir($path, $permission, true);
    } else {
        change_mode($path, $permission);
    }
}

function change_mode($path, $permission) {
    if(file_exists($path)) chmod($path, $permission);
}

function delete_file($path) {
    unlink($path);
}

define('ROOT', rtrim(__DIR__, '\\/'));
define('DS', DIRECTORY_SEPARATOR);

session_start();

$errors = array();

if($_SERVER['REQUEST_METHOD'] === 'POST') {

    if( ! isset($_SESSION['token']) || ! isset($_POST['token'])) {
        $errors[] = '<p>Invalid token.</p>';
    } else {
        if((string) $_SESSION['token'] !== (string) $_POST['token']) {
            $errors[] = '<p>Invalid token.</p>';
        }
    }

    $token = sha1(uniqid(mt_rand(), true));
    $_SESSION['token'] = $token;

    if(trim($_POST['name']) === "") $errors[] = '<p><i class="fa fa-exclamation-triangle"></i> What&rsquo;s your name?</p>';
    if(trim($_POST['email']) === "") {
        $errors[] = '<p><i class="fa fa-exclamation-triangle"></i> What&rsquo;s your email? Mecha need that.</p>';
    } else {
        if( ! filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = '<p><i class="fa fa-exclamation-triangle"></i> Invalid email address.</p>';
        }
    }
    if(trim($_POST['user']) === "") $errors[] = '<p><i class="fa fa-exclamation-triangle"></i> What&rsquo;s your user name? Mecha need that.</p>';
    if(trim($_POST['pass']) === "") $errors[] = '<p><i class="fa fa-exclamation-triangle"></i> What&rsquo;s your pass word? Mecha need that' . (trim($_POST['user']) === "" ? ' too' : "") . '.</p>';
    if(trim($_POST['user']) !== "" && preg_match('#[^\w-]#i', $_POST['user'])) $errors[] = '<p><i class="fa fa-exclamation-triangle"></i> User name can only contain letters, numbers, <code>_</code> and <code>-</code>.</p>';
    if(trim($_POST['pass']) !== "" && preg_match('#[^\w-]#i', $_POST['pass'])) $errors[] = '<p><i class="fa fa-exclamation-triangle"></i> Pass word can only contain letters, numbers, <code>_</code> and <code>-</code>.</p>';

    $_SESSION['meet_mecha'] = $_POST;

    if(count($errors) === 0) {
        $user_file = ROOT . DS . 'engine' . DS . 'log' . DS . 'users.txt';
        unset($_POST['token']);
        $_POST['status'] = 1;
        if( ! file_exists($user_file)) file_put_contents($user_file, json_encode($_POST));
        $_SESSION['message'] = '<div class="messages p cl cf"><p class="message message-success cl cf"><i class="fa fa-thumbs-up"></i> You can start login now.</p><p class="message message-info cl cf code"><strong>User:</strong> ' . $_POST['user'] . '<br><strong>Pass:</strong> ' . $_POST['pass'] . '</p></div>';
        unset($_SESSION['meet_mecha']);
        unset($_SESSION['token']);
        make_directory(ROOT . DS . 'lot' . DS . 'assets', 0777);
        make_directory(ROOT . DS . 'lot' . DS . 'extends', 0766);
        make_directory(ROOT . DS . 'lot' . DS . 'extends' . DS . 'chunk', 0766);
        make_directory(ROOT . DS . 'lot' . DS . 'extends' . DS . 'custom', 0766);
        make_directory(ROOT . DS . 'lot' . DS . 'extends' . DS . 'substance', 0777);
        make_directory(ROOT . DS . 'lot' . DS . 'plugins', 0777);
        make_directory(ROOT . DS . 'lot' . DS . 'posts' . DS . 'article', 0766);
        make_directory(ROOT . DS . 'lot' . DS . 'posts' . DS . 'page', 0766);
        make_directory(ROOT . DS . 'lot' . DS . 'responses' . DS . 'comment', 0766);
        make_directory(ROOT . DS . 'lot' . DS . 'scraps', 0766);
        make_directory(ROOT . DS . 'lot' . DS . 'shields', 0777);
        make_directory(ROOT . DS . 'lot' . DS . 'states', 0766);
        make_directory(ROOT . DS . 'lot' . DS . 'workers', 0766);
        change_mode(ROOT . DS . 'engine' . DS . 'log' . DS . 'users.txt', 0600);
        delete_file(ROOT . DS . 'engine' . DS . 'plug' . DS . '__.php');
        delete_file(__FILE__); // self destruct ...
        $base = trim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '\\/');
        header('Location: http://' . $_SERVER['HTTP_HOST'] . ( ! empty($base) ? '/' . $base . '/' : '/') . 'manager/login?kick=manager/config');
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
    <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css" rel="stylesheet">
    <style>
* {
  margin:0;
  padding:0;
}
html,
body {overflow:auto}
html {
  font:normal normal 13px/1.3846153846153846em Helmet,FreeSans,Sans-Serif;
  -webkit-font-smoothing:antialiased;
  -moz-osx-font-smoothing:grayscale;
  -webkit-text-size-adjust:100%;
  -moz-text-size-adjust:100%;
  -ms-text-size-adjust:100%;
  text-size-adjust:100%;
}
body {
  background-color:white;
  color:#333;
  padding:5% 0;
}
form {
  display:block;
  max-width:25em;
  margin:0 auto;
  padding:1.3846153846153846em;
}
h3 {
  font-size:160%;
  font-weight:normal;
  margin:0;
}
input,
button {
  -webkit-box-sizing:border-box;
  -moz-box-sizing:border-box;
  box-sizing:border-box;
  display:block;
  width:100%;
  vertical-align:middle;
  padding:.7692307692307693em;
  margin:0;
  font:inherit;
  line-height:normal;
  border:none;
  background-color:#333;
  color:white;
  text-align:left;
}
button {
  width:auto;
  text-align:center;
  cursor:pointer;
  background-color:#365D98;
  text-align:center;
  text-decoration:none;
  color:#FFA;
  padding-left:.9230769230769231em;
  padding-right:.9230769230769231em;
  -webkit-border-radius:2px;
  -moz-border-radius:2px;
  border-radius:2px;
  -webkit-user-select:none;
  -moz-user-select:none;
  -ms-user-select:none;
  user-select:none;
}
button:focus,
button:hover {background-color:#3A64A4}
button:active {background-color:#32568C}
button::-moz-focus-inner {
  marign:0;
  padding:0;
  border:none;
  outline:none;
}
input:invalid {
  color:#FAA;
  -webkit-box-shadow:none;
  -moz-box-shadow:none;
  box-shadow:none;
}
label,
label + div {
  display:block;
  margin:1.3846153846153846em 0 0;
  overflow:hidden;
}
label span,
label + div span {display:block}
label span:first-child,
label + div span:first-child {
  padding:0 0 .7692307692307693em;
  line-height:normal;
}
h3 + div {margin-top:1.3846153846153846em}
h3 + div p {
  margin:0;
  padding:.7692307692307693em .9230769230769231em;
  background-color:#CF3030;
  color:white;
  overflow:hidden;
}
h3 + div p + p {margin-top:2px}
h3 + div p code {opacity:.7}
    </style>
  </head>
  <body>
    <form method="post">
      <input name="token" type="hidden" value="<?php echo isset($token) ? $token : ""; ?>">
      <h3>First Meet</h3>
      <?php $cache = isset($_SESSION['meet_mecha']) ? $_SESSION['meet_mecha'] : array('name' => "", 'user' => "", 'pass' => ""); echo ! empty($errors) ? '<div>' . implode("", $errors) . '</div>' : ""; ?>
      <label>
        <span>Name</span>
        <span><input name="name" type="text" value="<?php echo isset($cache['name']) ? $cache['name'] : ""; ?>" pattern="[^<>]+" required autofocus></span>
      </label>
      <label>
        <span>Email</span>
        <span><input name="email" type="email" value="<?php echo isset($cache['email']) ? $cache['email'] : ""; ?>" pattern="^.+@[^\.].*\.[a-z]{2,}$" required></span>
      </label>
      <label>
        <span>User</span>
        <span><input name="user" type="text" value="<?php echo isset($cache['user']) ? $cache['user'] : ""; ?>" pattern="[\w-]+" required></span>
      </label>
      <label>
        <span>Pass</span>
        <span><input name="pass" type="password" value="<?php echo isset($cache['pass']) ? $cache['pass'] : ""; ?>" pattern="[\w-]+" required></span>
      </label>
      <div>
        <span></span>
        <span><button type="submit"><i class="fa fa-user"></i> Install</button></span>
      </div>
    </form>
  </body>
</html>