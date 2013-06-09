<style>
#fixed-div { 
	display: none;
}
</style>
<?php
/**
 * Edit blog form
 *
 * @package Blog
 */

$_SESSION['question_id'] = $vars['question_id'];
$_GET['question_id'] = $vars['question_id'];
define(GENGHIS_LIBS, "http://elgg.khanacademie.nl/mod/genghis/Genghis/libs");
$GENGHIS_LIBS="http://elgg.khanacademie.nl/mod/genghis/Genghis/libs";

$blog = get_entity($vars['guid']);
$vars['entity'] = $blog;

$draft_warning = $vars['draft_warning'];
if ($draft_warning) {
	$draft_warning = '<span class="mbm elgg-text-help">' . $draft_warning . '</span>';
}
$genghis_path = elgg_get_plugins_path() . 'genghis/Genghis/';

$action_buttons = '';
$delete_link = '';
$preview_button = '';

if ($vars['guid']) {
	// add a delete button if editing
	$delete_url = "action/blog/delete?guid={$vars['guid']}";
	$delete_link = elgg_view('output/confirmlink', array(
		'href' => $delete_url,
		'text' => elgg_echo('delete'),
		'class' => 'elgg-button elgg-button-delete float-alt'
	));
}

// published blogs do not get the preview button
if (!$vars['guid'] || ($blog && $blog->status != 'published')) {
	$preview_button = elgg_view('input/submit', array(
		'value' => elgg_echo('preview'),
		'name' => 'preview',
		'class' => 'mls',
	));
}

echo <<<___HTML

<!-- CSS -->
<link rel="stylesheet" href="$GENGHIS_LIBS/jqueryUI/css/custom-jqueryui-theme/jquery-ui-1.9.2.custom.min.css" type="text/css"></link>
<link rel="stylesheet" href="$GENGHIS_LIBS/css/puvikhan.css" type="text/css"></link>
<link rel="stylesheet" href="$GENGHIS_LIBS/css/fonts/stylesheet.css" type="text/css" media="screen"></link>

<!-- JS -->
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js"></script>
<script type="text/javascript" src="$GENGHIS_LIBS/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript" src="$GENGHIS_LIBS/js/jquery.multi-accordion-1.5.3.js"></script>
<script type="text/javascript" src="$GENGHIS_LIBS/autosave/javascript/jquery.autosave.js"></script>
<script type="text/javascript" src="$GENGHIS_LIBS/tinymce/jscripts/tiny_mce/plugins/asciimath/js/ASCIIMathMLwFallback.js"></script>
<script type="text/javascript">
var AMTcgiloc = "http://www.imathas.com/cgi-bin/mimetex.cgi";  		//change me
</script>



<script>
$(function() {
    $( "#tabs" ).tabs();
});
</script>


<script type="text/javascript">
//Script encargado de guardar la altura a la que esta el usuario en la pagina y que pestañas tiene abiertas
cookieName="page_scroll";
cookieName2="open_tabs";
expdays=365;

// An adaptation of Dorcht's cookie functions.

function setCookie(name, value, expires, path, domain, secure){
    if (!expires){expires = new Date()}
        document.cookie = name + "=" + escape(value) + 
        ((expires == null) ? "" : "; expires=" + expires.toGMTString()) +
        ((path == null) ? "" : "; path=" + path) +
        ((domain == null) ? "" : "; domain=" + domain) +
        ((secure == null) ? "" : "; secure")
}

function getCookie(name) {
    var arg = name + "="
        var alen = arg.length
        var clen = document.cookie.length
        var i = 0
        while (i < clen) {
            var j = i + alen
                if (document.cookie.substring(i, j) == arg){
                    return getCookieVal(j)
                }
            i = document.cookie.indexOf(" ", i) + 1
                if (i == 0) break
        }
    return null
}

function getCookieVal(offset){
    var endstr = document.cookie.indexOf (";", offset);
    if (endstr == -1)
        endstr = document.cookie.length;
    return unescape(document.cookie.substring(offset, endstr));
}

function deleteCookie(name,path,domain){
    document.cookie = name + "=" +
        ((path == null) ? "" : "; path=" + path) +
        ((domain == null) ? "" : "; domain=" + domain) +
        "; expires=Thu, 01-Jan-00 00:00:01 GMT";
}

