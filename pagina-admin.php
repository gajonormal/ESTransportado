<?php
session_start();

// Verificar se o usuário está logado e é admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: pagina-login.php");
    exit();
}

// Incluir conexão com o banco de dados
require_once 'basedados/basedados.h';
?>

<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ESTransportado</title>

  <!-- BOOTSTRAP 5 CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">  
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <!-- CSS Personalizado -->
  <link rel="stylesheet" href="style.css">

  <!-- BOXICONS -->
  <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
</head>

<body>
  <header>
    <a href="pagina-admin.php" class="logo">
      <img src="imagens/logo.png" alt="ESTransportado">
    </a>

    <ul class="navbar">
      <li><a href="gerir-registos.php">Gerir registos</a></li>
      <li><a href="gerir-utilizadores.php">Gerir Utilizadores</a></li>
      <li><a href="gerir-avaliacoes.php">Gerir avaliações</a></li>
      <li><a href="logs.php">Logs detalhados</a></li>
      <li><a href="ajuda.php">Ajuda</a></li>
    </ul>

    <a href="perfil.php" class="btn btn-primary" id="btn-entrar">Perfil</a>
  </header>
<style>
  .dark-bg {
    background-color: #222221;
  }

  .dashboard-box {
    background-color: #121212;
  }

  .box-yellow {
    background-color: #c6f31c;
    color: black;
  }

  .section-title {
    background-color: #212121;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    display: inline-block;
  }

  .dot-indicator {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 12px;
    height: 12px;
    border-radius: 50%;
  }

  .chart-container {
    position: relative;
    height: 200px;
    width: 100%;
  }

  .circle-placeholder {
    width: 80px;
    height: 80px;
    background-color: #3a3a3a;
    border-radius: 50%;
  }

  .progress-custom .progress-bar {
    background-color: #96c12a;
  }
</style>

