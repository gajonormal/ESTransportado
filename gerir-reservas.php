<?php
session_start();
date_default_timezone_set('Europe/Lisbon');

// Apenas gestor
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'gestor') {
    header('Location: pagina-login.php');
    exit();
}

$mysqli = new mysqli('localhost','root','','Estransportado');
if ($mysqli->connect_error) {
    die('Erro de ligação: ' . $mysqli->connect_error);
}
$mysqli->set_charset('utf8mb4');

// ===== Ações POST: editar ou eliminar reserva =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id     = (int)$_POST['id_reserva'];

    if ($action === 'delete_reserva') {
        $stmt = $mysqli->prepare("DELETE FROM Reservas WHERE id_reserva=?");
        $stmt->bind_param('i',$id);
        $stmt->execute();
        $stmt->close();
    }
    elseif ($action === 'edit_reserva') {
        $lugar  = $_POST['lugar'];
        $preco  = $_POST['preco_total'];
        $stmt = $mysqli->prepare("
          UPDATE Reservas
             SET lugar = ?, preco_total = ?
           WHERE id_reserva = ?
        ");
        $stmt->bind_param('sdi',$lugar,$preco,$id);
        $stmt->execute();
        $stmt->close();
    }

    header('Location: gerir-reservas.php');
    exit();
}

// ===== Filtros GET =====
$where = ['v.ativo=1'];
$params = []; $types = '';

if (!empty($_GET['viagem'])) {
    $where[]   = 'r.id_viagem = ?';
    $params[]  = (int)$_GET['viagem'];
    $types    .= 'i';
}
if (!empty($_GET['passageiro'])) {
    $where[]   = "(p.primeiro_nome LIKE ? OR p.sobrenome LIKE ?)";
    $params[]  = '%'.$_GET['passageiro'].'%';
    $params[]  = '%'.$_GET['passageiro'].'%';
    $types    .= 'ss';
}
if (!empty($_GET['data_reserva'])) {
    $where[]   = 'DATE(r.data_reserva) = ?';
    $params[]  = $_GET['data_reserva'];
    $types    .= 's';
}

// ===== Carregar reservas =====
$sql = "
  SELECT
    r.id_reserva,
    v.origem, v.destino,
    r.data_reserva,
    CONCAT(p.primeiro_nome,' ',p.sobrenome) AS passageiro,
    r.lugar, r.preco_total
  FROM Reservas r
  JOIN Viagens    v ON r.id_viagem    = v.id_viagem
  JOIN Passageiros p ON r.id_passageiro = p.id_passageiro
  WHERE ".implode(' AND ',$where)."
  ORDER BY r.data_reserva DESC
";
$stmt = $mysqli->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="refresh" content="30">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>ESTransportado – Gerir Reservas</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
   <link rel="stylesheet" href="style.css">
  <link href="https://unpkg.com/boxicons@latest/css/boxicons.min.css" rel="stylesheet">
  <style>
    /* Navbar maior */
    

    body {
      background: #121212;
      color: #fff;
      margin: 0;
    }
    .main-container {
      max-width: 95%;
      width: 1400px;
      margin: 40px auto;
      padding: 0 20px;
    }
    h1 {
      text-align: center;
      margin-bottom: 30px;
      font-weight: bold;
    }
    .tab-container {
      background: #1b1b1b;
      padding: 30px;
      border-radius: 20px;
    }

    /* Filtros */
    .form-section {
      display: flex;
      flex-wrap: wrap;
      gap: 15px;
      margin-bottom: 20px;
    }
    .form-group label {
      color: #c2ff22;
      font-weight: 600;
      display: block;
      margin-bottom: 4px;
    }
    .form-group input,
    .form-group select {
      background: #333;
      color: #fff;
      border: 1px solid #555;
      border-radius: 10px;
      padding: 6px 8px;
      width: 100%;
    }
    .btn-registo {
      background: #333;
      color: #fff;
      border: none;
      padding: 8px 12px;
      border-radius: 8px;
      cursor: pointer;
    }
    .btn-registo:hover {
      background: #444;
    }

    /* Tabela */
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
    }
    th, td {
      padding: 8px;
      border-bottom: 1px solid #333;
      vertical-align: middle;
    }
    .btn-action {
      background: #333;
      color: #fff;
      border: none;
      padding: 4px 8px;
      border-radius: 6px;
      cursor: pointer;
      margin-right: 4px;
    }
    .btn-action:hover {
      background: #444;
    }

    /* Modal de edição */
    .modal-content {
      background: #1e1e1e !important;
      color: #fff !important;
    }
    .modal-body .form-label {
      color: #c2ff22 !important;
    }
    .modal-body .form-control {
      background: #333 !important;
      color: #fff !important;
      border: 1px solid #555 !important;
    }
    .modal-body .form-control::placeholder {
      color: #bbb !important;
    }
    .modal-footer .btn-secondary {
      background: #555 !important;
      color: #fff !important;
    }
    .modal-footer .btn-secondary:hover {
      background: #666 !important;
    }
    .modal-footer .btn-primary {
      background: #c2ff22 !important;
      color: #000 !important;
    }
    .modal-footer .btn-primary:hover {
      background: #a8d810 !important;
    }
  </style>
