<?php
session_start();

// Verificar se o usuário está logado e é aluno
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'aluno') {
    header("Location: pagina-login.php");
    exit();
}

// Incluir conexão com o banco de dados
require_once 'basedados/basedados.h';

// Consulta para obter todas as viagens ativas
$query = "SELECT * FROM Viagens WHERE estado = 'ativo' ORDER BY estado ASC";
$result = mysqli_query($conn, $query);
$viagens = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>


<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ESTransportado - Viagens Ativas</title>

  <!-- BOOTSTRAP 5 CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

  <!-- CSS Personalizado -->
  <link rel="stylesheet" href="style.css">

  <!-- BOXICONS -->
  <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">

  <style>
    .container {
      max-width: 800px;
      margin: 30px auto;
      background: #111;
      padding: 20px;
      border-radius: 10px;
    }
    .results-section {
      margin-bottom: 30px;
    }
    
    .viagem {
      background: #222;
      padding: 15px;
      border-radius: 10px;
      margin-bottom: 15px;
      position: relative;
    }
    .viagem strong {
      color: #c2ff22;
    }
    .viagem button {
      margin-top: 10px;
      padding: 10px;
      background: #c2ff22;
      border: none;
      border-radius: 5px;
      font-weight: bold;
      cursor: pointer;
    }

    .tipo-transporte {
      position: absolute;
      top: 10px;
      right: 15px;
      background-color: #c2ff22;
      color: black;
      padding: 5px 10px;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: bold;
      text-transform: uppercase;
    }
    
    .lotacao {
      font-size: 0.9rem;
      color: #aaa;
    }
    
    .btn-reservar {
      width: 100%;
      background: #c2ff22;
      color: black;
      font-weight: bold;
      border: none;
      padding: 8px;
      border-radius: 5px;
      cursor: pointer;
      transition: background 0.3s;
    }
    
    .btn-reservar:hover {
      background: #a8e01e;
    }
    
    .no-results {
      text-align: center;
      padding: 20px;
      color: #aaa;
    }
    
    .page-title {
      color: #c2ff22;
      text-align: center;
      margin-bottom: 20px;
    }
    
    .viagem-info {
      display: flex;
      justify-content: space-between;
      margin-bottom: 5px;
    }
    
    .viagem-rota {
      font-weight: bold;
      margin-bottom: 5px;
    }
    
    .viagem-data {
      color: #ccc;
      font-size: 0.9rem;
    }
  </style>
</head>
<body>
  <header>
    <a href="pagina-aluno.php" class="logo">
      <img src="imagens/logo.png" alt="ESTransportado">
    </a>

    <ul class="navbar">
      <li><a href="">Serviços</a></li>
      <li><a href="">Sobre nós</a></li>
      <li><a href="">Contactos</a></li>
      <li><a href="ajuda.php">Ajuda</a></li>
    </ul>
    
    <a href="perfil.php" class="btn btn-primary" id="btn-entrar">Perfil</a>
  </header>

  <div class="container">
    <h1 class="page-title">Todas as Viagens Ativas</h1>

    <div class="results-section">
      <?php if (empty($viagens)): ?>
        <div class="no-results">
          <p>Nenhuma viagem ativa disponível no momento.</p>
        </div>
      <?php else: ?>
        <?php foreach ($viagens as $viagem): 
          // Formatar datas e horas
          $data_partida = date('d/m/Y H:i', strtotime($viagem['data_partida']));
          $data_chegada = date('d/m/Y H:i', strtotime($viagem['data_chegada']));
          $hora_partida = date('H:i', strtotime($viagem['data_partida']));
          $hora_chegada = date('H:i', strtotime($viagem['data_chegada']));
          
          // Calcular duração
          $partida = new DateTime($viagem['data_partida']);
          $chegada = new DateTime($viagem['data_chegada']);
          $duracao = $partida->diff($chegada);
          $duracao_formatada = $duracao->format('%hh %imin');
          
          // Verificar lotação
          $vagas_disponiveis = $viagem['lotacao_maxima'] - $viagem['lotacao_atual'];
          
          // Verificar se a viagem já ocorreu
          $data_atual = new DateTime();
          $viagem_ocorreu = ($partida < $data_atual);
        ?>
          <div class="viagem">
            <span class="tipo-transporte"><?php echo $viagem['tipo'] === 'publico' ? 'Público' : 'Privado'; ?></span>
            
            <div class="viagem-rota">
              <?php echo htmlspecialchars($viagem['origem']); ?> → <?php echo htmlspecialchars($viagem['destino']); ?>
            </div>
            
            <div class="viagem-info">
              <div>
                <strong>Partida:</strong> <?php echo $data_partida; ?>
              </div>
              <div>
                <strong>Chegada:</strong> <?php echo $data_chegada; ?>
              </div>
            </div>
            
            <div class="viagem-info">
              <div>
                <strong>Duração:</strong> <?php echo $duracao_formatada; ?>
              </div>
              <div>
                <strong>Preço:</strong> €<?php echo number_format($viagem['preco'], 2, ',', '.'); ?>
              </div>
            </div>
            
            <p class="lotacao">
              Vagas disponíveis: <?php echo $vagas_disponiveis; ?>/<?php echo $viagem['lotacao_maxima']; ?>
              <?php if ($viagem_ocorreu): ?>
                <span style="color: #ff5555;"> (Viagem já ocorreu)</span>
              <?php endif; ?>
            </p>
            
            <form method="POST" action="concluir-reserva.html">
              <input type="hidden" name="id_viagem" value="<?php echo $viagem['id_viagem']; ?>">
              <button type="submit" class="btn-reservar" 
                <?php echo ($vagas_disponiveis <= 0 || $viagem_ocorreu) ? 'disabled' : ''; ?>>
                <?php 
                  if ($viagem_ocorreu) {
                    echo 'VIAGEM REALIZADA';
                  } elseif ($vagas_disponiveis <= 0) {
                    echo 'LOTADO';
                  } else {
                    echo 'RESERVAR';
                  }
                ?>
              </button>
            </form>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</body>

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
</html>