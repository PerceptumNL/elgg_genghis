<?php
/**
 * Save blog entity
 *
 * Can be called by clicking save button or preview button. If preview button,
 * we automatically save as draft. The preview button is only available for
 * non-published drafts.
 *
 * Drafts are saved with the access set to private.
 *
 * @package Blog
 */

// start a new sticky form session in case of failure
elgg_make_sticky_form('khan_exercise');
require(dirname(dirname(dirname(__file__)))."/lib/khan_exercise.php");

// save or preview
$save = (bool)get_input('save');
error_log("GET");
foreach($_GET as $k => $v) {
	error_log($k . $v);
} 
error_log("post");
foreach($_POST as $k => $v) {
	error_log($k . $v);
} 

// store errors to pass along
$error = FALSE;
$error_forward_url = REFERER;
$user = elgg_get_logged_in_user_entity();
// edit or create a new entity
error_log("Save debug:");
error_log(get_input('question_id'));
error_log(get_input('title'));
$guid = get_guid_from_question_id(get_input('question_id'));
error_log($guid);
if ($guid) {
	$entity = get_entity($guid);
	if (elgg_instanceof($entity, 'object', 'khan_exercise') && $entity->canEdit()) {
		$blog = $entity;
	} else {
		register_error(elgg_echo('khan_exercise:error:exercise_not_found'));
		forward(get_input('forward', REFERER));
	}

	// save some data for revisions once we save the new edit
	$revision_text = $blog->description;
	$new_post = $blog->new_post;
} else {
	$entity = new ElggKhanExercise();
	$blog = $entity;
	$blog->question_id = null;
	$blog->save();
	//$blog = khan_exercise_create_or_update($_GET['question_id'], $_POST['title']);
	$new_post = TRUE;
}


//Check if question id exists in data base
error_log($blog->question_id);
if ($blog->question_id == null) {
	$query = "INSERT INTO
		`khan_exercises`.`khan_question`
		(`question_author`, `question_course`)
		VALUES
		('".$user->username."', 'nombre_grupo');";

	mysql_query($query);	
	$blog->question_id=mysql_insert_id();
} 
$qstring="UPDATE `khan_exercises`.`khan_question` SET ";
$qstring .= " question_title = '".$_POST["title"]."' ,";
$qstring .= " question_statement = '" . $_POST["statement"] . "' ,";
$qstring .= " question_solution = '" . $_POST["solution"] . "', ";
$qstring .= " question_check = '" . $_POST["solution_checker"] . "', ";
$qstring .= " question_error = '" . $_POST["error"] . "', ";
$qstring .= " question_round = '" . $_POST["round"] . "' ";
$qstring .= " WHERE question_id =".$blog->question_id."";
error_log( $qstring);
mysql_query($qstring);	
set_input("question_id", $blog->question_id);


// set the previous status for the hooks to update the time_created and river entries
$old_status = $blog->status;

// set defaults and required values.
$values = array(
	'title' => '',
	'description' => '',
	'status' => 'draft',
	'access_id' => ACCESS_DEFAULT,
	'comments_on' => 'On',
	'excerpt' => '',
	'tags' => '',
	'container_guid' => (int)get_input('container_guid'),
	'question_id' => $blog->question_id,
	'khan_hints' => array(),
);
error_log('HIFDSAIFDSAHIOPDSAJ');
error_log($_POST['title_genghis']);
error_log($qstring->question_title);
error_log($blog->title);
error_log($values->title);
// fail if a required entity isn't set
$required = array('title');

// load from POST and do sanity and access checking
foreach ($values as $name => $default) {
	if ($name === 'title') {
		$value = htmlspecialchars(get_input('title', $default, false), ENT_QUOTES, 'UTF-8');
	} else {
		$value = get_input($name, $default);
	}

	if (in_array($name, $required) && empty($value)) {
		$error = elgg_echo("khan_exercise:error:missing:$name");
	}

	if ($error) {
		break;
	}

	switch ($name) {
		case 'tags':
			if ($value) {
				$values[$name] = string_to_tag_array($value);
			} else {
				unset ($values[$name]);
			}
			break;

		case 'container_guid':
			// this can't be empty or saving the base entity fails
			if (!empty($value)) {
				if (can_write_to_container($user->getGUID(), $value)) {
					$values[$name] = $value;
				} else {
					$error = elgg_echo("khan_exercise:error:cannot_write_to_container");
				}
			} else {
				unset($values[$name]);
			}
			break;

		default:
			$values[$name] = $value;
			break;
	}
}

// if preview, force status to be draft
if ($save == false) {
	$values['status'] = 'draft';
}

// if draft, set access to private and cache the future access
if ($values['status'] == 'draft') {
	$values['future_access'] = $values['access_id'];
	$values['access_id'] = ACCESS_PRIVATE;
}

// assign values to the entity, stopping on error.
if (!$error) {
	foreach ($values as $name => $value) {
		if (FALSE === ($blog->$name = $value)) {
			$error = elgg_echo('khan_exercise:error:cannot_save' . "$name=$value");
			break;
		}
	}
}

// only try to save base entity if no errors
error_log("error");
error_log($error);
if (!$error) {
	if ($blog->save()) {
		// remove sticky form entries
		elgg_clear_sticky_form('khan_exercise');

		// remove autosave draft if exists
		$blog->deleteAnnotations('khan_exercise_auto_save');

		// no longer a brand new post.
		$blog->deleteMetadata('new_exercise');

		// if this was an edit, create a revision annotation
		if (!$new_post && $revision_text) {
			$blog->annotate('blog_revision', $revision_text);
		}

		system_message(elgg_echo('khan_exercise:message:saved'));

		$status = $blog->status;

		// add to river if changing status or published, regardless of new post
		// because we remove it for drafts.
		if (($new_post || $old_status == 'draft') && $status == 'published') {
			elgg_create_river_item(array(
				'view' => 'river/object/khan_exercise/create',
				'action_type' => 'create',
				'subject_guid' => $blog->owner_guid,
				'object_guid' => $blog->getGUID(),
			));

			elgg_trigger_event('publish', 'object', $blog);

			// reset the creation time for posts that move from draft to published
			if ($guid) {
				$blog->time_created = time();
				$blog->save();
			}
		} elseif ($old_status == 'published' && $status == 'draft') {
			elgg_delete_river(array(
				'object_guid' => $blog->guid,
				'action_type' => 'create',
			));
		}

		if ($blog->status == 'published' || $save == false) {
			forward("khan_exercise/edit/$blog->guid");
			//forward($blog->getURL());
		} else {
			forward("khan_exercise/edit/$blog->guid");
		}
	} else {
		register_error(elgg_echo('khan_exercise:error:cannot_save'));
		forward($error_forward_url);
	}
} else {
	register_error($error);
	forward($error_forward_url);
}
?>

