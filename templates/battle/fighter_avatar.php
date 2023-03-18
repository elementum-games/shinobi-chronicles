<style>
    .avatarContainer {
        width: 100px;
        height: 100px;

        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(0,0,0,0.1);
    }
    .avatarImage {
        display:block;
        margin: auto;

        /* for alt text */
        font-weight: bold;
    }
</style>
<?php

function renderAvatar(Fighter $fighter) {
    $max_size = $fighter->getAvatarSize();
    ?>
    <div class='avatarContainer'>
        <?php renderAvatarImage($fighter, $max_size) ?>
    </div>
    <?php
}

function renderAvatarImage(Fighter $fighter, int $max_size) {
    $name_words = explode(' ', ucwords($fighter->getName()));
    $name_letters = array_map(function($word) { return substr($word, 0, 1); }, $name_words);
    $name_initials = implode('', $name_letters);

    $alt = '';
    if(strlen($name_initials) > 1) {
        $alt = $name_initials[0] . $name_initials[strlen($name_initials) - 1];
    }
    else {
        $alt = $name_initials;
    }
    ?>
    <img
        src='<?= $fighter->avatar_link ?>'
        class='avatarImage'
        style='max-width: <?= $max_size ?>px'
        alt='<?= $alt ?>'
    />
    <?php
}