function saveScroll(){ // added function
    var expdate = new Date ();
    expdate.setTime (expdate.getTime() + (expdays*24*60*60*1000)); // expiry date

    var x = (document.pageXOffset?document.pageXOffset:document.body.scrollLeft);
    var y = (document.pageYOffset?document.pageYOffset:document.body.scrollTop);
    Data=x + "_" + y;
    setCookie(cookieName,Data,expdate);

    var varia = $("div.course")[0].className == "course active" ? 1 : 0;
    var title = $("div.course")[1].className == "course active" ? 1 : 0;
    var state = $("div.course")[2].className == "course active" ? 1 : 0;
    var solut = $("div.course")[3].className == "course active" ? 1 : 0;
    var hints = $("div.course")[4].className == "course active" ? 1 : 0;

    Data2= varia + "_" + title + "_" + state + "_" + solut + "_" + hints;
    // Data2= "0_0_1_0_0";
    setCookie(cookieName2,Data2,expdate);
}

function loadScroll(){ // added function
    if($('h1.typeOfPage').html()== "Fill in the blank"){
        inf2=getCookie(cookieName2);
        if(!inf2){return;}
        var ar2 = inf2.split("_");
        for (var i = 0; i < ar2.length; i++) {
            if(ar2[i]=="1"){
                $("div.course")[i].click();
        /*	if($("div.course")[i].className != "course active"){
            $("div.course")[i].className = 'course active';
            } else {
                $("div.course")[i].className = 'course';
        }*/
            }
        }
    }
    inf=getCookie(cookieName);
    if(!inf){return;}
    var ar = inf.split("_");
    if(ar.length == 2){
        window.scrollTo(parseInt(ar[0]), parseInt(ar[1]));
    }
}

// add onload="loadScroll()" onunload="saveScroll()" to the opening BODY tag
document.onload = function() {
    saveScroll();
}
$(document).ready(function() {
    loadScroll();
/* $("div.elem").click(function() {			
    if ($(this).attr("id")) {
        location.href = 'http://163.117.152.240/khan_exercises/?class=admin&action=info&elem=' + $(this).attr("id");				
    }
});
 */
    $("div.course").click(function() {
        $(this).toggleClass("active");
        if ($("span.toggle", $(this)).length == 0) return;

        $(".elem", $(this).parent()).each(function() {
            $(this).slideToggle(100);

        });

        $("span.toggle", $(this)).toggleClass("more");

    });

});

</script>
___HTML;
?>
<script type="text/javascript">
function remove_textbox() {
    var item = document.getElementById('new_var_type');

    var index = item.selectedIndex;
    if (item.options[index].text == 'entero' ) {
        document.getElementById('step_string').style.display="none";
    }
    if (item.options[index].text == 'decimal' ) {
        document.getElementById('step_string').style.display="inline";
    }

}

</script>

<?php
    $path =	elgg_get_plugins_path() . 'genghis/Genghis/';
    set_include_path(get_include_path() . PATH_SEPARATOR . $path);
    $con = _elgg_services()->db->getLink("readwrite");
	$_SESSION['question_id'] = $vars['question_id'];
	$_GET['question_id'] = $vars['question_id'];
	define(GENGHIS_LIBS, "http://elgg.khanacademie.nl/mod/genghis/Genghis/libs");
	$GENGHIS_LIBS="http://elgg.khanacademie.nl/mod/genghis/Genghis/libs";
	error_log("QUESTION IDDDDDD");
	error_log($vars['question_id']);

    mysql_select_db("khan_exercises", $con);
	include $path.'configs.php';
	include $path.'controllers/FillInTheBlank.php';
    mysql_select_db("elgg", $con);
?>


