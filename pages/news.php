<?php
/*
File: 		news.php
Coder:		Levi Meahan
Created:	11/03/2013
Revised:	11/03/2013 by Levi Meahan
Purpose:	Function for displaying news posts and allowing admins to create and edit news posts
Algorithm:	See master_plan.html
*/

function news() {
	global $system;

	global $player;

	$self_link = $system->router->links['news'];

	$page = isset($_GET['page']) ? $_GET['page'] : false;
    $max_length = 7500;

	if(!empty($_POST['create_post']) && $player->hasAdminPanel()) {
		$post = $system->db->clean($_POST['news_post']);
		$title = $system->db->clean($_POST['title']);

		try {
			if(strlen($post) < 5) {
				throw new RuntimeException("Please enter a post!");
			}
			if(strlen($title) < 4) {
				throw new RuntimeException("Please enter a title!");
			}

			if(strlen($post) > $max_length) {
				throw new RuntimeException("Post is too long! (" . strlen($post) . "/{$max_length} chars)");
			}
			if(strlen($title) > 50) {
				throw new RuntimeException("Title is too long! (" . strlen($title) . "/50 chars)");
			}

			$query = "INSERT INTO `news_posts` (`sender`, `title`, `message`, `time`)
				VALUES ('{$player->user_name}', '{$title}', '{$post}', '" . time() . "')";
			$system->db->query($query);

			if($system->db->last_affected_rows == 1) {
				$system->message("News posted!");
				$page = false;
			}
			else {
				throw new RuntimeException("There was an error posting.");
			}

		} catch (RuntimeException $e) {
			$system->message($e->getMessage());
			$page = "create_post";
		}
		$system->printMessage();
	}
	else if(!empty($_POST['edit_post']) && $player->isUserAdmin()) {
		$post_id = (int)$system->db->clean($_POST['post_id']);
		$message = $system->db->clean($_POST['news_post']);
		$title = $system->db->clean($_POST['title']);

		try {
			$result = $system->db->query("SELECT `post_id` FROM `news_posts` WHERE `post_id`='$post_id'");
			if($system->db->last_num_rows == 0) {
				throw new RuntimeException("Invalid post!");
			}

			if(strlen($message) < 5) {
				throw new RuntimeException("Please enter a post!");
			}
			if(strlen($title) < 4) {
				throw new RuntimeException("Please enter a title!");
			}

			if(strlen($message) > $max_length) {
				throw new RuntimeException("Post is too long! (" . strlen($message) . "/{$max_length}} chars)");
			}
			if(strlen($title) > 50) {
				throw new RuntimeException("Title is too long! (" . strlen($title) . "/50 chars)");
			}

			$query = "UPDATE `news_posts` SET
				`title` = '{$title}',
				`message` = '{$message}'
				WHERE `post_id`='{$post_id}' LIMIT 1";
			$system->db->query($query);

			if($system->db->last_affected_rows == 1) {
				$system->message("News edited!");
				$page = false;
			}
			else {
				throw new RuntimeException("There was an error posting.");
			}

		} catch (RuntimeException $e) {
			$system->message($e->getMessage());
			$page = "edit_post";
		}
		$system->printMessage();
	}

	// Show create, edit pages, or display news posts
	if($page == "create_post" && $player->hasAdminPanel()) {
		echo "<table class='table'><tr><th>New Post</th></tr>
		<tr><td style='text-align:center;'>
			<form action='$self_link' method='post'>
				<span style='font-weight:bold;'>Title</span><br />
				<input type='text' name='title' value='" . ($_POST['title'] ? $_POST['title'] : '') . "' /><br />
				<textarea name='news_post' style='height:200px;width:550px;'>" .
				($_POST['news_post'] ? $_POST['news_post'] : '') .
				"</textarea>
				<br />
				<input type='submit' name='create_post' value='Post' />
			</form>
		</td></tr></table>";
	}
	else if($page == "edit_post" && $player->isUserAdmin()) {
		$post_id = (int)$system->db->clean($_GET['post']);
		$result = $system->db->query("SELECT * FROM `news_posts` WHERE `post_id`='$post_id'");
		if($system->db->last_num_rows == 0) {
			$system->message("Invalid post!");
			$system->printMessage();
			$page = false;
		}
		else {
			$post = $system->db->fetch($result);
			echo "<table class='table'><tr><th>New Post</th></tr>
			<tr><td style='text-align:center;'>
				<form action='$self_link' method='post'>
					<span style='font-weight:bold;'>Title</span><br />
					<input type='text' name='title' value='" . stripslashes($post['title']) . "' /><br />
					<textarea name='news_post' style='height:200px;width:550px;'>" .
					stripslashes($post['message']) .
					"</textarea><br />
					<input type='hidden' name='post_id' value='{$post_id}' />
					<input type='submit' name='edit_post' value='Edit' />
				</form>
			</td></tr></table>";
		}
	}

	if(!$page) {
		if($player->hasAdminPanel()) {
			echo "<p style='text-align:center;'><a href='$self_link&page=create_post'>New post</a></p>";
			newsPosts($player->isUserAdmin());
		}
		else {
			newsPosts();
		}
	}
}

function newsPosts($ADMIN = false, $max_posts = 8): void {
	global $system;
	$self_link = $system->router->links['news'];

	$result = $system->db->query("SELECT * FROM `news_posts` ORDER BY `post_id` DESC LIMIT $max_posts");

	if($system->db->last_num_rows == 0) {
		$system->message("No news posts!");
		$system->printMessage();
	}

	while($post = $system->db->fetch($result)) {
		echo "<table id='newstable' class='table'><tr><th>" . $post['title'];
		if($ADMIN) {
			echo " ( <a style='color:inherit;' href='{$self_link}&page=edit_post&post={$post['post_id']}'>Edit</a> )";
		}
		echo "</th></tr>
			<tr><td>"
            . $system->parseMarkdown(text: stripslashes($post['message']), allow_images: true)
            . "</td></tr><tr id='newsfooter'><td class='newsFooter'>"
            . $post['sender'] . " - " . strftime("%m/%d/%y", $post['time']) .
		"</td></tr></table>";
	}
}
