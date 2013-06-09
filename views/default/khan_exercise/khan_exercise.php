<?php
$khan_exercise = $vars['entity'];
$baseurl = "http://elgg.khanacademie.nl/mod/genghis/khan-exercises/exercises/nombre_grupo_";
$ex_url = $baseurl . $khan_exercise->question_id;
error_log('ex_url');
error_log($ex_url);
?>
<style>
.view-exercise {
	width: 100%;
}
</style>
<iframe class="view-exercise" src="<?php echo $ex_url; ?>"></iframe>

