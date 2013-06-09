<?php
/**
 * Blog helper functions
 *
 * @package Blog
 */

function khan_exercise_import_repo() {
	$current_user = get_user_by_username("khanacademie");

	$options = array(
		'type' => 'object',
		'subtype' => 'khan_exercise',
	);
	$path = dirname(__file__)."/repos/KhanLatest/khan-exercises/exercises/";	
	$files = readdir($path);
	$html_files = array();
	foreach($files as $file) {
		if (basename($path.$file) == "html") {
			array_push($html_files($file));
		}
	}

	foreach($html_files as $html_file) {
		$options['metadata_name_value_pairs'] =	array('name' => 'html_file', 'value' => $html_file);
		$questions = elgg_get_entities_from_metadata($options);
    		if (count($questions) == 1)  {
    		    return $questions[0];
    		}
    		else {
    		    $question = new ElggKhanExercise();
    		    $question->question_id = $question_id;
    		    $question->html_file = $html_file;
    		    $question->save();
    		    return $question;
    		}
	}
}


/**
 * Get page components to view a blog post.
 *
 * @param int $guid GUID of a blog entity.
 * @return array
 */
function khan_exercise_get_page_content_read($guid = NULL) {

	$return = array();

	$blog = get_entity($guid);

	// no header or tabs for viewing an individual blog
	$return['filter'] = '';

	if (!elgg_instanceof($blog, 'object', 'khan_exercise')) {
		register_error(elgg_echo('noaccess'));
		elgg_get_session()->set('last_forward_from', current_page_url());
		forward('');
	}

	elgg_set_page_owner_guid($blog->container_guid);

	group_gatekeeper();

	$return['title'] = $blog->title;

	$container = $blog->getContainerEntity();
	$crumbs_title = $container->name;
	if (elgg_instanceof($container, 'group')) {
		elgg_push_breadcrumb($crumbs_title, "khan_exercise/group/$container->guid/all");
	} else {
		elgg_push_breadcrumb($crumbs_title, "khen_exercise/owner/$container->username");
	}

	elgg_push_breadcrumb($blog->title);
	//$return['content'] = elgg_view_entity($blog, array('full_view' => true));
	$return['content'] = elgg_view('khan_exercise/khan_exercise', array('entity' => $blog));
	// check to see if we should allow comments
	if ($blog->comments_on != 'Off' && $blog->status == 'published') {
		$return['content'] .= elgg_view_comments($blog);
	}
	$return['canvas_name'] = 'one_column';

	return $return;
}

/**
 * Get page components to list a user's or all blogs.
 *
 * @param int $container_guid The GUID of the page owner or NULL for all blogs
 * @return array
 */
function khan_exercise_get_page_content_list($container_guid = NULL) {

	$return = array();

	$return['filter_context'] = $container_guid ? 'mine' : 'all';

	$options = array(
		'type' => 'object',
		'subtype' => 'khan_exercise',
		'full_view' => false,
		'no_results' => elgg_echo('blog:none'),
	);

	$current_user = elgg_get_logged_in_user_entity();

	if ($container_guid) {
		// access check for closed groups
		group_gatekeeper();

		$options['container_guid'] = $container_guid;
		$container = get_entity($container_guid);
		if (!$container) {

		}
		$return['title'] = elgg_echo('khan_exercise:title:user_khan_exercises', array($container->name));

		$crumbs_title = $container->name;
		elgg_push_breadcrumb($crumbs_title);

		if ($current_user && ($container_guid == $current_user->guid)) {
			$return['filter_context'] = 'mine';
		} else if (elgg_instanceof($container, 'group')) {
			$return['filter'] = false;
		} else {
			// do not show button or select a tab when viewing someone else's posts
			$return['filter_context'] = 'none';
		}
	} else {
		$return['filter_context'] = 'all';
		$return['title'] = elgg_echo('khan_exercise:title:all_khan_exercises');
		elgg_pop_breadcrumb();
		elgg_push_breadcrumb(elgg_echo('khan_exercise:khan_exercises'));
	}

	elgg_register_title_button();

	// show all posts for admin or users looking at their own blogs
	// show only published posts for other users.
	$show_only_published = true;
	if ($current_user) {
		if (($current_user->guid == $container_guid) || $current_user->isAdmin()) {
			$show_only_published = false;
		}
	}
	if ($show_only_published) {
		$options['metadata_name_value_pairs'] = array(
			array('name' => 'status', 'value' => 'published'),
		);
	}

	$return['content'] = elgg_list_entities_from_metadata($options);

	return $return;
}

