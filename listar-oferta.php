<?php
session_start();
require_once 'basedados/basedados.h';

if (!isset($_SESSION['user_id'])) {
    header("Location: pagina-login.php");
    exit();
}

// Buscar propostas do usuário logado
$query = "SELECT p.*, 
                 DATE_FORMAT(p.data_partida, '%H:%i') as hora_partida,
                 DATE_FORMAT(p.data_partida, '%H:%i') as hora_chegada_estimada
          FROM PropostasTransporte p
          JOIN Utilizadores u ON p.id_aluno = u.id_utilizador
          WHERE u.id_utilizador = ? AND p.estado = 'ativo'
          ORDER BY p.data_partida ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$propostas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Minhas Propostas - ESTransportado</title>

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
    .form-section, .results-section {
      margin-bottom: 30px;
    }
    .trip-type {
      display: flex;
      gap: 10px;
      margin-bottom: 20px;
    }
    .trip-type button {
      flex: 1;
      padding: 10px;
      border: none;
      cursor: pointer;
      background: #333;
      color: white;
      border-radius: 5px;
    }
    .trip-type button.active {
      background: #c2ff22;
      color: black;
    }
    .form-grid {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
    }
    .form-group {
      flex: 1;
      min-width: 150px;
    }
    .form-group input {
      width: 100%;
      padding: 10px;
      border: none;
      border-radius: 5px;
      background: #333;
      color: white;
    }
    .form-group label {
      display: block;
      margin-bottom: 5px;
    }
    
    .viagem {
      background: #222;
      padding: 15px;
      border-radius: 10px;
      margin-bottom: 15px;
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

    .proposta-viagem {
      background: #111;
      border: 2px dashed #c2ff22;
      text-align: center;
      margin: 20px auto 0 auto;
      margin-bottom: 20px;
      padding: 20px;
      border-radius: 10px;
      max-width: 800px;
    }

    .proposta-viagem p {
      color: white;
      margin: 10px 0;
      font-size: 1rem;
    }

    .proposta-viagem p strong {
      color: #c2ff22;
      font-size: 1.1rem;
    }

    .proposta-viagem button {
      background: #c2ff22;
      color: black;
      font-weight: bold;
      padding: 12px 24px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-size: 1rem;
      margin-top: 15px;
    }

    .page-title {
      color: #c2ff22;
      text-align: center;
      margin-bottom: 30px;
    }

    .btn-primary {
      background-color: #c2ff22;
      color: black;
      border: none;
      margin-right: 10px;
    }

    .btn-danger {
      background-color: #ff4444;
      color: white;
      border: none;
    }
  </style>
</head>
<body>
    <header>
        <a href="pagina_inicial.php" class="logo">
          <img src="imagens/logo.png" alt="ESTransportado">
        </a>
        
        <ul class="navbar">
          <li><a href="servicos.php">Serviços</a></li>
          <li><a href="sobrenos.php">Sobre nós</a></li>
          <li><a href="contactos.php">Contactos</a></li>
          <li><a href="ajuda.php">Ajuda</a></li>
        </ul>
        
        <a href="logout.php" class="btn btn-primary" id="btn-entrar">Sair</a>
    </header>

    <section class="contactos">
        <h2 class="page-title">Suas Propostas de Transporte</h2>
        
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success" style="max-width: 800px; margin: 0 auto 20px auto;">
                <?= htmlspecialchars($_SESSION['success_message']) ?>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        
        <div class="container">
            <div class="results-section">
                <?php if (empty($propostas)): ?>
                    <div class="proposta-viagem">
                        <p>Você ainda não criou nenhuma proposta de transporte.</p>
                        <a href="criar-proposta.php" class="btn btn-primary">Criar Proposta</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($propostas as $proposta): ?>
                        <div class="viagem">
                            <p>
                                <strong><?= htmlspecialchars($proposta['hora_partida']) ?></strong> - 
                                <?= htmlspecialchars($proposta['origem']) ?> → 
                                <strong><?= htmlspecialchars($proposta['hora_chegada_estimada']) ?></strong> - 
                                <?= htmlspecialchars($proposta['destino']) ?>
                            </p>
                            <p>
                                Lotação: <?= htmlspecialchars($proposta['lotacao_maxima']) ?> lugares | 
                                Preço: €<?= number_format($proposta['preco'], 2) ?> | 
                                Tipo: <?= $proposta['tipo'] === 'publico' ? 'Público' : 'Privado' ?>
                            </p>
                            <a href="editar-proposta.php?id=<?= $proposta['id_proposta'] ?>" class="btn btn-primary">Editar</a>
                            <a href="eliminar-proposta.php?id=<?= $proposta['id_proposta'] ?>" class="btn btn-danger" 
                               onclick="return confirm('Tem certeza que deseja eliminar esta proposta?')">Eliminar</a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

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
</body>
</html>