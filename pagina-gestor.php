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
    <a href="#.html" class="logo">
      <img src="imagens/logo.png" alt="ESTransportado">
    </a>

    <ul class="navbar">
      <li><a href="gerir-avaliacoes.html"> Gerir  Avaliações </a></li>
      <li><a href="pagina-gerir-Utilizadores.php">Gerir Utilizadores</a></li>
     
      
    

      
      
      </div>


    <a href="perfil-gestor.php" class="btn btn-primary" id="btn-entrar">Perfil</a>
  </header>
<style>
  .dark-bg {
  background-color: #1e1e1e;
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

.bar-graph {
  height: 150px;
}

.bar {
  width: 40px;
  background-color: #161616;
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
            <li><a href="#">As minhas reservas</a></li>
            <li><a href="#">Ajuda</a></li>
            <li><a href="#">Perfil</a></li>
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
