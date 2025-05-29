<?php
session_start();
// Apenas gestor
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'gestor') {
    header('Location: pagina-login.php'); exit();
}

// Conexão com BD
$mysqli = new mysqli('localhost','root','','Estransportado');
if ($mysqli->connect_error) {
    die('Erro de ligação: ' . $mysqli->connect_error);
}
$mysqli->set_charset('utf8mb4');

// Processar aceitar/recusar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id      = (int)$_POST['id_proposta'];

    if ($action === 'accept') {
        $stmt = $mysqli->prepare("UPDATE PropostasTransporte SET estado = 'completo' WHERE id_proposta = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
    }
    elseif ($action === 'refuse') {
        // 1) recolhemos o motivo
        $motivo = trim($_POST['motivo'] ?? '');

        // 2) marcamos a proposta como cancelada
        $stmt = $mysqli->prepare("
            UPDATE PropostasTransporte
            SET estado = 'cancelado'
            WHERE id_proposta = ?
        ");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();

        // 3) buscamos o id_aluno para só notificar esse
        $stmt = $mysqli->prepare("SELECT id_aluno FROM PropostasTransporte WHERE id_proposta = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->bind_result($id_aluno);
        $stmt->fetch();
        $stmt->close();

        // 4) inserimos a notificação para esse aluno, incluindo o motivo
        $stmt = $mysqli->prepare("
            INSERT INTO Notificacoes (id_utilizador, titulo, mensagem, tipo)
            VALUES (?, 'Proposta cancelada', ?, 'alerta')
        ");
        $mensagem = "A sua proposta #{$id} foi recusada. Motivo: {$motivo}";
        $stmt->bind_param('is', $id_aluno, $mensagem);
        $stmt->execute();
        $stmt->close();
    }

    header('Location: gerir-propostas.php');
    exit();
}

// Filtros por origem e destino
$where  = [];
$params = [];
$types  = '';

if (!empty($_GET['origem'])) {
    $where[]  = 'origem LIKE ?';
    $params[] = '%'.$_GET['origem'].'%';
    $types   .= 's';
}
if (!empty($_GET['destino'])) {
    $where[]  = 'destino LIKE ?';
    $params[] = '%'.$_GET['destino'].'%';
    $types   .= 's';
}

// Listar todas as propostas pendentes (estado = 'ativo')
$sql = "
    SELECT 
      id_proposta,
      DATE_FORMAT(data_partida,'%Y-%m-%dT%H:%i') AS data_partida,
      origem,
      destino,
      lotacao_maxima,
      preco,
      tipo
    FROM PropostasTransporte
    WHERE estado = 'ativo'
    " . (count($where) ? 'AND '.implode(' AND ',$where) : '') . "
    ORDER BY data_partida
";
$stmt = $mysqli->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="refresh" content="30">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>ESTransportado - Gerir Propostas</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://unpkg.com/boxicons@latest/css/boxicons.min.css" rel="stylesheet">
  <link href="style.css" rel="stylesheet">
  <style>
    .main-container { max-width:95%; width:1400px; margin:40px auto; padding:0 20px; }
    .tab-container  { background:#1b1b1b; border-radius:20px; padding:30px; }
    h1              { text-align:center; margin-bottom:30px; font-weight:bold; color:#fff; }
    .form-section   { display:flex; gap:15px; flex-wrap:wrap; align-items:flex-end; margin-bottom:30px; }
    .form-group label { color:#c2ff22; font-weight:600; }
    .form-group input { background:#333; color:#fff; border:1px solid #555; border-radius:10px; padding:8px; }
    .btn-registo    { background:#333; color:#fff; padding:10px 15px; border-radius:10px; text-decoration:none; }
    .btn-action     { background:#333; border:none; border-radius:10px; padding:12px; color:#fff; font-size:22px; cursor:pointer; transition:background .3s; }
    .btn-action:hover { background:#444; }
    .card-viagem    { background:#222; border-radius:15px; padding:25px 30px; margin-bottom:25px; color:#fff; }
    .card-viagem h5 { color:#c2ff22; margin-bottom:15px; }
    header { background:#c2ff22; padding:3px 40px; display:flex; align-items:center; justify-content:space-between; }
    .navbar { list-style:none; display:flex; gap:30px; margin:0; padding:0; }
    .navbar li a { display:inline-block; padding:5px 10px; color:#000; font-weight:600; border-radius:5px; transition:background .2s,color .2s; }
    .navbar li a:hover, .navbar li a.active { background:#000; color:#c2ff22; }
    #btn-entrar { background:#000; color:#c2ff22 !important; padding:8px 20px; border-radius:20px; }
  </style>
</head>
<body style="background:#121212;">

  <header>
    <a href="pagina-gestor.php" class="logo">
      <img src="imagens/logo.png" alt="ESTransportado">
    </a>

    <ul class="navbar">
      <li><a href="gerir-viagens.php"> Gerir Viagens </a></li>
      <li><a href="gerir-reservas.php"> Gerir Reservas </a></li>
      <li><a href="gerir-propostas.php"> Gerir Propostas </a></li>
    </ul>

    <a href="perfil.php" class="btn btn-primary" id="btn-entrar">Perfil</a>
  </header>

  <div class="main-container">
    <h1>Gerir Propostas de Transporte</h1>
    <div class="tab-container">

      <form method="get" class="form-section">
        <div class="form-group">
          <label>Origem</label>
          <input type="text" name="origem" value="<?=htmlspecialchars($_GET['origem']??'')?>">
        </div>
        <div class="form-group">
          <label>Destino</label>
          <input type="text" name="destino" value="<?=htmlspecialchars($_GET['destino']??'')?>">
        </div>
        <button class="btn-registo" type="submit">Filtrar</button>
      </form>

      <?php if ($result->num_rows === 0): ?>
        <div class="no-registros" style="color:#fff;">Nenhuma proposta encontrada.</div>
      <?php else: ?>
        <?php while ($p = $result->fetch_assoc()): ?>
          <div class="card-viagem">
            <h5><?=htmlspecialchars($p['origem'])?> → <?=htmlspecialchars($p['destino'])?></h5>
            <p><i class='bx bx-calendar'></i> <?=date('d/m/Y H:i', strtotime($p['data_partida']))?></p>
            <p>
              <i class='bx bx-money'></i> €<?=number_format($p['preco'],2,',','.')?> |
              <i class='bx bx-group'></i> <?=$p['lotacao_maxima']?> lugares |
              <i class='bx bx-car'></i> <?=ucfirst($p['tipo'])?>
            </p>
            <div class="d-flex gap-2">
              <!-- Aceitar -->
              <form method="post" class="m-0 flex-fill">
                <input type="hidden" name="action" value="accept">
                <input type="hidden" name="id_proposta" value="<?=$p['id_proposta']?>">
                <button class="btn-action btn-success w-100" onclick="return confirm('Aceitar esta proposta?');">
                  <i class='bx bx-check'></i>
                </button>
              </form>
              <!-- Recusar com prompt de motivo -->
              <form method="post" class="m-0 flex-fill" onsubmit="return handleRefuse(this);">
                <input type="hidden" name="action" value="refuse">
                <input type="hidden" name="id_proposta" value="<?=$p['id_proposta']?>">
                <button class="btn-action btn-danger w-100">
                  <i class='bx bx-x'></i>
                </button>
              </form>
            </div>
          </div>
        <?php endwhile; ?>
      <?php endif; ?>

    </div>
  </div>

  <script>
    function handleRefuse(form) {
      var motivo = prompt('Por favor, insira o motivo da recusa:');
      if (motivo === null || motivo.trim() === '') {
        alert('Recusa cancelada: é necessário informar o motivo.');
        return false;
      }
      var inp = document.createElement('input');
      inp.type = 'hidden';
      inp.name = 'motivo';
      inp.value = motivo.trim();
      form.appendChild(inp);
      return confirm('Confirmar recusa desta proposta?');
    }
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
