<?php
session_start();

require "classes/System.php";
$system = new System();
$system->renderStaticPageHeader();

?>

<table class='table'><tr><th>Manual</th></tr>
    <tr><td>
        <b>Contributing</b>
        To submit changes to this manual (you may need a GitHub account):
        <ol>
            <li>Go to <a href='https://github.com/levimeahan/shinobi-chronicles/blob/main/manual.php'>manual.php on GitHub</a></li>
            <li>Click the pencil icon on top right</li>
            <li>Make your changes</li>
            <li>Scroll down and click "Propose Changes"</li>
            <li>Click "Create Pull Request"</li>
        </ol>

        <!-- Content -->
        <div>

        </div>
    </td></tr>
</table>

<?php
$system->renderStaticPageFooter();
