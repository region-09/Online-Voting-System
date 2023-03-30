<?php
    include_once("storage.php");
    include_once("pollstorage.php");
    include_once("auth.php");
    include_once("helper.php");

    session_start();
    if (isset($_SESSION["voted"])) {
        unset($_SESSION["voted"]);
    }
    $polls = new DataStorage();
    $auth = new Auth($polls);
    if (!$auth->is_authenticated()) {
        redirect("login.php");
    }

    $user = $auth->authenticated_user();

    if (isset($user["username"]) && $user["username"] != "admin") {
        redirect("main.php");
    }

    $id = $_GET["id"];
    $content = $polls->findById($id);
    if ($content != null) {
        $polls->delete($id);
    }
    redirect("main.php");
?>