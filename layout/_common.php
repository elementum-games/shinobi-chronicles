<?php

function coreHeading(string $stylesheet_path): string {
    global $system;

    $react_dev_tags = <<<HTML
<script src="https://unpkg.com/react@17/umd/react.development.js" crossorigin></script>
<script src="https://unpkg.com/react-dom@17/umd/react-dom.development.js" crossorigin></script>
HTML;

    $react_prod_tags = <<<HTML
<script src="https://unpkg.com/react@17/umd/react.production.min.js" crossorigin></script>
<script src="https://unpkg.com/react-dom@17/umd/react-dom.production.min.js" crossorigin></script>
HTML;

    if($system->environment == System::ENVIRONMENT_DEV) {
        $extra_meta_tags = '<meta name="robots" content="noindex" />';
        $react_tags = $react_dev_tags;
    }
    else {
        $extra_meta_tags = '';
        $react_tags = $react_prod_tags;
    }

    return <<<HTML
<!doctype HTML public>
<html lang="en">
<head>
	<title>Shinobi Chronicles RPG</title>
	<link rel='stylesheet' type='text/css' href='{$stylesheet_path}' />
	<link rel="icon" href="images/icons/favicon.ico" type="image/x-icon" />
	<script type='text/javascript' src='./scripts/jquery-2.1.0.min.js'></script>
	<script type='text/javascript' src="./scripts/jquery-ui.js"></script>
	{$react_tags}
	<script type='text/javascript' src="./scripts/functions.js"></script>
	<script type='text/javascript' src="./scripts/timer.js"></script>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="keywords" content="naruto, rpg, online, game, anime, manga, mmorpg" />
	<meta name="description" content="Shinobi Chronicles: An online browser-based RPG inspired by the anime/manga Naruto." />
	$extra_meta_tags
	<script type='text/javascript'>
        $(document).ready(function(){
            if(typeof train_time !== 'undefined') {
                countdownTimer(train_time, 'trainingTimer');
            }
	});
	</script>
</head>
HTML;
}