<?php
    include_once("storage.php");
    include_once("auth.php");
    include_once("pollstorage.php");
    include_once("userstorage.php");
    include_once('helper.php');

    session_start();
    $user_storage = new UserStorage();
    $contents = new DataStorage();
    $auth = new Auth($user_storage);
    $user = $auth->authenticated_user();
    $content = $contents->findAll();
    usort($content, 'date_compare');
    if (!$auth->is_authenticated()) {
        unset($_SESSION["voted"]);
    }
    if (isset($_POST["vote"])) {
        if (!$auth->is_authenticated()) {
            redirect("login.php");
        }
        $p = $_POST["vote"];
        unset($_SESSION["voted"]);
        redirect("vote.php?id=$p");
    }
    if (isset($_POST["mod"])) {
        if (!$auth->is_authenticated()) {
            redirect("login.php");
        }
        $p = $_POST["mod"];
        unset($_SESSION["voted"]);
        redirect("modify.php?id=$p");
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
    <title>Main</title>
</head>
<body>
    <div class="container">
        <p>Hi, <?=$user["username"] ?? "You are not logged In press login button to login!\r
        If you don't have account yet press register button!"?><br>     
            <?php if (!isset($user["username"])) :?>
                <button><a href="login.php">Login</a></button>
                <button><a href="register.php">Register</a></button>
            <?php elseif ($user["username"] == "admin") :?>
                <button><a href="poll.php">Create poll</a></button>
                <button><a href="logout.php">Logout</a></button>
            <?php else :?>
                <button><a href="logout.php">Logout</a></button>
            <?php endif; ?>
        </p>
        <h1 style="text-align: center;">Main page</h1>
        <div id="description">
            <p>
                This is main page of poll application where authorized users can participate in 
                existing polls and admin who creates, modifies and deletes existing polls!
                To participate in polls you need to login and press <b>Vote/Update</b>. <span class="error">Admin can delete poll in voting section
                and expired ones here.</span>
            </p>
        </div>
        <div>
            <?php if (isset($_SESSION["voted"])) : ?>
                <span class="success">Your vote submitted!</span>
            <?php endif; ?><br>
            Current Polls:
            <form action="" method="post">
                <ol>
                    <?php foreach ($content as $con) : ?>
                        <?php if ($con["deadline"] >= date('Y-m-d')) : ?>
                            <?php if ($user != null && already_voted($con["answers"], $user["username"])) : ?>
                                <?php if ($user != null && $user["username"] == "admin") : ?>
                                    <li>ID:<?=$con["id"]?>, Start: <?=$con["start"]?>, Deadline: <?=$con["deadline"]?><button name="vote" value="<?=$con["id"]?>">Update</button><button name="mod" value="<?=$con["id"]?>">Modify</button></li>
                                <?php else : ?>
                                    <li>ID:<?=$con["id"]?>, Start: <?=$con["start"]?>, Deadline: <?=$con["deadline"]?><button name="vote" value="<?=$con["id"]?>">Update</button></li>
                                <?php endif; ?>
                            <?php else : ?>
                                <?php if ($user != null && $user["username"] == "admin") : ?>
                                    <li>ID:<?=$con["id"]?>, Start: <?=$con["start"]?>, Deadline: <?=$con["deadline"]?><button name="vote" value="<?=$con["id"]?>">Vote</button><button name="mod" value="<?=$con["id"]?>">Modify</button></li>
                                <?php else : ?>
                                    <li>ID:<?=$con["id"]?>, Start: <?=$con["start"]?>, Deadline: <?=$con["deadline"]?><button name="vote" value="<?=$con["id"]?>">Vote</button></li>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ol>
            </form>
        </div>
        <div>
            Expired polls
            <ul>
                <?php foreach ($content as $con) : ?>
                    <?php if ($con["deadline"] < date('Y-m-d')) : ?>
                        <?php if (isset($user["username"]) && $user["username"] == "admin") : ?>
                            <li>ID:<?=$con["id"]?>, Start: <?=$con["start"]?>, Deadline: <?=$con["deadline"]?><a href="delete.php?id=<?=$con["id"]?>"><button>Delete</button></a></li>
                        <?php else : ?>
                            <li>ID:<?=$con["id"]?>, Start: <?=$con["start"]?>, Deadline: <?=$con["deadline"]?></li>
                        <?php endif; ?>
                        <?php foreach ($con["answers"] as $key => $c) : ?>
                            <?=$key. "=" . count($c). ","?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
</body>
</html>