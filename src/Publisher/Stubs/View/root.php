<?php
use Jengo\Inertia\Inertia;

use function Jengo\Base\vite_tags;

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jengo Inertia App</title>
    <?= vite_tags(); ?>
</head>

<body>
    <?= Inertia::init($page)?>
</body>

</html>