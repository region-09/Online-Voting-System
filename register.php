<?php
include_once('storage.php');
include_once('userstorage.php');
include_once('auth.php');
include_once('helper.php');

// functions
$user_storage = new UserStorage();
$auth = new Auth($user_storage);
function validate($post, &$data, &$errors) {
  global $auth;
  // username, password
  if (strlen($post["username"]) > 0) {
    if (str_contains($post["username"], ' ')) {
      $errors["global"] = "Username shouldn't contain spaces!";
    }else {
      $data["username"] = $post["username"];
    }
  }else {
    $errors["global"] = "Username is not given!";
  }
  if (strlen($post["email"]) == 0) {
    $errors["email"] = "Email is not given!";
  }else {
    $email = trim($post["email"]);
    if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
      $errors["email"] = "Email has wrong format!";
    }else {
      $data["email"] = $email;
    }
  }
  if (strlen($post["password"]) == 0) {
    $errors["password"] = "Password is not set!";
  }else if (strlen($post["password2"]) == 0) {
    $errors["password2"] = "Password should be retyped!";
  }else {
    if ($post["password"] != $post["password2"]) {
      $errors["password"] = "Passwords doesn't match!";
    }else {
      $data["password"] = $post["password"];
    }
  }
  if (isset($data["username"])) {
    if ($auth->user_exists($data['username'])) {
      $errors['global'] = "Username already exists!";
    }
  }
  return count($errors) === 0;
}
// main
$errors = [];
$data = [];
if (count($_POST) > 0) {
  if (isset($_POST["main"])) {
    redirect('main.php');
  }
  if (validate($_POST, $data, $errors)) {
    $auth->register($data);
    redirect('login.php');
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style.css">
  <title>Registration</title>
</head>
<body>

  <form action="" method="post" id="vote" novalidate>
    <h1>Registration</h1>
    <?php if (isset($errors['global'])) : ?>
      <p><span class="error"><?= $errors['global'] ?></span></p>
    <?php endif; ?>
    <div>
      <label for="username">Username: </label><br>
      <input type="text" name="username" id="username" value="<?= $_POST['username'] ?? "" ?>">
      <?php if (isset($errors['username'])) : ?>
        <span class="error"><?= $errors['username'] ?></span>
      <?php endif; ?>
    </div>

    <div>
      <label for="email">Email: </label><br>
      <input type="email" name="email" value="<?= $_POST['email'] ?? "" ?>">
      <?php if (isset($errors['email'])) : ?>
        <span class="error"><?= $errors['email'] ?></span>
      <?php endif; ?>
    </div>

    <div>
      <label for="password">Password: </label><br>
      <input type="password" name="password">
      <?php if (isset($errors['password'])) : ?>
        <span class="error"><?= $errors['password'] ?></span>
      <?php endif; ?>
    </div>

    <div>
      <label for="password2">Repeat password: </label><br>
      <input type="password" name="password2">
      <?php if (isset($errors['password2'])) : ?>
        <span class="error"><?= $errors['password2'] ?></span>
      <?php endif; ?>
    </div>
    <div>
      <button type="submit">Register</button>
      <button name="main">Main</button>
    </div>
  </form>
</body>
</html>