function get_guid_from_question_id($question_id) {

	$options = array(
		'type' => 'object',
		'subtype' => 'khan_exercise',
	);

	$current_user = elgg_get_logged_in_user_entity();

	$options['metadata_name_value_pairs'] =	array('name' => 'question_id', 'value' => $question_id);
	$questions = elgg_get_entities_from_metadata($options);
    if (count($questions) == 1)  {
        return $questions[0]->guid;
    }
    else {
        return null;
    }

}

/**
 * Get page components to list of the user's friends' posts.
 *
 * @param int $user_guid
 * @return array
 */
function khan_exercise_get_page_content_friends($user_guid) {

	$user = get_user($user_guid);
	if (!$user) {
		forward('khan_exercise/all');
	}

	$return = array();

	$return['filter_context'] = 'friends';
	$return['title'] = elgg_echo('khan_exercise:title:friends');

	$crumbs_title = $user->name;
	elgg_push_breadcrumb($crumbs_title, "khan_exercise/owner/{$user->username}");
	elgg_push_breadcrumb(elgg_echo('friends'));

	elgg_register_title_button();

	$options = array(
		'type' => 'object',
		'subtype' => 'khan_exercise',
		'full_view' => false,
		'relationship' => 'friend',
		'relationship_guid' => $user_guid,
		'relationship_join_on' => 'container_guid',
		'no_results' => elgg_echo('khan_exercise:none'),
	);

	// admin / owners can see any posts
	// everyone else can only see published posts
	$show_only_published = true;
	$current_user = elgg_get_logged_in_user_entity();
	if ($current_user) {
		if (($user_guid == $current_user->guid) || $current_user->isAdmin()) {
			$show_only_published = false;
		}
	}
	if ($show_only_published) {
		$options['metadata_name_value_pairs'][] = array(
			array('name' => 'status', 'value' => 'published')
		);
	}

	$return['content'] = elgg_list_entities_from_relationship($options);

	return $return;
}

/**
 * Get page components to show blogs with publish dates between $lower and $upper
 *
 * @param int $owner_guid The GUID of the owner of this page
 * @param int $lower      Unix timestamp
 * @param int $upper      Unix timestamp
 * @return array
 */
function blog_get_page_content_archive($owner_guid, $lower = 0, $upper = 0) {

	$now = time();

	$owner = get_entity($owner_guid);
	elgg_set_page_owner_guid($owner_guid);

	$crumbs_title = $owner->name;
	if (elgg_instanceof($owner, 'user')) {
		$url = "blog/owner/{$owner->username}";
	} else {
		$url = "blog/group/$owner->guid/all";
	}
	elgg_push_breadcrumb($crumbs_title, $url);
	elgg_push_breadcrumb(elgg_echo('blog:archives'));

	if ($lower) {
		$lower = (int)$lower;
	}

	if ($upper) {
		$upper = (int)$upper;
	}

	$options = array(
		'type' => 'object',
		'subtype' => 'blog',
		'full_view' => false,
		'no_results' => elgg_echo('blog:none'),
	);

	if ($owner_guid) {
		$options['container_guid'] = $owner_guid;
	}

	// admin / owners can see any posts
	// everyone else can only see published posts
	if (!(elgg_is_admin_logged_in() || (elgg_is_logged_in() && $owner_guid == elgg_get_logged_in_user_guid()))) {
		if ($upper > $now) {
			$upper = $now;
		}

		$options['metadata_name_value_pairs'] = array(
			array('name' => 'status', 'value' => 'published')
		);
	}

	if ($lower) {
		$options['created_time_lower'] = $lower;
	}

	if ($upper) {
		$options['created_time_upper'] = $upper;
	}

	$content = elgg_list_entities_from_metadata($options);

	$title = elgg_echo('date:month:' . date('m', $lower), array(date('Y', $lower)));

	return array(
		'content' => $content,
		'title' => $title,
		'filter' => '',
	);
}

/**
 * Get page components to edit/create a blog post.
 *
 * @param string  $page     'edit' or 'new'
 * @param int     $guid     GUID of blog post or container
 * @param int     $revision Annotation id for revision to edit (optional)
 * @return array
 */
