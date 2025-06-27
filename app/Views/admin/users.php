<?php $this->layout('layouts/default', ['title' => 'Usuários']) ?>

<h1>Usuários</h1>

<table class="table table-striped">
    <thead>
    <tr>
        <th>ID</th><th>Usuário</th><th>Perfil</th><th>Ações</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($users as $user): ?>
        <tr>
            <td><?= $this->e($user['id']) ?></td>
            <td><?= $this->e($user['username']) ?></td>
            <td><?= $this->e($user['role']) ?></td>
            <td>
                <!-- Botões para editar/excluir podem abrir modal ou link para formulário -->
                <form method="post" action="/admin/users/delete" style="display:inline" onsubmit="return confirm('Confirma exclusão?')">
                    <input type="hidden" name="id" value="<?= $this->e($user['id']) ?>" />
                    <button type="submit" class="btn btn-danger btn-sm">Excluir</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<h2>Adicionar Usuário</h2>
<form method="post" action="/admin/users/create">
    <div class="mb-3">
        <label for="username" class="form-label">Usuário</label>
        <input type="text" class="form-control" id="username" name="username" required />
    </div>
    <div class="mb-3">
        <label for="password" class="form-label">Senha</label>
        <input type="password" class="form-control" id="password" name="password" required />
    </div>
    <div class="mb-3">
        <label for="role" class="form-label">Perfil</label>
        <select class="form-select" id="role" name="role" required>
            <option value="atendente">Atendente</option>
            <option value="gestor">Gestor</option>
            <option value="administrador">Administrador</option>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Adicionar</button>
</form>