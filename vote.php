<?php
    include_once("storage.php");
    include_once("pollstorage.php");
    include_once("userstorage.php");
    include_once("auth.php");
    include_once("helper.php");

    session_start();
    $user_storage = new UserStorage();
    $auth = new Auth($user_storage);
    // authentification
    if (!$auth->is_authenticated()) {
        redirect("login.php");
    }
    $id = $_GET["id"];
    $contents = new DataStorage();
    $content = $contents->findById($id);
    if ($content["deadline"] < date('Y-m-d')) {
        redirect("main.php");
    }
    $errors = [];
    $param = [];
    if (isset($_POST["opt"])) {
        $param = $_POST["opt"];
    }
    if (isset($_POST["yes"])) {
        $param = $_POST["yes"];
    }
    foreach ($param as $p) {
        if (!in_array($p, $content["options"])) {
            $errors["global"] = "Not existing option!";
        }
    }
    $user = $auth->authenticated_user();
    if ($user["username"] == "admin" && isset($_POST["delete"])) {
        redirect("delete.php?id=".$id);
    }
    if ($param === [] && count($_POST) > 0) {
        $errors["global"] = "Please select one of the options";
    }
    if (count($errors) == 0 && $param != []) {
        if (already_voted($content["answers"], $user["username"])) {
            $userR = $user["username"];
            foreach ($content["answers"] as $key => $value) {
                global $userR;
                foreach ($value as $k => $v) {
                    if ($v == $userR) {
                        unset($content["answers"][$key][$k]);
                        $content["answers"][$key] = array_values($content["answers"][$key]);
                    }
                }
            }
        }
        if (!in_array($user["username"], $content["voted"])) {
            array_push($content["voted"], $user["username"]);
        }
        foreach ($param as $p) {
            if (!in_array($user["username"], $content["answers"][$p])) {
                array_push($content["answers"][$p], $user["username"]);
            }
        }
        $contents->update($id, $content);
        $_SESSION["voted"] = "Successful vote!";
        redirect("main.php");
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Vote</title>
</head>
<body>
    <form action="" method="post" id="vote" novalidate>
        <h1>Voting Page</h1>
        <p><?=$content["question"]?>?</p>
        <?php if (isset($errors["global"])) :?>
            <span class="error"><?=$errors["global"]?></span><br>
        <?php endif; ?>
        <?php if ($content["can_multiple"]) : ?>
            <?php foreach ($content["options"] as $con) : ?>
                <input type="checkbox" name="opt[]" value="<?=$con?>"><?=$con?><br>
            <?php endforeach; ?>
        <?php else : ?>
            <?php foreach ($content["options"] as $con) : ?>
                <input type="radio" name="yes[]" value="<?=$con?>"><?=$con?><br>
            <?php endforeach; ?>
        <?php endif; ?>
        <button type="submit" name="ok">OK</button>
        <?php if ($user["username"] == "admin") :?>
            <button name="delete" value="del">Delete poll</button>
        <?php endif; ?>
        <p>Deadline:<?=$content["deadline"]?></p>
        <p>Start:<?=$content["start"]?></p>
    </form>
    </body>
</html>