<?php
/**
 * Created by Atom.
 * User: Enrico
 * Date: 28/02/18
 * Time: 11.30
 */

$json_mode = true;
$force_silent = true;

require_once ($_SERVER["DOCUMENT_ROOT"]) . "/utils/lib.hphp";
require_once ($_SERVER["DOCUMENT_ROOT"]) . "/utils/auth.hphp";

(new \auth\User())->is_authorized(\auth\LEVEL_GOOGLE_STUDENT, \auth\User::UNAUTHORIZED_THROW);

\auth\connect_token_google($google_client, $_SESSION["user"]["token"], false);

$server = new \mysqli_wrapper\mysqli();
$return = [];
$newDescription = $_POST['contenuto'];

if (empty($newDescription))
{
    echo json_encode([
        "error" => -1,
        "what" => "You have to supply something!"
    ]);
    return;
}

if (empty($_POST['tirocinio']))
{
    echo json_encode([
        "error" => -1,
        "what" => "Invalid tirocinio ID!"
    ]);
    return;
}

$update = $server->prepare("UPDATE Tirocinio SET descrizione=? WHERE id=?");

$update->bind_param('si', $newDescription, $_POST['tirocinio']);

$update->execute();

$return["success"]=$update->execute();
$return["md5"]=md5($newDescription);

echo json_encode($return, JSON_UNESCAPED_UNICODE);