<?php
	define(GENGHIS_LIBS, "http://elgg.khanacademie.nl/mod/genghis/Genghis/libs");
	$GENGHIS_LIBS="http://elgg.khanacademie.nl/mod/genghis/Genghis/libs";
	
	$blog = get_entity($vars['guid']);
	$vars['entity'] = $blog;
	
	$draft_warning = $vars['draft_warning'];
	if ($draft_warning) {
		$draft_warning = '<span class="mbm elgg-text-help">' . $draft_warning . '</span>';
	}
	$genghis_path = elgg_get_plugins_path() . 'genghis/Genghis/';
	
	$action_buttons = '';
	$delete_link = '';
	$preview_button = '';
	
	if ($vars['guid']) {
		// add a delete button if editing
		$delete_url = "action/blog/delete?guid={$vars['guid']}";
		$delete_link = elgg_view('output/confirmlink', array(
			'href' => $delete_url,
			'text' => elgg_echo('delete'),
			'class' => 'elgg-button elgg-button-delete float-alt'
		));
	}
	
	// published blogs do not get the preview button
	if (!$vars['guid'] || ($blog && $blog->status != 'published')) {
		$preview_button = elgg_view('input/submit', array(
			'value' => elgg_echo('preview'),
			'name' => 'preview',
			'class' => 'mls',
		));
	}
	
	$save_button = elgg_view('input/submit', array(
		'value' => elgg_echo('save'),
		'name' => 'save',
		'id' => 'khan_exercise_save',

	));
	$action_buttons = $save_button; // . $preview_button . $delete_link;
	
	$title_label = elgg_echo('title');
	$title_input = elgg_view('input/text', array(
		'name' => 'title',
		'id' => 'blog_title',
		'value' => $vars['entity']['title']
	));
	
	$excerpt_label = elgg_echo('blog:excerpt');
	$excerpt_input = elgg_view('input/text', array(
		'name' => 'excerpt',
		'id' => 'blog_excerpt',
		'value' => _elgg_html_decode($vars['excerpt'])
	));
	
	$body_label = elgg_echo('blog:body');
	$body_input = elgg_view('input/longtext', array(
		'name' => 'description',
		'id' => 'blog_description',
		'value' => $vars['description']
	));
	
	$save_status = elgg_echo('blog:save_status');
	if ($vars['guid']) {
		$entity = get_entity($vars['guid']);
		$saved = date('F j, Y @ H:i', $entity->time_created);
	} else {
		$saved = elgg_echo('blog:never');
	}
	
	$status_label = elgg_echo('blog:status');
	$status_input = elgg_view('input/select', array(
		'name' => 'status',
		'id' => 'blog_status',
		'value' => $vars['status'],
		'options_values' => array(
			'draft' => elgg_echo('blog:status:draft'),
			'published' => elgg_echo('blog:status:published')
		)
	));
	
	$comments_label = elgg_echo('comments');
	$comments_input = elgg_view('input/select', array(
		'name' => 'comments_on',
		'id' => 'blog_comments_on',
		'value' => $vars['comments_on'],
		'options_values' => array('On' => elgg_echo('on'), 'Off' => elgg_echo('off'))
	));
	
	$tags_label = elgg_echo('tags');
	$tags_input = elgg_view('input/tags', array(
		'name' => 'tags',
		'id' => 'blog_tags',
		'value' => $vars['tags']
	));
	
	$access_label = elgg_echo('access');
	$access_input = elgg_view('input/access', array(
		'name' => 'access_id',
		'id' => 'blog_access_id',
		'value' => $vars['access_id']
	));
	
	$categories_input = elgg_view('input/categories', $vars);
	
	// hidden inputs
	$container_guid_input = elgg_view('input/hidden', array('name' => 'container_guid', 'value' => elgg_get_page_owner_guid()));
	$guid_input = elgg_view('input/hidden', array('name' => 'guid', 'value' => $vars['guid']));
	
	
	echo <<<___HTML
	
	$draft_warning

	<div>
		<label for="blog_title">$title_label</label>
		$title_input
	</div>
<!--	
	<div>
		<label for="blog_excerpt">$excerpt_label</label>
		$excerpt_input
	</div>
	
	<div>
		<label for="blog_description">$body_label</label>
		$body_input
	</div>
	
	<div>
		<label for="blog_tags">$tags_label</label>
		$tags_input
	</div>
-->	
	$categories_input
	
	<div>
		<label for="blog_comments_on">$comments_label</label>
		$comments_input
	</div>
	
	<div>
		<label for="blog_access_id">$access_label</label>
		$access_input
	</div>
	
	<div>
		<label for="blog_status">$status_label</label>
		$status_input
	</div>
	
	<div class="elgg-foot">
		<div class="elgg-subtext mbm">
		$save_status <span class="blog-save-status-time">$saved</span>
		</div>
	
		$guid_input
		$container_guid_input
	
		$action_buttons
	</div>
	
___HTML;


?>
