<?php
    include_once("storage.php");
    include_once("auth.php");
    include_once("pollstorage.php");
    include_once("userstorage.php");
    include_once('helper.php');
    
    session_start();
    if (isset($_SESSION["voted"])) {
        unset($_SESSION["voted"]);
    }
    $user_storage = new UserStorage();
    $auth = new Auth($user_storage);
    if (!$auth->is_authenticated()) {
        redirect("login.php");
    }
    if ($auth->authenticated_user()["username"] != "admin") {
        redirect("main.php");
    }
    $id = $_GET["id"];
    $polls = new DataStorage();
    $content = $polls->findById($id);
    if ($content["deadline"] < date('Y-m-d')) {
        redirect("main.php");
    }
    //functions
    function validateDate($deadline, $format = 'Y-m-d') {
        $d = DateTime::createFromFormat($format, $deadline);
        return $d && $d->format($format) == $deadline;
    }

    function validate($post, &$data, &$errors) {
        // Question part
        if (!isset($post["question"])) {
            $errors["question"] = "Question is not given!";
        }else if (strlen(trim($post["question"])) == 0) {
            $errors["question"] = "Question is empty!";
        }else {
            $data["question"] = $post["question"];
        }
        // multiple option can be selected part
        if (!isset($post["can_multiple"])) {
            $errors["can_multiple"] = "'Can multiple options be selected' is not set!";
        }else if ($post["can_multiple"] == "Yes" || $post["can_multiple"] == "No") {
            $data["can_multiple"] = $post["can_multiple"] == "Yes" ? true : false;
        }else {
            $errors["can_multiple"] = "Can multiple option selected error!";
        }
        // Options part
        if (!isset($post["options"])) {
            $errors["options"] = "Options not set!";
        }else {
            $options = explode("\n", $post["options"]);
            $options_updated = [];
            for ($i = 0; $i < count($options); $i++) {
                if (strlen(trim($options[$i])) != 0) {
                    array_push($options_updated, trim($options[$i]));
                }
            }
            // When we have less than 2 options
            if (count($options_updated) < 2) {
                $errors["options"] = "Options can not be less than 2";
            }
            $data["options"] = $options_updated;
        }
        // deadline validation
        if (!isset($post["deadline"])) {
            $errors["deadline"] = "Deadline is not set!";
        }else {
            if (strlen($post["deadline"]) == 0) {
                $errors["deadline"] = "Deadline is not set!";
            }else if (validateDate($post["deadline"])) {
                $data["deadline"] = $post["deadline"];
                if ($post["deadline"] < date('Y-m-d')) {
                    $errors["deadline"] = "Deadline is in the past!";
                }
            }else {
                $errors["deadline"] = "deadline format error!";
            }
        }
        if (count($errors) > 0) {
            return false;
        }
        return true;
    }
    $data = [];
    $errors = [];
    if (count($_POST) > 0) {
        if (validate($_POST, $data, $errors)) {
            $data["start"] = $content["start"];
            $data["voted"] = [];
            $d = [];
            foreach ($data["options"] as $key) {
                $d += ["$key" => []];
            }
            if (!is_same($data["options"], $content["options"]) || $content["can_multiple"] != $data["can_multiple"]) {
                $data["answers"] = $d;
            }else {
                $data["answers"] = $content["answers"];
            }
            $data["id"] = $id;
            $polls->update($id, $data);
            redirect("main.php");
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
    <title>Poll Creation</title>
</head>
<body>
    <div>
        <form action="" method="post" novalidate>
            <h1>Poll modification page</h1>
            <?php if (isset($errors["question"])) : ?>
                <span class="error"><?=$errors["question"]?></span><br>
            <?php endif; ?>
            Question:<br> 
            <input type="text" name="question" style="width: 700px;" value="<?=$content["question"]?>"><br>
            <?php if (isset($errors["options"])) : ?>
                <span class="error"><?=$errors["options"]?></span><br>
            <?php endif; ?>
            Options:<br>
            <textarea name="options"  cols="109" rows="10"><?php foreach ($content["options"] as $con) : ?><?=$con."\r\n"?><?php endforeach; ?>
            </textarea><br>
            <?php if (isset($errors["can_multiple"])) : ?>
                <span class="error"><?=$errors["can_multiple"]?></span><br>
            <?php endif; ?>
            <label for="can_multiple">Multiple options can be selected:</label><br>
            <?php if ($content["can_multiple"] == "Yes") : ?>
                <input type="radio" name="can_multiple" value="Yes" checked>Yes</input><br>
                <input type="radio" name="can_multiple" value="No">No</input><br>
            <?php else : ?>
                <input type="radio" name="can_multiple" value="Yes">Yes</input><br>
                <input type="radio" name="can_multiple" value="No" checked>No</input><br>
            <?php endif; ?>
            <?php if (isset($errors["deadline"])) : ?>
                <span class="error"><?=$errors["deadline"]?></span><br>
            <?php endif; ?>
            Deadline: <input type="date" name="deadline" value="<?=$content["deadline"]?>"><br>
            <button type="submit">Ok</button>
        </form>
    </div>
</body>
</html>