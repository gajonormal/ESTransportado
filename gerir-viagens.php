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

// Processar criação/edição/eliminar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $stmt = $mysqli->prepare("
            INSERT INTO Viagens 
              (origem, destino, data_partida, data_chegada, preco, lotacao_maxima, tipo, estado, lotacao_atual, ativo)
            VALUES 
              (?, ?, ?, ?, ?, ?, ?, 'ativo', 0, TRUE)
        ");
        $stmt->bind_param(
            'ssssdis', 
            $_POST['origem'], 
            $_POST['destino'], 
            $_POST['data_partida'], 
            $_POST['data_chegada'], 
            $_POST['preco'], 
            $_POST['lotacao_maxima'], 
            $_POST['tipo']
        );
        $stmt->execute();
        $stmt->close();
        header('Location: gerir-viagens.php'); exit();
    } 
    elseif ($action === 'edit') {
        $stmt = $mysqli->prepare("
            UPDATE Viagens SET
              origem         = ?,
              destino        = ?,
              data_partida   = ?,
              data_chegada   = ?,
              preco          = ?,
              lotacao_maxima = ?,
              tipo           = ?
            WHERE id_viagem = ?
        ");
        $stmt->bind_param(
            'ssssdisi', 
            $_POST['origem'], 
            $_POST['destino'], 
            $_POST['data_partida'], 
            $_POST['data_chegada'], 
            $_POST['preco'], 
            $_POST['lotacao_maxima'], 
            $_POST['tipo'], 
            $_POST['id_viagem']
        );
        $stmt->execute();
        $stmt->close();
        header('Location: gerir-viagens.php'); exit();
    } 
    elseif ($action === 'delete') {
        $id = (int)$_POST['id_viagem'];
        // Apaga reservas e viagem dentro de transacção
        $mysqli->begin_transaction();
        try {
            $d1 = $mysqli->prepare("DELETE FROM Reservas WHERE id_viagem = ?");
            $d1->bind_param('i', $id);
            $d1->execute();
            $d1->close();

            $d2 = $mysqli->prepare("DELETE FROM Viagens WHERE id_viagem = ?");
            $d2->bind_param('i', $id);
            $d2->execute();
            $d2->close();

            $mysqli->commit();
        } catch (\Exception $e) {
            $mysqli->rollback();
        }
        header('Location: gerir-viagens.php'); exit();
    }
}

// Construir filtros
$where  = [];
$params = [];
$types  = '';

if (!empty($_GET['origem'])) {
    $where[]   = 'v.origem LIKE ?';
    $params[]  = '%'.$_GET['origem'].'%';
    $types    .= 's';
}
if (!empty($_GET['destino'])) {
    $where[]   = 'v.destino LIKE ?';
    $params[]  = '%'.$_GET['destino'].'%';
    $types    .= 's';
}
if (!empty($_GET['data_partida'])) {
    $where[]   = 'DATE(v.data_partida) = ?';
    $params[]  = $_GET['data_partida'];
    $types    .= 's';
}
if (!empty($_GET['tipo'])) {
    $where[]   = 'v.tipo = ?';
    $params[]  = $_GET['tipo'];
    $types    .= 's';
}

