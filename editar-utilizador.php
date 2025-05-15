<?php
session_start();

// Verificar se o usuário está logado e é admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: pagina-login.php");
    exit();
}

// Incluir conexão com o banco de dados
require_once 'basedados/basedados.h';

// Obter ID do utilizador a editar
$id_utilizador = isset($_GET['id']) ? $_GET['id'] : null;

if (!$id_utilizador) {
    header("Location: gerir-utilizadores.php");
    exit();
}

// Obter dados do utilizador
$stmt = $conn->prepare("SELECT * FROM Utilizadores WHERE id_utilizador = ?");
$stmt->bind_param("i", $id_utilizador);
$stmt->execute();
$result = $stmt->get_result();
$utilizador = $result->fetch_assoc();

if (!$utilizador) {
    header("Location: gerir-utilizadores.php");
    exit();
}

// Processar atualização
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome_completo = $_POST['nome_completo'];
    $email_institucional = $_POST['email_institucional'];
    $data_nascimento = $_POST['data_nascimento'];
    $sexo = $_POST['sexo'];
    $numero_matricula = $_POST['numero_matricula'];
    $tipo = $_POST['tipo'];
    $conta_ativa = isset($_POST['conta_ativa']) ? 1 : 0;

    // Validar email institucional
    if (!filter_var($email_institucional, FILTER_VALIDATE_EMAIL) || 
        !(strpos($email_institucional, '@ipcb.pt') !== false || strpos($email_institucional, '@ipcbcampus.pt') !== false)) {
        $erro = "Email institucional inválido";
    } else {
        // Atualizar dados
        $stmt = $conn->prepare("UPDATE Utilizadores SET 
            nome_completo = ?, 
            email_institucional = ?, 
            data_nascimento = ?, 
            sexo = ?, 
            numero_matricula = ?, 
            tipo = ?, 
            conta_ativa = ? 
            WHERE id_utilizador = ?");
        
        $stmt->bind_param("ssssssii", 
            $nome_completo, 
            $email_institucional, 
            $data_nascimento, 
            $sexo, 
            $numero_matricula, 
            $tipo, 
            $conta_ativa, 
            $id_utilizador);
        
        if ($stmt->execute()) {
            $sucesso = "Utilizador atualizado com sucesso!";
            // Atualizar dados locais
            $utilizador = array_merge($utilizador, [
                'nome_completo' => $nome_completo,
                'email_institucional' => $email_institucional,
                'data_nascimento' => $data_nascimento,
                'sexo' => $sexo,
                'numero_matricula' => $numero_matricula,
                'tipo' => $tipo,
                'conta_ativa' => $conta_ativa
            ]);
        } else {
            $erro = "Erro ao atualizar utilizador";
        }
    }
}

// Formatar data de nascimento para o input date
$data_nascimento_formatada = !empty($utilizador['data_nascimento']) ? date('Y-m-d', strtotime($utilizador['data_nascimento'])) : '';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Editar Utilizador - ESTransportado</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
  <style>
    .form-section {
      max-width: 800px;
      margin: 30px auto;
      padding: 20px;
      background: #222; /* Fundo escuro */
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.3);
      color: #eee;
    }
    
    h2 {
      color: #c2ff22;
      text-align: center;
      margin-bottom: 25px;
    }
    
    /* Estilo para os inputs */
    .form-control, .form-select {
      background-color: #333 !important;
      border: 1px solid #444;
      color: #eee;
      padding: 10px 15px;
    }
    
    .form-control:focus, .form-select:focus {
      background-color: #444 !important;
      border-color: #c2ff22;
      color: #fff;
      box-shadow: 0 0 0 0.25rem rgba(194, 255, 34, 0.25);
    }
    
    .box-inserir {
      background: #333;
      padding: 10px;
      border-radius: 5px;
      border: 1px solid #444;
      color: #eee;
    }
    
    .radio-group {
      display: flex;
      gap: 10px;
    }
    
    .radio-group button {
      padding: 8px 15px;
      border: 1px solid #444;
      background: #333;
      border-radius: 5px;
      cursor: pointer;
      color: #eee;
      transition: all 0.3s;
    }
    
    .radio-group button.selected {
      background: #c2ff22;
      border-color: #c2ff22;
      color: #222;
      font-weight: bold;
    }
    
    .status-ativo {
      display: flex;
      align-items: center;
      gap: 10px;
    }
    
    .form-check-input {
      background-color: #333;
      border: 1px solid #444;
    }
    
    .form-check-input:checked {
      background-color: #c2ff22;
      border-color: #c2ff22;
    }
    
    .alert {
      margin-top: 20px;
      border-radius: 5px;
    }
    
    .btn-success {
      background-color: #c2ff22;
      border-color: #c2ff22;
      color: #222;
      font-weight: bold;
    }
    
    .btn-secondary {
      background-color: #444;
      border-color: #444;
      color: #eee;
    }
    
    .btn-success:hover {
      background-color: #a8d900;
      border-color: #a8d900;
      color: #222;
    }
    
    .btn-secondary:hover {
      background-color: #555;
      border-color: #555;
    }
    
    small.text-muted {
      color: #999 !important;
    }
  </style>
