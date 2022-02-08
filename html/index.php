<?php
include_once "core/classes/Utils.php";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<body>

<?php
if (!Utils::verifyPermissions()) {
    echo "Permissions writable Denied!!<br>" ;
}
?>

<a href="core/GenerateFavicon.php">Favicon</a>
<a href="core/GenerateMiniThumb.php">Thumbnail</a>

</body>
</html>