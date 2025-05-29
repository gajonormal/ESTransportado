<?php
session_start();

// Verificar se o usuário está logado e é aluno
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'aluno') {
    header("Location: pagina-login.php");
    exit();
}

// Incluir conexão com o banco de dados
require_once 'basedados/basedados.h';

// Obter o ID do aluno logado
$id_aluno = $_SESSION['user_id'];

// Obter propostas criadas pelo aluno
$propostas = [];
$stmt = $conn->prepare("SELECT * FROM propostastransporte WHERE id_aluno = ? ORDER BY data_criacao DESC");
$stmt->bind_param("i", $id_aluno);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $propostas[] = $row;
}

// Obter viagens efetuadas pelo aluno
$viagens = [];
$stmt = $conn->prepare("
    SELECT v.*, r.data_reserva 
    FROM Viagens v 
    JOIN Reservas r ON v.id_viagem = r.id_viagem 
    JOIN Passageiros p ON r.id_passageiro = p.id_passageiro 
    WHERE p.id_utilizador = ? 
    ORDER BY v.data_partida DESC
");
$stmt->bind_param("i", $id_aluno);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $viagens[] = $row;
}

// Obter avaliações enviadas pelo aluno
$avaliacoes = [];
$stmt = $conn->prepare("
    SELECT a.*, v.origem, v.destino, v.data_partida 
    FROM Avaliacoes a 
    JOIN Viagens v ON a.id_viagem = v.id_viagem 
    WHERE a.id_avaliador = ? 
    ORDER BY a.data_avaliacao DESC
");
$stmt->bind_param("i", $id_aluno);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $avaliacoes[] = $row;
}

