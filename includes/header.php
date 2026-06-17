<?php
require_once __DIR__ . '/functions.php';
$pageTitle = $pageTitle ?? 'Finote';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e($pageTitle); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo e(app_url('assets/css/app.css')); ?>" rel="stylesheet">
    <link rel="icon" href="/web.ico">
</head>
<body class="app-body">