<!-- DASHBOARD ESTATÍSTICAS -->
<section class="dashboard py-5 dark-bg">
  <div class="container">
    <div class="dashboard-box p-4 rounded text-white">
      <h2 class="mb-4">Dashboard</h2>

      <div class="d-flex gap-3 mb-4 flex-wrap">
        <div class="badge bg-dark p-2"><?php echo date('F Y'); ?></div>
        <div class="badge bg-dark p-2 d-flex align-items-center">
          Denúncias pendentes <span class="badge bg-secondary ms-2">
            <?php 
              $sql = "SELECT COUNT(*) as total FROM denuncias WHERE estado = 'pendente'";
              $result = $conn->query($sql);
              $row = $result->fetch_assoc();
              echo $row['total'];
            ?>
          </span>
        </div>
      </div>

      <div class="row">
        <!-- Gráfico de viagens com Chart.js -->
        <div class="col-md-6 mb-4">
          <div class="box-yellow p-4 rounded">
            <h4 class="section-title">Viagens</h4>
            <div class="d-flex gap-3 my-3">
              <button class="btn btn-dark position-relative" id="btn-viagens-ativas">
                Viagens ativas
              </button>
              <button class="btn btn-dark position-relative" id="btn-viagens-executadas">
                Viagens executadas
              </button>
            </div>
            <?php
              // Consulta para obter viagens por status
              $sql = "SELECT 
                        SUM(CASE WHEN estado = 'ativo' THEN 1 ELSE 0 END) as ativas,
                        SUM(CASE WHEN estado = 'completo' THEN 1 ELSE 0 END) as completas
                      FROM viagens";
              $result = $conn->query($sql);
              $row = $result->fetch_assoc();
            ?>
            <!-- Canvas para o gráfico Chart.js -->
            <div class="chart-container">
              <canvas id="viagensChart"></canvas>
            </div>
            <div class="d-flex justify-content-between mt-2">
              <small>Ativas: <?php echo $row['ativas']; ?></small>
              <small>Completas: <?php echo $row['completas']; ?></small>
            </div>
          </div>
        </div>

        <!-- Estatísticas à direita -->
        <div class="col-md-6">
          <div class="box-yellow p-3 rounded mb-3">
            <h5 class="section-title">Total de utilizadores</h5>
            <div class="text-center my-3">
              <h1 class="display-4">
                <?php
                  $sql = "SELECT COUNT(*) as total FROM utilizadores";
                  $result = $conn->query($sql);
                  $row = $result->fetch_assoc();
                  echo $row['total'];
                ?>
              </h1>
            </div>
          </div>
          <div class="box-yellow p-3 rounded mb-3">
            <h5 class="section-title">Rotas mais populares</h5>
            <div class="my-3">
              <?php
                $sql = "SELECT origem, destino, COUNT(*) as total 
                        FROM viagens 
                        GROUP BY origem, destino 
                        ORDER BY total DESC 
                        LIMIT 3";
                $result = $conn->query($sql);
                
                while($row = $result->fetch_assoc()) {
                  echo '<div class="d-flex justify-content-between mb-1">
                          <span>'.$row['origem'].' - '.$row['destino'].'</span>
                          <span class="badge bg-dark">'.$row['total'].' viagens</span>
                        </div>';
                }
              ?>
            </div>
          </div>

          <div class="box-yellow p-3 rounded">
            <h5 class="section-title">Avaliação média dos condutores</h5>
            <div class="progress mt-3 progress-custom">
              <?php
                $sql = "SELECT AVG(classificacao) as media FROM avaliacoes";
                $result = $conn->query($sql);
                $row = $result->fetch_assoc();
                $media = round($row['media'] * 20, 2); // Convertendo para porcentagem (5 estrelas = 100%)
              ?>
              <div class="progress-bar" role="progressbar" style="width: <?php echo $media; ?>%;"></div>
            </div>
            <div class="text-center mt-2">
              <h4><?php echo number_format($row['media'], 1); ?> <small>/ 5.0</small></h4>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Script para o gráfico de viagens -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Obter o contexto do canvas
  const ctx = document.getElementById('viagensChart').getContext('2d');
  
  // Dados para o gráfico (vindo do PHP)
  const viagensAtivas = <?php 
    $sql = "SELECT SUM(CASE WHEN estado = 'ativo' THEN 1 ELSE 0 END) as ativas FROM viagens";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    echo $row['ativas']; 
  ?>;
  
  const viagensCompletas = <?php 
    $sql = "SELECT SUM(CASE WHEN estado = 'completo' THEN 1 ELSE 0 END) as completas FROM viagens";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    echo $row['completas']; 
  ?>;
  
  // Criar o gráfico
  const viagensChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: ['Viagens Ativas', 'Viagens Completas'],
      datasets: [{
        label: 'Número de Viagens',
        data: [viagensAtivas, viagensCompletas],
        backgroundColor: [
          '#3a86ff', // Azul para viagens ativas
          '#43aa8b'  // Verde para viagens completas
        ],
        borderColor: [
          '#2168e0',
          '#348c70'
        ],
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            color: '#000000'
          },
          grid: {
            color: 'rgba(0, 0, 0, 0.1)'
          }
        },
        x: {
          ticks: {
            color: '#000000'
          },
          grid: {
            display: false
          }
        }
      },
      plugins: {
        legend: {
          display: false
        }
      }
    }
  });
  
  // Adicionar interatividade aos botões
  document.getElementById('btn-viagens-ativas').addEventListener('click', function() {
    viagensChart.data.datasets[0].data = [viagensAtivas, 0];
    viagensChart.update();
  });
  
  document.getElementById('btn-viagens-executadas').addEventListener('click', function() {
    viagensChart.data.datasets[0].data = [0, viagensCompletas];
    viagensChart.update();
  });
  
  // Para mostrar todos os dados novamente
  document.querySelector('.box-yellow').addEventListener('click', function(e) {
    if (e.target === this) {
      viagensChart.data.datasets[0].data = [viagensAtivas, viagensCompletas];
      viagensChart.update();
    }
  });
});
</script>

