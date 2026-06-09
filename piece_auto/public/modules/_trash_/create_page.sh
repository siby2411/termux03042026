#!/bin/bash
PAGE_NAME=$1
TARGET="public/modules/$PAGE_NAME.php"

cat <<PHP > "$TARGET"
<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/Database.php';

\$db = (new Database())->getConnection();
\$page_title = "$PAGE_NAME";

include __DIR__ . '/../includes/header.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <h1><?php echo \$page_title; ?></h1>
        </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
PHP
echo "Page $TARGET créée avec succès."
