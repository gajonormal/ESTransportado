<?php
session_start();

// Verificar se o usuário está logado e é admin
if (!isset($_SESSION['user_id'])) {
    header("Location: pagina-login.php");
    exit();
}

// Verificar se o usuário é admin
if ($_SESSION['user_type'] !== 'admin') {
    header("Location: login.php"); // Redireciona se não for admin
    exit();
}

// Incluir conexão com o banco de dados
require_once 'basedados/basedados.h';

// Processar ações (aceitar/recusar registos)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['acao'])) {
        $id_utilizador = $_POST['id_utilizador'];
        $acao = $_POST['acao'];
        
        if ($acao === 'aceitar') {
            // Ativar conta do utilizador
            $stmt = $conn->prepare("UPDATE Utilizadores SET conta_ativa = TRUE WHERE id_utilizador = ?");
            $stmt->bind_param("i", $id_utilizador);
            $stmt->execute();
        } elseif ($acao === 'recusar') {
            // Desativar conta do utilizador
            $stmt = $conn->prepare("UPDATE Utilizadores SET conta_ativa = FALSE WHERE id_utilizador = ?");
            $stmt->bind_param("i", $id_utilizador);
            $stmt->execute();
        }
        
        // Recarregar a página para atualizar a lista
        header("Location: gerir-registos.php");
        exit();
    }
}

// Obter registos pendentes (conta_ativa = FALSE)
$registos_pendentes = [];
$stmt = $conn->prepare("SELECT * FROM Utilizadores WHERE conta_ativa = FALSE ORDER BY data_registo DESC");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $registos_pendentes[] = $row;
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ESTransportado</title>

  <!-- BOOTSTRAP 5 CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

  <!-- CSS Personalizado -->
  <link rel="stylesheet" href="style.css">

  <!-- BOXICONS -->
  <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
</head>

<header>
    <a href="pagina-admin.php" class="logo">
      <img src="imagens/logo.png" alt="ESTransportado">
    </a>
</header>

<body>
  <style>
    .tab-container {
      background: #c2ff22;
      border-radius: 20px;
      padding: 20px;
      max-width: 800px;
      margin: 50px auto;
    }

    .tabs {
      display: flex;
      justify-content: center;
      margin-bottom: 20px;
      gap: 10px;
    }

    .tabs .btn-tab {
        background-color: #333;
        border: none;
        padding: 10px 20px;
        color: white;
        border-radius: 12px;
        font-weight: bold;
        text-decoration: none;
    }

    .tabs .btn-tab.active {
      background-color: #c2ff22;
      color: black;
    }

    .item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      background-color: #222;
      border-radius: 15px;
      padding: 15px 20px;
      margin-bottom: 15px;
      color: white;
    }

    .item .user-info {
      flex-grow: 1;
      color: white;
    }

    .item .icons {
      display: flex;
      gap: 10px;
    }

    .btn-icon {
        background-color: #333;
        border: none;
        border-radius: 10px;
        padding: 10px;
        color: white;
        font-size: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        text-decoration: none;
    }

    .btn-icon:hover {
        background-color: #555;
    }

    h2 {
      text-align: center;
      margin-bottom: 30px;
      font-weight: bold;
    }
    
    .no-registros {
      text-align: center;
      padding: 20px;
      color: white;
      background-color: #222;
      border-radius: 15px;
    }
  </style>

  <main>
    <section class="form-section" id="perfil-aluno">
        <h2>Gerir registos</h2>

        <div class="tab-container">
          <?php if (empty($registos_pendentes)): ?>
            <div class="no-registros">Não há registros pendentes</div>
          <?php else: ?>
            <?php foreach ($registos_pendentes as $registo): ?>
              <div class="item">
                <div class="user-info">
                  <?= htmlspecialchars($registo['nome_completo']) ?> (<?= htmlspecialchars($registo['email_institucional']) ?>)
                  <div style="font-size: 0.8em; color: #ccc;">
                    Matrícula: <?= htmlspecialchars($registo['numero_matricula']) ?> | 
                    Registado em: <?= date('d/m/Y H:i', strtotime($registo['data_registo'])) ?>
                  </div>
                </div>
                <div class="icons">
                  <form method="POST" style="display: inline;">
                    <input type="hidden" name="id_utilizador" value="<?= $registo['id_utilizador'] ?>">
                    <input type="hidden" name="acao" value="aceitar">
                    <button type="submit" title="Aceitar" class="btn-icon"><i class='bx bxs-user-check'></i></button>
                  </form>
                  <form method="POST" style="display: inline;">
                    <input type="hidden" name="id_utilizador" value="<?= $registo['id_utilizador'] ?>">
                    <input type="hidden" name="acao" value="recusar">
                    <button type="submit" title="Recusar" class="btn-icon"><i class='bx bx-x'></i></button>
                  </form>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
    </section>
  </main>

  <!-- Rodapé -->
  <footer class="rodape">
    <div class="container">
      <div class="row">
        <!-- Sobre -->
        <div class="col-md-4">
          <div class="rodape-sobre">
            <h3>Sobre a <span>EST</span>ransportado</h3>
            <p>A ESTransportado oferece soluções de transporte eficientes e acessíveis para estudantes, ligando-os com as suas instituições de ensino.</p>
          </div>
        </div>
        <!-- Links Rápidos -->
        <div class="col-md-4">
          <div class="rodape-links">
          </div>
        </div>
        <!-- Contacto -->
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
</body>
</html>