</head>
<body>

  <header>
    <a href="pagina-gestor.php" class="logo">
      <img src="imagens/logo.png" alt="ESTransportado">
    </a>

    <ul class="navbar">
      <li><a href="gerir-viagens.php"> Gerir  Viagens </a></li>
      <li><a href="gerir-reservas.php">Gerir Reservas</a></li>
      <li><a href="gerir-propostas.php">Gerir Propostas</a></li>
     
      
    

      
      
      </div>


    <a href="perfil.php" class="btn btn-primary" id="btn-entrar">Perfil</a>
  </header>

  <div class="main-container">
    <h1>Gerir Reservas</h1>
    <div class="tab-container">
      <!-- filtros -->
      <form method="get" class="form-section">
        <div class="form-group" style="flex:1;min-width:120px;">
          <label>Viagem (ID)</label>
          <input type="number" name="viagem" value="<?=htmlspecialchars($_GET['viagem']??'')?>">
        </div>
        <div class="form-group" style="flex:2;min-width:180px;">
          <label>Passageiro</label>
          <input type="text" name="passageiro" placeholder="nome" value="<?=htmlspecialchars($_GET['passageiro']??'')?>">
        </div>
        <div class="form-group" style="flex:1;min-width:140px;">
          <label>Data</label>
          <input type="date" name="data_reserva" value="<?=htmlspecialchars($_GET['data_reserva']??'')?>">
        </div>
        <button type="submit" class="btn-registo" style="align-self:flex-end;">Filtrar</button>
      </form>

      <!-- lista de reservas -->
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Viagem</th>
            <th>Passageiro</th>
            <th>Quando</th>
            <th>Lugar</th>
            <th>Preço</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
        <?php while($r = $res->fetch_assoc()): ?>
          <tr>
            <td><?= $r['id_reserva'] ?></td>
            <td><?= htmlspecialchars($r['origem'].' → '.$r['destino']) ?></td>
            <td><?= htmlspecialchars($r['passageiro']) ?></td>
            <td><?= date('d/m/Y H:i', strtotime($r['data_reserva'])) ?></td>
            <td><?= htmlspecialchars($r['lugar']) ?></td>
            <td>€<?= number_format($r['preco_total'],2,',','.') ?></td>
            <td>
              <button 
                class="btn-action btn-primary btn-sm"
                data-bs-toggle="modal" data-bs-target="#editarModal"
                data-id="<?=$r['id_reserva']?>"
                data-lugar="<?=htmlspecialchars($r['lugar'])?>"
                data-preco="<?=htmlspecialchars($r['preco_total'])?>">
                ✎
              </button>
              <form method="post" style="display:inline-block" onsubmit="return confirm('Eliminar esta reserva?')">
                <input type="hidden" name="id_reserva" value="<?=$r['id_reserva']?>">
                <button name="action" value="delete_reserva" class="btn-action btn-danger btn-sm">🗑</button>
              </form>
            </td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Modal de edição -->
  <div class="modal fade" id="editarModal" tabindex="-1">
    <div class="modal-dialog">
      <form method="post" class="modal-content">
        <input type="hidden" name="action" value="edit_reserva">
        <input type="hidden" id="edit-id" name="id_reserva">
        <div class="modal-header">
          <h5 class="modal-title">Editar Reserva</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Lugar</label>
            <input type="text" id="edit-lugar" name="lugar" class="form-control">
          </div>
          <div class="mb-3">
            <label class="form-label">Preço Total (€)</label>
            <input type="number" step="0.01" id="edit-preco" name="preco_total" class="form-control">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Guardar</button>
        </div>
      </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.getElementById('editarModal').addEventListener('show.bs.modal', e => {
      const btn = e.relatedTarget;
      document.getElementById('edit-id').value    = btn.dataset.id;
      document.getElementById('edit-lugar').value = btn.dataset.lugar;
      document.getElementById('edit-preco').value = btn.dataset.preco;
    });
  </script>
</body>
</html>
