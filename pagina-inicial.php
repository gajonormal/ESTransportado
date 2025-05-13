<?php
session_start();
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

<body>
  <header>
    <a href="index.php" class="logo">
      <img src="imagens/logo.png" alt="ESTransportado">
    </a>

    <ul class="navbar">
      <li><a href="servicos.php">Serviços</a></li>
      <li><a href="sobrenos.php">Sobre nós</a></li>
      <li><a href="contactos.php">Contactos</a></li>
      <li><a href="ajuda.php">Ajuda</a></li>
    </ul>
    
    <?php if(isset($_SESSION['id_utilizador'])): ?>
      <!-- Mostra perfil e logout se estiver logado -->
      <div class="dropdown">
        <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
          <?php echo htmlspecialchars($_SESSION['nome_completo']); ?>
        </button>
        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
          <li><a class="dropdown-item" href="perfil.php">Perfil</a></li>
          <li><a class="dropdown-item" href="minhas_viagens.php">Minhas Viagens</a></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item" href="logout.php">Sair</a></li>
        </ul>
      </div>
    <?php else: ?>
      <!-- Mostra botão de login se não estiver logado -->
      <a href="pagina-login.php" class="btn btn-primary" id="btn-entrar">Entrar</a>
    <?php endif; ?>
  </header>

  <!-- CARROSSEL Bootstrap 5 -->
  <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel">
    <div class="carousel-indicators">
      <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0" class="active"></button>
      <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="1"></button>
      <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="2"></button>
    </div>

    <div class="carousel-inner">
      <div class="carousel-item active">
        <img src="imagens/autocarro-slider.jpg" class="d-block w-100" alt="Imagem 1">
        <div class="carousel-caption d-none d-md-block">
          <h5>Bem-vindo ao ESTransportado</h5>
          <p>O melhor serviço de transporte universitário.</p>
        </div>
      </div>
      <div class="carousel-item">
        <img src="imagens/conducaoprivada.jpg" class="d-block w-100" alt="Imagem 2">
      </div>
      <div class="carousel-item">
        <img src="imagens/autocarrointerior.jpg" class="d-block w-100" alt="Imagem 3">
      </div>
    </div>

    <!-- Botões de navegação -->
    <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators"
      data-bs-slide="prev">
      <span class="carousel-control-prev-icon" aria-hidden="true"></span>
      <span class="visually-hidden">Anterior</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators"
      data-bs-slide="next">
      <span class="carousel-control-next-icon" aria-hidden="true"></span>
      <span class="visually-hidden">Próximo</span>
    </button>
  </div>

  <!-- BOOTSTRAP 5 JS (Sem jQuery) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

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
        <form action="enviar_contato.php" method="POST" class="preenche-contacto">
          <input type="text" name="nome" placeholder="Nome Completo" required>
          <input type="email" name="email" placeholder="E-mail" required>
          <input type="text" name="assunto" placeholder="Assunto" required>
          <textarea name="mensagem" cols="30" rows="10" placeholder="Escreva a sua mensagem" required></textarea>
          <button type="submit" class="btn btn-primary" id="btn-lermais">Enviar</button>
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
              <li><a href="index.php">Início</a></li>
              <li><a href="servicos.php">Serviços</a></li>
              <li><a href="sobrenos.php">Sobre Nós</a></li>
              <li><a href="contactos.php">Contactos</a></li>
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
        <p>&copy; <?php echo date('Y'); ?> ESTransportado. Todos os direitos reservados.</p>
      </div>
    </div>
  </footer>

</body>
</html>