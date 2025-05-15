<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: pagina-login.php");
    exit();
}

// Incluir conexão com o banco de dados
require_once 'basedados/basedados.h';

// Obter dados atualizados do usuário
$stmt = $conn->prepare("SELECT * FROM Utilizadores WHERE id_utilizador = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Processar logout
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: pagina-login.php");
    exit();
}

// Formatar data de nascimento para o input date
$data_nascimento_formatada = !empty($user['data_nascimento']) ? date('Y-m-d', strtotime($user['data_nascimento'])) : '';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Perfil - ESTransportado</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
</head>
<body>
    <header>
        <a href="<?php
        switch($_SESSION['user_type']) {
            case 'admin':
                echo 'pagina-admin.php'; // Página principal do admin
                break;
            case 'gestor':
                echo 'pagina-gestor.php'; // Página principal do gestor
                break;
            case 'aluno':
            default:
                echo 'pagina-aluno.php'; // Página principal do aluno
        }
    ?>" class="logo">
        <img src="imagens/logo.png" alt="ESTransportado">
    </a>
    
        <ul class="navbar">
          <?php if ($_SESSION['user_type'] === 'aluno'): ?>
            <li><a href="historico-aluno.php">Histórico</a></li>
          <?php endif; ?>
        </ul>
    
        <a href="perfil.php?logout=1" class="btn btn-primary" id="btn-entrar">Logout</a>
    </header>
   
    <main>
        <section class="form-section" id="perfil-usuario">
            <h2>Meu Perfil</h2>
            <form action="atualizar-perfil.php" method="POST">
                <div class="mb-3">
                    <label class="form-label">Nome Completo</label>
                    <div class="box-inserir">
                        <?= htmlspecialchars($user['nome_completo']) ?>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Email Institucional</label>
                    <div class="box-inserir">
                        <?= htmlspecialchars($user['email_institucional']) ?>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Data de Nascimento</label>
                    <div class="box-inserir">
                        <input type="date" class="form-control" value="<?= $data_nascimento_formatada ?>" readonly>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Sexo</label>
                    <div class="radio-group">
                        <button type="button" class="<?= $user['sexo'] === 'masculino' ? 'selected' : '' ?>" disabled>
                            Masculino
                        </button>
                        <button type="button" class="<?= $user['sexo'] === 'feminino' ? 'selected' : '' ?>" disabled>
                            Feminino
                        </button>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Número de Identificação</label>
                    <div class="box-inserir">
                        <?= htmlspecialchars($user['numero_matricula']) ?>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Tipo de Utilizador</label>
                    <div class="box-inserir">
                        <?= ucfirst($user['tipo']) ?>
                    </div>
                </div>
                
                <button type="button" id="btn-editar" class="btn btn-primary">Editar Perfil</button>
                <button type="submit" id="btn-salvar" class="btn btn-success" style="display:none;">Salvar Alterações</button>
            </form>
        </section>
    </main>

    <footer class="rodape">
        <div class="container">
            <!-- Rodapé comum a todas as páginas -->
        </div>
    </footer>

    <script>
    // Lógica para ativar/desativar edição
    document.getElementById('btn-editar')?.addEventListener('click', function() {
        const inputs = document.querySelectorAll('input[readonly]');
        inputs.forEach(input => {
            input.removeAttribute('readonly');
        });
        
        // Ativar botões de sexo
        const sexoButtons = document.querySelectorAll('.radio-group button');
        sexoButtons.forEach(button => {
            button.disabled = false;
            button.addEventListener('click', function() {
                sexoButtons.forEach(btn => btn.classList.remove('selected'));
                this.classList.add('selected');
            });
        });
        
        document.getElementById('btn-editar').style.display = 'none';
        document.getElementById('btn-salvar').style.display = 'block';
    });
    </script>
</body>
</html>