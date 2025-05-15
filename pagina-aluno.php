<?php
session_start();

// Verificar se o usuário está logado e é aluno
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'aluno') {
    header("Location: pagina-login.php");
    exit();
}

// Incluir conexão com o banco de dados
require_once 'basedados/basedados.h';

// Buscar todas as origens e destinos disponíveis
$origens = array();
$destinos = array();

$sql = "SELECT DISTINCT origem, destino FROM viagens WHERE ativo = 1";
$result = mysqli_query($conn, $sql);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        if (!in_array($row['origem'], $origens)) {
            $origens[] = $row['origem'];
        }
        if (!in_array($row['destino'], $destinos)) {
            $destinos[] = $row['destino'];
        }
    }
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

    <style>
    /* Estilo para os grupos de rádio */
    .radio-group {
        display: flex;
        gap: 10px;
        margin-bottom: 15px;
        background-color: #111111;
        padding: 10px;
        border-radius: 8px;
    }

    /* Esconde o input radio padrão */
    .radio-group input[type="radio"] {
        display: none;
    }

    /* Estilo das labels que funcionarão como botões */
    .radio-group label {
        padding: 8px 15px;
        background-color: #222221;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-align: center;
        flex: 1;
    }

    /* Estilo quando o radio está selecionado */
    .radio-group input[type="radio"]:checked + label {
        background-color: #c2ff22;
        color: #000;
        font-weight: bold;
    }
    
    /* Estilo para os selects */
    .box-inserir select {
        width: 100%;
        padding: 10px;
        border-radius: 5px;
        border: none;
        background-color: #222221;
        color: white;
    }
    </style>
</head>

<body>
    <header>
      <a href="#.html" class="logo">
        <img src="imagens/logo.png" alt="ESTransportado">
      </a>

      <ul class="navbar">
        <li><a href="as-minhas-reservas.html">As minhas reservas</a></li>
        <li><a href="consultar-horarios.html">Consultar horarios</a></li>
      

        <li><a href="ajuda.html">Ajuda</a></li>
        
        </div>

      

      <a href="perfil.php" class="btn btn-primary" id="btn-entrar">Perfil</a>
    </header>


  <section class="procura-section">
      <h2>Para onde deseja ir</h2>
      <form method="POST" action="pesquisar-viagens.php">
          
          <label>De</label>
          <div class="box-inserir">
              <select name="origem" required>
                  <option value="">Selecione a origem</option>
                  <?php foreach ($origens as $origem): ?>
                      <option value="<?php echo htmlspecialchars($origem); ?>"><?php echo htmlspecialchars($origem); ?></option>
                  <?php endforeach; ?>
              </select>
          </div>
          
          <label>Para</label>
          <div class="box-inserir">
              <select name="destino" required>
                  <option value="">Selecione o destino</option>
                  <?php foreach ($destinos as $destino): ?>
                      <option value="<?php echo htmlspecialchars($destino); ?>"><?php echo htmlspecialchars($destino); ?></option>
                  <?php endforeach; ?>
              </select>
          </div>
          
          <label>Tipo de viagem</label>
          <div class="radio-group">
              <input type="radio" id="tipo_publica" name="tipo_transporte" value="publica" checked>
              <label for="tipo_publica">Pública</label>
              
              <input type="radio" id="tipo_privada" name="tipo_transporte" value="privada">
              <label for="tipo_privada">Privada</label>
          </div>
          
          <label>Data</label>
          <div class="box-inserir">
              <input type="date" name="data_viagem" required>
          </div>

          <button type="submit" class="btn-registo">Pesquisar</button>
      </form>
  </section>
  
  </section>
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
                <a href="ler_mais.html" class="btn btn-primary" id="btn-lermais">Ler mais</a>
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
                <a href="ler_mais.html" class="btn btn-primary" id="btn-lermais">Ler mais</a>
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
              <a href="ler_mais.html" class="btn btn-primary" id="btn-lermais">Ler mais</a>
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
                <li><a href="minhas-reservas.php">As minhas reservas</a></li>
                <li><a href="consultar-horarios.php">Consultar horários</a></li>
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