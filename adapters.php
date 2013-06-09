<?php
class ElggKhanExercise extends ElggObject {

}
class ElggAdapter {
	public function getUser() {
    }

}

require_once("./libs/sdic_api_client_elearning.class.php");
class SDICAdapter extends SDICApiClientELearning {
    // Gestion de la userkey que nos viene de GEL
    if (isset($_GET['userKey'])) {
        setcookie("user", $_GET['userKey'] , time()+3600);
        header('Location: http://baal.uc3m.es/genghis/');
    }
    if (!isset($_COOKIE['user']) & !isset($_GET['userKey'])) {
        header('Location: http://baal.uc3m.es/gel');
    }

    static function connect() {
        try {
            $api = new SDICAdapter();
            $api->assignKey("18908eee-90a1-11e2-a8a5-005056933b24");
        } catch (Exception $e) {
            echo "Exception: ".$e->getMessage();
        }
    }

}
?>
