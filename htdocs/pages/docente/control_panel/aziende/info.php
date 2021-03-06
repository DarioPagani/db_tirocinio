<?php
/**
 * Created by PhpStorm.
 * User: dario
 * Date: 03/02/18
 * Time: 20.30
 */

require_once ($_SERVER["DOCUMENT_ROOT"]) . "/utils/lib.hphp";
require_once ($_SERVER["DOCUMENT_ROOT"]) . "/utils/auth.hphp";

$server = new \mysqli_wrapper\mysqli();

$user = new \auth\User();
$user->is_authorized(\auth\LEVEL_GOOGLE_TEACHER, \auth\User::UNAUTHORIZED_REDIRECT);
$user_info = ($user->get_info(new RetriveDocenteFromDatabase($server)));
$permissions = new \auth\PermissionManager($server, $user);

$permissions->check("factory.contacts.create", \auth\PermissionManager::UNAUTHORIZED_REDIRECT);

$google_user = new \auth\GoogleConnection($user); $oauth2 = $google_user->getUserProps();

$info = $server->prepare("SELECT IVA, codiceFiscale, nominativo, C.descrizione, C2.cod2007, dimensione, gestione, no_accessi 
                                  FROM Azienda 
                                  INNER JOIN Classificazioni C ON Azienda.classificazione = C.id
                                  INNER JOIN CodiceAteco C2 ON Azienda.ateco = C2.id
                                  WHERE Azienda.id = ?");

$info->bind_param("i", $_GET["id"]);
$info->execute();
$info->bind_result($iva, $cf, $nome, $classificazione, $ateco_c,$dimensione, $gestione, $no_accesso);
$info->store_result();
if($info->fetch() !== true)
	throw new RuntimeException("Azienda non esistente!", -1);

// Variabili pagina
$page = sanitize_html($nome);

function edit_button()
{
	return;
    ?>
    <a class="button is-small is-fullwidth">
        <span class="icon">
            <i class="fa fa-pencil" aria-hidden="true"></i>
        </span>
        <span>
            Modifica
        </span>
    </a>
    <?php
}
?>
<html lang="it">
<head>
    <?php include ($_SERVER["DOCUMENT_ROOT"]) .  "/utils/pages/head.phtml"; ?>
    <!--<style>
        .edit-button
        {
            width: 18%;
        }
    </style>-->
</head>
<body>
<?php include "../../../common/google_navbar.php"; ?>
<br>
<section class="container">
    <div class="columns">
        <aside class="column is-3 is-fullheight">
            <?php
            $index_menu = 8;
            include "../../menu.php";
            ?>
        </aside>
        <div class="column">
			<p class="control">
				<a class="button is-primary is-pulled-right is-large" href="./aggiungi_contatto.php?id=<?= $_GET["id"] ?>">
					<span class="icon">
						<i class="fa fa-user-plus" aria-hidden="true"></i>
					</span>
					<span>
                        Crea contatto
					</span>
				</a>
			</p>
            <div class="">
                <table class="table is-fullwidth">
                    <tr>
                        <th>Identificativo</th>
                        <td><?= 0 ?></td>
                        <td></td>
                    </tr>
                    <tr>
                        <th>Parola d'Ordine</th>
                        <td>
                            <strong>*****</strong>
                        </td>
                        <td class="edit-button" data-edit="parola_ordine">
                            <?php edit_button() ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Nominativo</th>
                        <td>
                            <?= sanitize_html($nome) ?>
                        </td>
                        <td class="edit-button" data-edit="nominativo">
                            <?php edit_button() ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Parita IVA</th>
                        <td>
                            <?= sanitize_html($iva)?>
                        </td>
                        <td class="edit-button" data-edit="iva">
                            <?php edit_button() ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Codice Fiscale</th>
                        <td>
                            <?= sanitize_html($cf) ?>
                        </td>
                        <td class="edit-button" data-edit="cf">
                            <?php edit_button() ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Classificazione</th>
                        <td>
                            <?= sanitize_html($classificazione) ?>
                        </td>
                        <td class="edit-button" data-edit="classificazione">
                            <?php edit_button() ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Ateco</th>
                        <td>
                            <?= sanitize_html($ateco_c) ?>
                        </td>
                        <td class="edit-button" data-edit="ateco">
                            <?php edit_button() ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Dimensione</th>
                        <td>
                            <?= sanitize_html($dimensione) ?>
                        </td>
                        <td class="edit-button" data-edit="dimensione">
                            <?php edit_button() ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Tipologia gestione</th>
                        <td>
                            <?= sanitize_html($gestione) ?>
                        </td>
                        <td class="edit-button" data-edit="gestione">
                            <?php edit_button() ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Già stato acceduto?</th>
                        <td>
                            <?= sanitize_html($no_accesso) ?>
                        </td>
                        <td class="edit-button" data-edit="access">
                            <?php edit_button() ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</section>

<!-- modifiche -->
<div class="modal" data-edit-modal="parola_ordine">
    <div class="modal-background"></div>
    <div class="modal-card">
        <header class="modal-card-head">
            <p class="modal-card-title">Nuova Parola d'ordine</p>
        </header>
        <section class="modal-card-body">
            
        </section>
        <footer class="modal-card-foot">
            <button class="button is-success">Applica</button>
            <button class="button">Scarta</button>
        </footer>
    </div>
</div>


<script src="js/edit.js"></script>
<?php include ($_SERVER["DOCUMENT_ROOT"]) .  "/utils/pages/footer.phtml"; ?>

</body>
</html>