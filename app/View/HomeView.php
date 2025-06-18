<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hello World - Aether PHP Framework</title>
</head>
<body>

    <h1><?= $text['home'] ?></h1>

    <p>Page rendered in <?= $debug['time'] ?> seconds and using <?= $debug['memory'] ?> MB of memory.</p>

    <?= view('Component/FooterComponent') ?>