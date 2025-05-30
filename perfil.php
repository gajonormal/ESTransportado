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
<style>
    .form-control {
        display: flex;
        align-items: center;
        background: #2c2c2c;
        padding: 10px;
        border-radius: 5px;
        margin: 10px 0;
        border: 2px solid transparent; /* Borda transparente por padrão */
        color: #ffffff;
        transition: border-color 0.3s ease; /* Transição suave para a borda */
    }

    input[readonly] {
        pointer-events: none;
        background: #2c2c2c;
    }

    input:not([readonly]) {
        background: #2c2c2c !important;
        color: #ffffff !important;
    }

    /* Estilo quando o input está em foco (selecionado) */
    .form-control:focus, 
    input:focus {
        outline: none;
        border-color: #bdf13b; /* Borda verde */
        box-shadow: 0 0 0 3px rgba(189, 241, 59, 0.3); /* Efeito de glow verde suave */
    }

    .radio-group button {
        background: #2c2c2c;
        color: #ffffff;
        border: none;
        padding: 8px 15px;
        margin-right: 5px;
        border-radius: 5px;
        cursor: pointer;
    }
    
    .radio-group button.selected {
        background: #bdf13b;
    }
        #btn-salvar {
        background-color: #bdf13b;
        color: #000000;
        border: 2px solid #bdf13b;
        padding: 8px 20px;
        border-radius: 5px;
        font-weight: bold;
        transition: all 0.3s ease;
    }

    #btn-salvar:hover {
        background-color: #a8d82e;
        border-color: #a8d82e;
    }

</style>
<body>
    <header>
        <a href="<?php
        switch($_SESSION['user_type']) {
            case 'admin':
                echo 'pagina-admin.php';
                break;
            case 'gestor':
                echo 'pagina-gestor.php';
                break;
            case 'aluno':
            default:
                echo 'pagina-aluno.php';
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

            <?php if (isset($_GET['status'])): ?>
              <div class="alert alert-<?= $_GET['status'] === 'sucesso' ? 'success' : 'danger' ?>">
                <?= htmlspecialchars($_GET['mensagem']) ?>
              </div>
            <?php endif; ?>

            <form action="atualizar-perfil.php" method="POST">
                <div class="mb-3">
                    <label class="form-label">Nome Completo</label>
                    <input type="text" class="form-control" name="nome_completo" value="<?= htmlspecialchars($user['nome_completo']) ?>" readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email Institucional</label>
                    <input type="email" class="form-control" name="email_institucional" value="<?= htmlspecialchars($user['email_institucional']) ?>" readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label">Data de Nascimento</label>
                    <input type="date" class="form-control" name="data_nascimento" value="<?= $data_nascimento_formatada ?>" readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label">Sexo</label>
                    <input type="hidden" name="sexo" id="sexo" value="<?= $user['sexo'] ?>">
                    <div class="radio-group">
                        <button type="button" class="<?= $user['sexo'] === 'masculino' ? 'selected' : '' ?>" data-value="masculino" disabled>Masculino</button>
                        <button type="button" class="<?= $user['sexo'] === 'feminino' ? 'selected' : '' ?>" data-value="feminino" disabled>Feminino</button>
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
            <div class="row">
                <div class="col-md-4">
                    <div class="rodape-sobre">
                        <h3>Sobre a <span>EST</span>ransportado</h3>
                        <p>A ESTransportado oferece soluções de transporte eficientes e acessíveis para estudantes, ligando-os com as suas instituições de ensino.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="rodape-links">
                        <h3>Links <span>Rápidos</span></h3>
                        <ul>
                            <li><a href="pagina-admin.php">Dashboard</a></li>
                            <li><a href="gerir-utilizadores.php">Gerir Utilizadores</a></li>
                            <li><a href="gerir-registos.php">Gerir Registos</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="rodape-contactos">
                        <h3>Contacte-nos</h3>
                        <ul>
                            <li><strong>Email:</strong> info@estransportado.pt</li>
                            <li><strong>Telefone:</strong> +351 123 456 789</li>
                            <li><strong>Endereço:</strong> Rua da Universidade, 1000 - Castelo Branco, Portugal</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="rodape-direitos">
                <p>&copy; 2025 ESTransportado. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>

    <script>
    document.getElementById('btn-editar')?.addEventListener('click', function () {
        const inputs = document.querySelectorAll('input[readonly]');
        inputs.forEach(input => input.removeAttribute('readonly'));

        const sexoButtons = document.querySelectorAll('.radio-group button');
        const sexoHidden = document.getElementById('sexo');

        sexoButtons.forEach(button => {
            button.disabled = false;
            button.addEventListener('click', function () {
                sexoButtons.forEach(btn => btn.classList.remove('selected'));
                this.classList.add('selected');
                sexoHidden.value = this.dataset.value;
            });
        });

        document.getElementById('btn-editar').style.display = 'none';
        document.getElementById('btn-salvar').style.display = 'block';
    });
    </script>
</body>
</html>