function khan_exercise_get_page_content_edit($page, $guid = 0, $revision = NULL, $question_id = NULL) {
	elgg_load_js('elgg.khan_exercise');

	$return = array(
		'filter' => '',
	);

	$vars = array();
	$vars['id'] = 'khan-exercise-edit';
	$vars['class'] = 'khan-exercise-alt';
    $vars['guid'] = $guid;
    $vars['question_id'] = $question_id ? $question_id : 0;

	$sidebar = '';
	if ($page == 'edit') {
		$blog = get_entity((int)$guid);
        $vars['question_id'] = $blog->question_id;
        $vars['guid'] = $blog->guid;

		$title = elgg_echo('khan_exercise:edit');

		if ($blog && $blog->canEdit()) {
			$vars['entity'] = $blog;

			$title .= ": \"$blog->title\"";

			//if ($revision) {
			//	$revision = elgg_get_annotation_from_id((int)$revision);
			//	$vars['revision'] = $revision;
			//	$title .= ' ' . elgg_echo('blog:edit_revision_notice');

			//	if (!$revision || !($revision->entity_guid == $guid)) {
			//		$content = elgg_echo('blog:error:revision_not_found');
			//		$return['content'] = $content;
			//		$return['title'] = $title;
			//		return $return;
			//	}
			//}

			$body_vars = khan_exercise_prepare_form_vars($blog, $revision);

			elgg_push_breadcrumb($blog->title, $blog->getURL());
			elgg_push_breadcrumb(elgg_echo('edit'));
			
			elgg_load_js('elgg.khan_exercise');
			$vars['action'] = 'action/khan_exercise/save?question_id='.$blog->question_id;

			$content = elgg_view_form('khan_exercise/save', $vars, $body_vars);
			$sidebar = elgg_view('khan_exercise/sidebar/revisions', $vars);
		} else {
			$content = elgg_echo('blog:error:cannot_edit_post');
		}
	} else {
		include("Genghis/index.php");
		$sidebar = elgg_view('khan_exercise/sidebar/revisions', $vars);
		elgg_push_breadcrumb(elgg_echo('khan_exercise:add'));
		//$body_vars = blog_prepare_form_vars(null);

		$vars['title'] = '';
        $vars['guid'] = $guid;
        $vars['question_id'] = $question_id;
		$content = elgg_view('forms/khan_exercise/save', $vars, $body_vars);
	}

	$return['title'] = null;
	$return['content'] = $content;
	$return['canvas_name'] = 'one_column';
	$return['sidebar'] = null;//null;//$sidebar;
	return $return;	
}

/**
 * Pull together blog variables for the save form
 *
 * @param ElggBlog       $post
 * @param ElggAnnotation $revision
 * @return array
 */
function khan_exercise_prepare_form_vars($post = NULL, $revision = NULL) {

	// input names => defaults
	$values = array(
		'title' => NULL,
		'description' => NULL,
		'status' => 'published',
		'access_id' => ACCESS_DEFAULT,
		'comments_on' => 'On',
		'excerpt' => NULL,
		'tags' => NULL,
		'container_guid' => NULL,
		'guid' => NULL,
		'draft_warning' => '',
	);

	if ($post) {
		foreach (array_keys($values) as $field) {
			if (isset($post->$field)) {
				$values[$field] = $post->$field;
			}
		}

		if ($post->status == 'draft') {
			$values['access_id'] = $post->future_access;
		}
	}

	if (elgg_is_sticky_form('khan_exercise')) {
		$sticky_values = elgg_get_sticky_values('khan_exercise');
		foreach ($sticky_values as $key => $value) {
			$values[$key] = $value;
		}
	}
	
	elgg_clear_sticky_form('khan_exercise');

	if (!$post) {
		return $values;
	}

	// load the revision annotation if requested
	if ($revision instanceof ElggAnnotation && $revision->entity_guid == $post->getGUID()) {
		$values['revision'] = $revision;
		$values['description'] = $revision->value;
	}

	// display a notice if there's an autosaved annotation
	// and we're not editing it.
	if ($auto_save_annotations = $post->getAnnotations('khan_exercise_auto_save', 1)) {
		$auto_save = $auto_save_annotations[0];
	} else {
		$auto_save = false;
	}

	if ($auto_save && $auto_save->id != $revision->id) {
		$values['draft_warning'] = elgg_echo('blog:messages:warning:draft');
	}

	return $values;
}