$sql = "
    SELECT 
      v.id_viagem,
      v.origem,
      v.destino,
      DATE_FORMAT(v.data_partida,'%Y-%m-%dT%H:%i') AS data_partida,
      DATE_FORMAT(v.data_chegada,'%Y-%m-%dT%H:%i') AS data_chegada,
      v.preco,
      v.lotacao_atual,
      v.lotacao_maxima,
      v.tipo,
      v.estado
    FROM Viagens v
    ".(count($where) ? 'WHERE '.implode(' AND ',$where) : '')."
    ORDER BY v.data_partida
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
  <title>ESTransportado - Gerir Viagens</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://unpkg.com/boxicons@latest/css/boxicons.min.css" rel="stylesheet">
  <link href="style.css" rel="stylesheet">
  <style>
    .main-container { max-width:95%; width:1400px; margin:40px auto; padding:0 20px; }
    .tab-container  { background:#1b1b1b; border-radius:20px; padding:30px; }
    h1              { text-align:center; margin-bottom:30px; font-weight:bold; color:#fff; }
    .form-section   { display:flex; gap:15px; flex-wrap:wrap; align-items:flex-end; margin-bottom:30px; }
    .form-group label { color:#c2ff22; font-weight:600; }
    .form-group input, .form-group select { background:#333; color:#fff; border:1px solid #555; border-radius:10px; padding:8px; }
    .btn-registo    { background:#333; color:#fff; padding:10px 15px; border-radius:10px; text-decoration:none; }
    .btn-action     { background:#333; border:none; border-radius:10px; padding:12px; color:#fff; font-size:22px; cursor:pointer; transition:background .3s; }
    .btn-action:hover { background:#444; }
    .card-viagem    { background:#222; border-radius:15px; padding:25px 30px; margin-bottom:25px; color:#fff; }
    .card-viagem h5 { color:#c2ff22; margin-bottom:15px; }
    .badge-estado   { font-size:.8rem; }
    header { background:#c2ff22; padding:15px 40px; display:flex; align-items:center; justify-content:space-between; }
    .navbar { list-style:none; display:flex; gap:30px; margin:0; padding:0; }
    .navbar li a { display:inline-block; padding:5px 10px; color:#000; font-weight:600; border-radius:5px; transition:background .2s,color .2s; }
    .navbar li a:hover { background:#000; color:#c2ff22; }
    #btn-entrar { background:#000; color:#c2ff22 !important; padding:8px 20px; border-radius:20px; }
  </style>
</head>
<body style="background:#121212;">


  <header>
    <a href="pagina-gestor.php" class="logo">
      <img src="imagens/logo.png" 
      
      alt="ESTransportado">
    </a>

    <ul class="navbar">
      <li><a href="gerir-viagens.php"> Gerir  Viagens </a></li>
      <li><a href="pagina-gerir-Utilizadores.php">Gerir Utilizadores</a></li>
     
      
    

      
      
      </div>


    <a href="perfil.php" class="btn btn-primary" id="btn-entrar">Perfil</a>
  </header>

  <div class="main-container">
    <h1>Gerir Viagens</h1>
    <div class="tab-container">
      <form method="get" class="form-section">
        <div class="form-group"><label>Origem</label><input type="text" name="origem" value="<?=htmlspecialchars($_GET['origem']??'')?>"></div>
        <div class="form-group"><label>Destino</label><input type="text" name="destino" value="<?=htmlspecialchars($_GET['destino']??'')?>"></div>
        <div class="form-group"><label>Data</label><input type="date" name="data_partida" value="<?=htmlspecialchars($_GET['data_partida']??'')?>"></div>
        <div class="form-group"><label>Tipo</label>
          <select name="tipo">
            <option value="">Todos</option>
            <option value="publico" <?=($_GET['tipo']==='publico')?'selected':''?>>Público</option>
            <option value="privado" <?=($_GET['tipo']==='privado')?'selected':''?>>Privado</option>
          </select>
        </div>
        <button class="btn-registo" type="submit">Filtrar</button>
        <button class="btn-registo" type="button" onclick="location.href='criar-viagem.php'">Criar Viagem</button>
      </form>

      <?php if ($result->num_rows === 0): ?>
        <div class="no-registros">Nenhuma viagem encontrada.</div>
      <?php else: ?>
        <?php while ($v = $result->fetch_assoc()): ?>
          <div class="card-viagem">
            <h5>
              <?=htmlspecialchars($v['origem'])?> → <?=htmlspecialchars($v['destino'])?>
              <span class="badge badge-<?= $v['estado']=='completo'?'success':($v['estado']=='cancelado'?'danger':'primary') ?> badge-estado">
                <?=ucfirst($v['estado'])?>
              </span>
            </h5>
            <p><i class='bx bx-calendar'></i>
              <?=date('d/m/Y H:i',strtotime($v['data_partida']))?> → <?=date('d/m/Y H:i',strtotime($v['data_chegada']))?>
            </p>
            <p><i class='bx bx-money'></i> €<?=number_format($v['preco'],2,',','.')?> |
               <i class='bx bx-group'></i> <?=$v['lotacao_atual']?>/<?=$v['lotacao_maxima']?> |
               <i class='bx bx-car'></i> <?=ucfirst($v['tipo'])?>
            </p>
            <div class="d-flex gap-2">
              <button class="btn-action btn-warning flex-fill"
                      data-bs-toggle="modal" data-bs-target="#editarModal"
                      data-id="<?=$v['id_viagem']?>" data-origem="<?=htmlspecialchars($v['origem'])?>"
                      data-destino="<?=htmlspecialchars($v['destino'])?>" data-partida="<?=$v['data_partida']?>"
                      data-chegada="<?=$v['data_chegada']?>" data-preco="<?=$v['preco']?>"
                      data-lotacao="<?=$v['lotacao_maxima']?>" data-tipo="<?=$v['tipo']?>">
                <i class='bx bx-edit'></i>
              </button>
              <form method="post" class="m-0 flex-fill">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id_viagem" value="<?=$v['id_viagem']?>">
                <button class="btn-action btn-danger w-100" onclick="return confirm('Eliminar esta viagem e todas as reservas?');">
                  <i class='bx bx-trash'></i>
                </button>
              </form>
            </div>
          </div>
        <?php endwhile; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- Modal Editar Viagem -->
  <div class="modal fade" id="editarModal" tabindex="-1">
    <div class="modal-dialog">
      <form method="post" class="modal-content">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" id="edit-id" name="id_viagem">
        <div class="modal-header">
          <h5 class="modal-title">Editar Viagem</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3"><label class="form-label">Origem</label><input type="text" id="edit-origem" name="origem" class="form-control"></div>
          <div class="mb-3"><label class="form-label">Destino</label><input type="text" id="edit-destino" name="destino" class="form-control"></div>
          <div class="mb-3"><label class="form-label">Partida</label><input type="datetime-local" id="edit-partida" name="data_partida" class="form-control"></div>
          <div class="mb-3"><label class="form-label">Chegada</label><input type="datetime-local" id="edit-chegada" name="data_chegada" class="form-control"></div>
          <div class="mb-3"><label class="form-label">Preço (€)</label><input type="number" step="0.01" id="edit-preco" name="preco" class="form-control"></div>
          <div class="mb-3"><label class="form-label">Lotação Máx.</label><input type="number" id="edit-lotacao" name="lotacao_maxima" class="form-control"></div>
          <div class="mb-3"><label class="form-label">Tipo</label><select id="edit-tipo" name="tipo" class="form-select"><option value="publico">Público</option><option value="privado">Privado</option></select></div>
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
      ['id','origem','destino','partida','chegada','preco','lotacao','tipo']
        .forEach(f => document.getElementById('edit-'+f).value = btn.dataset[f]);
    });
  </script>
</body>
</html>
