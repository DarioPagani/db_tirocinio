<?php
/**
 * Created by PhpStorm.
 * User: dario
 * Date: 12/02/18
 * Time: 18.58
 */

require_once ($_SERVER["DOCUMENT_ROOT"]) . "/utils/lib.hphp";
require_once ($_SERVER["DOCUMENT_ROOT"]) . "/utils/auth.hphp";

$server = new \mysqli_wrapper\mysqli();

$user = new \auth\User();
$user->is_authorized(\auth\LEVEL_GOOGLE_TEACHER, \auth\User::UNAUTHORIZED_REDIRECT);
$user_info = ($user->get_info(new RetriveDocenteFromDatabase($server)));

$google_user = new \auth\GoogleConnection($user); $oauth2 = $google_user->getUserProps();

$permissions = new \auth\PermissionManager($server, $user);
// Variabili pagina
$page = "Tirocini";
?>
<html lang="it">
<head>
    <?php include "../../../utils/pages/head.phtml"; ?>
</head>
<body>
<?php include "../../common/google_navbar.php"; ?>
<br>
<section class="container">
    <div class="columns">
        <aside class="column is-3 is-fullheight">
            <?php
            $index_menu = 1;
            include "../menu.php";
            ?>
        </aside>
        <div class="column">
            <p class="field has-text-right">
                <?php
                if($permissions->check("train.import"))
                {
                    ?>
                    <a class="button is-primary is-large" href="./csv_import">
                        <span class="icon">
                            <i class="fa fa-file-text" aria-hidden="true"></i>
                        </span>
                            <span>
                            Carica da un documento
                        </span>
                    </a>
                    <?php
                }
                if($permissions->check("train.add"))
                {
                    ?>
                    <a class="button is-primary is-large" href="./aggiungi.php">
                        <span class="icon">
                            <i class="fa fa-plus" aria-hidden="true"></i>
                        </span>
                            <span>
                            Crea
                        </span>
                    </a>
                    <?php
                }
                ?>
            </p>
            <div class="columns">
                <div class="column">
                    <article class="box">
                        <h1 class="title">
                            <?php
                            $info = $server->prepare(
                                    "SELECT COUNT(*) FROM Tirocinio WHERE docenteTutore = ? AND (dataTermine<CURRENT_DATE() AND dataTermine IS NOT NULL)");
                            $info->bind_param(
                                    "i",
                                    $user->get_database_id()
                            );
                            $info->execute();
                            $info->bind_result($numero);
                            $info->fetch();
                            echo $numero;
                            $info->close();
                            ?>
                        </h1>
                        <h2 class="subtitle">Terminati</h2>
                    </article>
                </div>
                <div class="column">
                    <article class="box">
                        <h1 class="title">
                            <?php
                            $info = $server->prepare(
                                "SELECT COUNT(*) FROM Tirocinio WHERE docenteTutore = ? AND (CURRENT_DATE()>=dataInizio AND (dataTermine IS NULL OR CURRENT_DATE()<=dataTermine))");
                            $info->bind_param(
                                "i",
                                $user->get_database_id()
                            );
                            $info->execute();
                            $info->bind_result($numero);
                            $info->fetch();
                            echo $numero;
                            $info->close();
                            ?>
                        </h1>
                        <h2 class="subtitle">In corso</h2>
                    </article>
                </div>
                <div class="column">
                    <article class="box">
                        <h1 class="title">
                            <?php
                            $info = $server->prepare(
                                "SELECT COUNT(*) FROM Tirocinio WHERE docenteTutore = ? AND CURRENT_DATE()<dataInizio");
                            $info->bind_param(
                                "i",
                                $user->get_database_id()
                            );
                            $info->execute();
                            $info->bind_result($numero);
                            $info->fetch();
                            echo $numero;
                            $info->close();
                            ?>
                        </h1>
                        <h2 class="subtitle">Futuri</h2>
                    </article>
                </div>
            </div>
			<h4 class="title is-4">Ultime modifiche ai resoconti</h4>

        </div>
    </div>
</section>
<?php include ($_SERVER["DOCUMENT_ROOT"]) . "/utils/pages/footer.phtml"; ?>

<script src="<?= BASE_DIR ?>js/togglePanel.js"></script>
</body>
</html>