// Obter avaliações recebidas pelo aluno
$avaliacoes_recebidas = [];
$stmt = $conn->prepare("
    SELECT a.*, v.origem, v.destino, v.data_partida, u.nome_completo as nome_avaliador
    FROM Avaliacoes a 
    JOIN Viagens v ON a.id_viagem = v.id_viagem 
    JOIN Utilizadores u ON a.id_avaliador = u.id_utilizador
    JOIN Reservas r ON v.id_viagem = r.id_viagem
    JOIN Passageiros p ON r.id_passageiro = p.id_passageiro
    WHERE p.id_utilizador = ? 
    ORDER BY a.data_avaliacao DESC
");
$stmt->bind_param("i", $id_aluno);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $avaliacoes_recebidas[] = $row;
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
    <a href="pagina-aluno.php" class="logo">
      <img src="imagens/logo.png" alt="ESTransportado">
    </a>

    <ul class="navbar">
      <li><a href="as-minhas-reservas.php">As minhas reservas</a></li>
      <li><a href="consultar-horarios.php">Consultar horários</a></li>
      <li><a href="historico-aluno.php">Histórico</a></li>
      <li><a href="ajuda.php">Ajuda</a></li>
    </ul>

    <a href="perfil.php" class="btn btn-primary" id="btn-entrar">Perfil</a>
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

    .tabs button {
      background-color: #333;
      border: none;
      padding: 10px 20px;
      color: white;
      border-radius: 12px;
      font-weight: bold;
      cursor: pointer;
    }

    .tabs button.active {
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

    .item .icons {
      display: flex;
      gap: 10px;
    }

    .item .icons button {
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
    }

    .item .icons button:hover {
      background-color: #555;
    }

    h2 {
      text-align: center;
      margin-bottom: 30px;
      font-weight: bold;
    }
  </style>
</head>

<main>
  <section class="form-section" id="perfil-aluno">
    <h2>Histórico de ações</h2>

    <div class="tabs">
      <button class="active" onclick="mostrarTab('propostas')">Propostas criadas</button>
      <button onclick="mostrarTab('viagens')">Viagens efetuadas</button>
      <button onclick="mostrarTab('avaliacoes')">Avaliações enviadas</button>
      <button onclick="mostrarTab('avaliacoes-recebidas')">Avaliações recebidas</button>
    </div>

    <!-- Propostas criadas -->
    <div id="propostas" class="tab-content">
      <?php if (empty($propostas)): ?>
        <div class="no-items">Nenhuma proposta criada</div>
      <?php else: ?>
        <?php foreach ($propostas as $proposta): ?>
          <div class="item">
            <span>
              Proposta: <?php echo htmlspecialchars($proposta['origem']); ?> → 
              <?php echo htmlspecialchars($proposta['destino']); ?> 
              (<?php echo date('d/m/Y', strtotime($proposta['data_criacao'])); ?>)
            </span>
            <div class="icons">
              <button title="Editar" onclick="editarProposta(<?php echo $proposta['id_proposta']; ?>)">
                <i class='bx bx-pencil'></i>
              </button>
              <button title="Eliminar" onclick="eliminarProposta(<?php echo $proposta['id_proposta']; ?>)">
                <i class='bx bx-trash'></i>
              </button>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <!-- Viagens efetuadas -->
    <div id="viagens" class="tab-content" style="display: none;">
      <?php if (empty($viagens)): ?>
        <div class="no-items">Nenhuma viagem efetuada</div>
      <?php else: ?>
        <?php foreach ($viagens as $viagem): ?>
          <div class="item">
            <span>
              Viagem: <?php echo htmlspecialchars($viagem['origem']); ?> → 
              <?php echo htmlspecialchars($viagem['destino']); ?> 
              (<?php echo date('d/m/Y', strtotime($viagem['data_partida'])); ?>)
            </span>
            <div class="icons">
              <button title="Detalhes" onclick="verDetalhesViagem(<?php echo $viagem['id_viagem']; ?>)">
                <i class='bx bx-info-circle'></i>
              </button>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <!-- Avaliações enviadas -->
    <div id="avaliacoes" class="tab-content">
      <?php if (empty($avaliacoes)): ?>
        <div class="no-items">Nenhuma avaliação enviada</div>
      <?php else: ?>
        <?php foreach ($avaliacoes as $avaliacao): ?>
          <div class="item">
            <span>
              Avaliação: <?php echo htmlspecialchars($avaliacao['origem']); ?> → 
              <?php echo htmlspecialchars($avaliacao['destino']); ?> 
              (<?php echo date('d/m/Y', strtotime($avaliacao['data_avaliacao'])); ?>)
              - <?php echo $avaliacao['classificacao']; ?> estrelas
            </span>
            <div class="icons">
              <button title="Ver detalhes" onclick="verDetalhesAvaliacao(<?php echo $avaliacao['id_avaliacao']; ?>)">
                <i class='bx bx-info-circle'></i>
              </button>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <!-- Avaliações recebidas -->
    <div id="avaliacoes-recebidas" class="tab-content" style="display: none;">
      <?php if (empty($avaliacoes_recebidas)): ?>
        <div class="no-items">Nenhuma avaliação recebida</div>
      <?php else: ?>
        <?php foreach ($avaliacoes_recebidas as $avaliacao): ?>
          <div class="item">
            <span>
              Avaliação de <?php echo htmlspecialchars($avaliacao['nome_avaliador']); ?>: 
              <?php echo htmlspecialchars($avaliacao['origem']); ?> → 
              <?php echo htmlspecialchars($avaliacao['destino']); ?> 
              (<?php echo date('d/m/Y', strtotime($avaliacao['data_avaliacao'])); ?>)
              - <?php echo $avaliacao['classificacao']; ?> estrelas
              <?php if ($avaliacao['comentario']): ?>
                <br>
                <small>Comentário: <?php echo htmlspecialchars($avaliacao['comentario']); ?></small>
              <?php endif; ?>
            </span>
            <div class="icons">
              <button title="Ver detalhes" onclick="verDetalhesAvaliacao(<?php echo $avaliacao['id_avaliacao']; ?>)">
                <i class='bx bx-info-circle'></i>
              </button>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </section>
</main>

<script>
function mostrarTab(tabId) {
  // Esconder todas as tabs
  document.querySelectorAll('.tab-content').forEach(tab => {
    tab.style.display = 'none';
  });
  
  // Mostrar a tab selecionada
  document.getElementById(tabId).style.display = 'block';
  
  // Atualizar botões
  document.querySelectorAll('.tabs button').forEach(button => {
    button.classList.remove('active');
  });
  event.target.classList.add('active');
}

function editarProposta(id) {
  window.location.href = `editar-proposta.php?id=${id}`;
}

function eliminarProposta(id) {
  if (confirm('Tem certeza que deseja eliminar esta proposta?')) {
    window.location.href = `eliminar-proposta.php?id=${id}`;
  }
}

function verDetalhesViagem(id) {
  window.location.href = `detalhes-viagem.php?id=${id}`;
}

function verDetalhesAvaliacao(id) {
  window.location.href = `detalhes-avaliacao.php?id=${id}`;
}
</script>

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