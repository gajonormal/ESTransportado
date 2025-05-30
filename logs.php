<?php
session_start();

// Verificar se o usuário está logado e é admin
if (empty($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'admin') {
    header("Location: pagina-login.php");
    exit();
}

// Incluir conexão com o banco de dados
require_once 'basedados/basedados.h';

// Consultar estatísticas gerais
$queryStats = "
    SELECT 
        (SELECT COUNT(*) FROM Utilizadores) AS total_utilizadores,
        (SELECT COUNT(*) FROM Viagens WHERE estado = 'ativo') AS viagens_ativas,
        (SELECT COUNT(*) FROM Viagens WHERE estado = 'completo') AS viagens_concluidas,
        (SELECT COUNT(*) FROM Denuncias WHERE estado = 'pendente') AS denuncias_pendentes,
        (SELECT AVG(classificacao) FROM Avaliacoes) AS media_avaliacoes
";
$resultStats = mysqli_query($conn, $queryStats);
$stats = mysqli_fetch_assoc($resultStats);

// Consultar estatísticas diárias (últimos 7 dias)
$queryDaily = "
    SELECT data_referencia, total_utilizadores, viagens_ativas, viagens_concluidas, denuncias_pendentes, avaliacao_media
    FROM Estatisticas
    ORDER BY data_referencia DESC
    LIMIT 7
";
$resultDaily = mysqli_query($conn, $queryDaily);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin | Logs & Estatísticas</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
  <style>
    body { background: #111; color: #fff; }
    .logo {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      text-decoration: none;
    }
    .logo img {
      height: 75px;
    }
    .logo span {
      font-size: 1.5rem;
      font-weight: bold;
      color: #c2ff22;
    }
    .main-container { max-width: 95%; margin: 40px auto; padding: 0 20px; }
    h1 { text-align: center; margin-bottom: 30px; font-weight: bold; color: #c2ff22; }
    .stats-cards { display: flex; flex-wrap: wrap; gap: 1rem; justify-content: center; }
    .stats-card { background: #222; border-radius: 15px; padding: 20px 25px; min-width: 200px; text-align: center; }
    .stats-card h3 { color: #c2ff22; font-size: 1.1em; margin-bottom: .5em; }
    .stats-card p { font-size: 1.5em; margin: 0; }
    .log-table { background: #222; border-radius: 15px; padding: 20px; margin-top: 30px; }
    table { color: #222; }
    thead th { border-bottom: 1px solid #444; }
    tbody tr { border-bottom: 1px solid #333; }
  </style>
</head>
<body>
  <header>
    <div class="header-container">
      <a href="pagina-admin.php" class="logo">
        <img src="imagens/logo.png" alt="ESTransportado">
      </a>

    </div>
  </header>
  <div class="main-container">
    <h1>Painel de Logs & Estatísticas</h1>
    <div class="stats-cards">
      <div class="stats-card">
        <h3>Total de Utilizadores</h3>
        <p><?= htmlspecialchars($stats['total_utilizadores']) ?></p>
      </div>
      <div class="stats-card">
        <h3>Viagens Ativas</h3>
        <p><?= htmlspecialchars($stats['viagens_ativas']) ?></p>
      </div>
      <div class="stats-card">
        <h3>Viagens Concluídas</h3>
        <p><?= htmlspecialchars($stats['viagens_concluidas']) ?></p>
      </div>
      <div class="stats-card">
        <h3>Denúncias Pendentes</h3>
        <p><?= htmlspecialchars($stats['denuncias_pendentes']) ?></p>
      </div>
      <div class="stats-card">
        <h3>Média de Avaliações</h3>
        <p><?= number_format($stats['media_avaliacoes'],2) ?></p>
      </div>
    </div>
    <div class="log-table">
      <h2 class="text-center text-white mb-3">Estatísticas Diárias (Últimos 7 dias)</h2>
      <table class="table table-striped">
        <thead>
          <tr>
            <th>Data</th>
            <th>Utilizadores</th>
            <th>Ativas</th>
            <th>Concluídas</th>
            <th>Denúncias</th>
            <th>Média Avaliações</th>
          </tr>
        </thead>
        <tbody>
          <?php if(mysqli_num_rows($resultDaily) == 0): ?>
            <tr><td colspan="6" class="text-center">Sem registros</td></tr>
          <?php else: ?>
            <?php while($row = mysqli_fetch_assoc($resultDaily)): ?>
            <tr>
              <td><?= htmlspecialchars($row['data_referencia']) ?></td>
              <td><?= htmlspecialchars($row['total_utilizadores']) ?></td>
              <td><?= htmlspecialchars($row['viagens_ativas']) ?></td>
              <td><?= htmlspecialchars($row['viagens_concluidas']) ?></td>
              <td><?= htmlspecialchars($row['denuncias_pendentes']) ?></td>
              <td><?= number_format($row['avaliacao_media'],2) ?></td>
            </tr>
            <?php endwhile; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <footer class="rodape mt-5">
    <div class="container text-center text-white">&copy; 2025 ESTransportado. Todos os direitos reservados.</div>
  </footer>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
