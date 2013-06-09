<?php
/**
 * Delete blog entity
 *
 * @package Blog
 */

$blog_guid = get_input('guid');
$blog = get_entity($blog_guid);
error_log('aqui va el blog');
var_dump($blog);
if (elgg_instanceof($blog, 'object', 'khan_exercise') && $blog->canEdit()) {
	$container = get_entity($blog->container_guid);
	if ($blog->delete()) {
		system_message(elgg_echo('blog:message:deleted_post'));
		if (elgg_instanceof($container, 'group')) {
			forward("khan_exercise/group/$container->guid/all");
		} else {
			forward("khan_exercise/owner/$container->username");
		}
	} else {
		register_error(elgg_echo('blog:error:cannot_delete_post'));
	}
} else {
	register_error(elgg_echo('blog:error:post_not_found'));
}

forward(REFERER);
