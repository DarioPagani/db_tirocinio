<?php
/**
 * Created by PhpStorm.
 * User: dario
 * Date: 31/01/18
 * Time: 12.02
 */

require_once ($_SERVER["DOCUMENT_ROOT"]) . "/utils/lib.hphp";
require_once ($_SERVER["DOCUMENT_ROOT"]) . "/utils/auth.hphp";

$server = new \mysqli_wrapper\mysqli();

$user = new \auth\User();
$user->is_authorized(\auth\LEVEL_GOOGLE_TEACHER, \auth\User::UNAUTHORIZED_THROW);

// Questo vettore contiene tutti gli errori
$errori = array();

// Nominativo, attenzione se uno passa un vettore entra nell'IF lo stesso :P
if (strlen($_POST["nominativo"]) < 1)
	array_push($errori, "nominativo empty");

$tmp = mb_strlen($_POST["codice_fiscale"]);
if ($tmp !== 11 && $tmp !== 0)
	array_push($errori, "codice fiscale must be 11 chars or must be empty!");

$codice_fisclae = $tmp > 0 ? $_POST["codice_fiscale"] : NULL;

$tmp = mb_strlen($_POST["iva"]);
if ($tmp > 11)
	array_push($errori, "Partita IVA cannot exceed 11 chars!");

$iva = $tmp > 0 ? $_POST["iva"] : NULL;

/*if(strlen($_POST["tipo_gestione"]) < 1)
    array_push($errori, "Tipo Gestione empty!");

if(strlen($_POST["dimensione"]) < 1)
    array_push($errori, "Dimensione empty!");*/

if (strlen($_POST["classificazione"]) < 1)
	array_push($errori, "Classificazione empty!");

if (!is_numeric($_POST["classificazione"]))
	array_push($errori, "Classificazione MUST be an integer!");

if (strlen($_POST["ateco_unique"]) < 1 && strlen($_POST["ateco"]) < 1)
	array_push($errori, "Ateco string nor Ateco numeric id were supplied!");

if (!is_numeric($_POST["ateco"]))
	array_push($errori, "Ateco numeric id MUST be an integer!");

if (mb_strlen($_POST["parolaordine"]) < 8)
	array_push($errori, "Parola d'ordine must be at least 8 chars!");

$sedi = json_decode($_POST["sedi"], true);
if ($sedi === NULL || !is_array($sedi))
	array_push($errori, "Supplied invalid JSON for sedi");

// Se c'è almeno un errore reindirizzo
if (sizeof($errori) > 0)
{
	redirect("aggiungi.php", [
		"errors" => json_encode($errori, JSON_PRETTY_PRINT)
	]);
}

// Controlli 0k, procedere all'inserimento!
$server = new \mysqli_wrapper\mysqli();

$inserimento = $server->prepare("INSERT INTO Azienda(IVA, codiceFiscale, nominativo, parolaOrdine, classificazione, ateco, dimensione, gestione) VALUES
                                      (?, ?, ?, ?, ?, ?, ?, ?);");

$parola_ordine = password_hash($_POST["parolaordine"], PASSWORD_DEFAULT);
$inserimento->bind_param(
	"ssssiiss",
	$iva,
	$codice_fisclae,
	$_POST["nominativo"],
	$parola_ordine,
	$_POST["classificazione"],
	$_POST["ateco"],
	$_POST["dimensione"],
	$_POST["gestione"]
);

try
{
	$inserimento->execute();
}
catch (\mysqli_wrapper\sql_exception $exception)
{
	redirect("aggiungi.php", [
		"errors" => $exception->get_error_list_as_json()
	]);
}


$azienda_id = $inserimento->insert_id;

// Inserimento sedi
$inserimento = $server->prepare(
	"INSERT INTO Sede(azienda, nomeSede, indirizzo, numCivico, comune, provincia, stato, CAP) VALUES 
                            (?, ?, ?, ?, ?, ?, ?, ?)"
);

$errori = array();
foreach ($sedi as $sede)
{
	$inserimento->bind_param(
		"isssssss",
		$azienda_id,
		$sede["nominativo"],
		$sede["indirizzo"],
		$sede["civico"],
		$sede["comune"],
		$sede["provincia"],
		$sede["stato"],
		$sede["cap"]
	);

	try
	{
		$inserimento->execute();
	}
	catch (\mysqli_wrapper\sql_exception $exception)
	{
		$errori[$sede["nominativo"]] = $exception->get_error_list();

	}
}

if (sizeof($errori) > 0)
{
	redirect("aggiungi.php", [
		"errors" => json_encode($errori, JSON_PRETTY_PRINT)
	]);
}

$_SESSION["last_passwd"] = [
	"id" => $azienda_id,
	"passwd" => $_POST["parolaordine"]
];

redirect("aggiunto_mostra_info.php", [
	"id" => $azienda_id
]);