</head>
<body>
    <header>
        <a href="pagina-admin.php" class="logo">
            <img src="imagens/logo.png" alt="ESTransportado">
        </a>
    </header>
   
    <main>
        <section class="form-section" id="editar-utilizador">
            <h2>Editar Utilizador</h2>
            
            <?php if (isset($erro)): ?>
                <div class="alert alert-danger"><?= $erro ?></div>
            <?php endif; ?>
            
            <?php if (isset($sucesso)): ?>
                <div class="alert alert-success"><?= $sucesso ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Nome Completo</label>
                    <input type="text" class="form-control" name="nome_completo" value="<?= htmlspecialchars($utilizador['nome_completo']) ?>" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Email Institucional</label>
                    <input type="email" class="form-control" name="email_institucional" value="<?= htmlspecialchars($utilizador['email_institucional']) ?>" required>
                    <small class="text-muted">Deve terminar com @ipcb.pt ou @ipcbcampus.pt</small>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Data de Nascimento</label>
                    <input type="date" class="form-control" name="data_nascimento" value="<?= $data_nascimento_formatada ?>">
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Sexo</label>
                    <div class="radio-group">
                        <button type="button" class="<?= $utilizador['sexo'] === 'masculino' ? 'selected' : '' ?>" onclick="document.querySelector('input[name=sexo][value=masculino]').checked = true; this.classList.add('selected'); this.nextElementSibling.classList.remove('selected');">
                            Masculino
                        </button>
                        <button type="button" class="<?= $utilizador['sexo'] === 'feminino' ? 'selected' : '' ?>" onclick="document.querySelector('input[name=sexo][value=feminino]').checked = true; this.classList.add('selected'); this.previousElementSibling.classList.remove('selected');">
                            Feminino
                        </button>
                    </div>
                    <input type="radio" name="sexo" value="masculino" <?= $utilizador['sexo'] === 'masculino' ? 'checked' : '' ?> style="display: none;">
                    <input type="radio" name="sexo" value="feminino" <?= $utilizador['sexo'] === 'feminino' ? 'checked' : '' ?> style="display: none;">
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Número de Identificação</label>
                    <input type="text" class="form-control" name="numero_matricula" value="<?= htmlspecialchars($utilizador['numero_matricula']) ?>" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Tipo de Utilizador</label>
                    <select class="form-select" name="tipo" required>
                        <option value="aluno" <?= $utilizador['tipo'] === 'aluno' ? 'selected' : '' ?>>Aluno</option>
                        <option value="gestor" <?= $utilizador['tipo'] === 'gestor' ? 'selected' : '' ?>>Gestor</option>
                        <option value="admin" <?= $utilizador['tipo'] === 'admin' ? 'selected' : '' ?>>Administrador</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Estado da Conta</label>
                    <div class="status-ativo">
                        <input type="checkbox" class="form-check-input" name="conta_ativa" id="conta_ativa" <?= $utilizador['conta_ativa'] ? 'checked' : '' ?>>
                        <label for="conta_ativa">Conta ativa</label>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between">
                    <button type="submit" class="btn btn-success">Salvar Alterações</button>
                    <a href="gerir-utilizadores.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </section>
    </main>
    </footer>

    <script>
        // Atualizar visual dos botões de sexo quando a página carrega
        document.addEventListener('DOMContentLoaded', function() {
            const sexoSelecionado = document.querySelector('input[name="sexo"]:checked').value;
            document.querySelectorAll('.radio-group button').forEach(btn => {
                btn.classList.remove('selected');
                if (btn.textContent.trim().toLowerCase() === sexoSelecionado) {
                    btn.classList.add('selected');
                }
            });
        });
    </script>
</body>
</html>