<!-- Secção de Serviços -->
<section class="service_section layout_padding">
  <div class="container">
    <div class="heading_container">
      <h2>Os nossos <span>serviços</span></h2>
      <p>A ESTransportado assegura transporte seguro e acessível para estudantes, ligando-os às suas instituições com
        conforto e eficiência.</p>
    </div>
    <div class="row">
      <!-- Transporte Público -->
      <div class="col-md-6">
        <div class="box">
          <div class="img-box">
            <img src="imagens/bus.png" alt="Transporte Público">
          </div>
          <div class="detail-box">
            <h3>Transportes públicos</h3>
            <p>Texto de exemplo sobre transporte público.</p>
            <a href="ler_mais.php" class="btn btn-primary" id="btn-lermais">Ler mais</a>
          </div>
        </div>
      </div>
      <!-- Transporte Privado -->
      <div class="col-md-6">
        <div class="box">
          <div class="img-box">
            <img src="imagens/carro.png" alt="Transporte Privado">
          </div>
          <div class="detail-box">
            <h3>Transportes privados</h3>
            <p>Texto de exemplo sobre transporte privado.</p>
            <a href="ler_mais.php" class="btn btn-primary" id="btn-lermais">Ler mais</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Secção Sobre Nós -->
<section class="about_section layout_padding-bottom">
  <div class="container">
    <div class="row">
      <div class="col-md-6">
        <div class="detail-box">
          <div class="heading_container">
            <h2>
              Sobre <span>Nós</span>
            </h2>
          </div>
          <p>
            There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration
            in some form, by injected humour, or randomised words which don't look even slightly believable. If you
            are going to use a passage of Lorem Ipsum, you need to be sure there isn't anything embarrassing hidden in
            the middle of text. All
          </p>
          <a href="ler_mais.php" class="btn btn-primary" id="btn-lermais">Ler mais</a>
        </div>
      </div>
      <div class="col-md-6">
        <div class="img-box">
          <img src="imagens/carrosnaestrada.jpg" alt="">
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Contactar -->
<section class="contactos">
  <h2>Contactar</h2>
  <p>Estamos disponíveis para esclarecer as suas dúvidas e ajudá-lo da melhor forma possível.</p>
  <div class="container-contactos">
    <div class="container-preenche">
      <h3>Contacte-nos</h3>
      <form action="" class="preenche-contacto">
        <input type="text" placeholder="Nome Completo">
        <input type="email" name="" id="" placeholder="E-mail">
        <input type="text" placeholder="Assunto">
        <textarea name="" cols="30" rows="10" placeholder="Escreva a sua mensagem"></textarea>
        <a href="#" class="btn btn-primary" id="btn-lermais">Enviar</a>
      </form>
    </div>
    <div class="mapa">
      <iframe
        src="https://www.google.com/maps/embed?pb=!1m10!1m8!1m3!1d1288.4629963944399!2d-7.501756625487006!3d39.81824741633561!3m2!1i1024!2i768!4f13.1!5e0!3m2!1spt-PT!2spt!4v1742757426521!5m2!1spt-PT!2spt"
        width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"
        referrerpolicy="no-referrer-when-downgrade"></iframe>
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
      <!-- Links Rápidos -->
      <div class="col-md-4">
        <div class="rodape-links">
          <h3>Links <span>Rápidos</span></h3>
          <ul>
            <li><a href="gerir-registos.php">Gerir registos</a></li>
            <li><a href="gerir-utilizadores.php">Gerir utilizadores</a></li>
            <li><a href="gerir-avaliacoes.php">Gerir avaliações</a></li>
            <li><a href="ajuda.php">Ajuda</a></li>
          </ul>
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