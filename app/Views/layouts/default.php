<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <title><?= $this->e($title ?? 'Administração') ?></title>
    <link href="/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="/admin/dashboard">Admin</a>
    <ul class="navbar-nav">
        <li class="nav-item"><a class="nav-link" href="/admin/users">Usuários</a></li>
        <li class="nav-item"><a class="nav-link" href="/admin/sectors">Setores</a></li>
        <li class="nav-item"><a class="nav-link" href="/admin/counters">Guichês</a></li>
        <li class="nav-item"><a class="nav-link" href="/admin/ticket-types">Tipos de Senha</a></li>
    </ul>
</nav>
<div class="container mt-4">
    <?= $this->section('content') ?>
</div>
<script src="/js/bootstrap.bundle.min.js"></script>
</body>
</html>