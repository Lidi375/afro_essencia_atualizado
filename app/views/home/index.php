<?php
/**
 * home/index.php
 * Variáveis disponíveis:
 *   $galeria         — imagens da galeria
 *   $servicos        — array de serviços ativos
 *   $servicosPorSlug — índice slug => servico
 */

// Helper: retorna a URL de detalhes dado um slug de card da home
function svUrl(string $slug, array $servicosPorSlug): string {
    if (isset($servicosPorSlug[$slug])) {
        return '/afro_essencia/servico/show/' . htmlspecialchars($slug, ENT_QUOTES);
    }
    // Fallback: manda para login/agendamento se slug não mapeado
    return '/afro_essencia/auth/login';
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AFRO ESSÊNCIA | Beleza com identidade</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Montserrat:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Dancing+Script:wght@600;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="/afro_essencia/public/css/home.css">
  <script src="https://unpkg.com/lucide@latest" defer></script>
</head>
<body>

<!-- ═══════════════════════ NAVBAR ═══════════════════════ -->
<header class="hero-header">

  <a href="/afro_essencia/home" class="hero-brand">
    <img src="/afro_essencia/public/imagens/logo.png" alt="Logo AFRO ESSÊNCIA">
    <span class="logo-text">AFRO ESSÊNCIA</span>
  </a>

  <nav class="hero-nav" id="home-nav">
    <div class="home-sidebar-logo">
      <img src="/afro_essencia/public/imagens/logo.png" alt="AFRO ESSÊNCIA">
      <span>AFRO ESSÊNCIA</span>
    </div>
    <div class="home-sidebar-nav">
      <a href="#servicos" class="home-nav-item"><i data-lucide="sparkles"></i><span>Serviços</span></a>
      <a href="#sobre" class="home-nav-item"><i data-lucide="heart"></i><span>Sobre</span></a>
      <a href="#contato" class="home-nav-item"><i data-lucide="map-pin"></i><span>Contato</span></a>
      <a href="/afro_essencia/trabalheconosco" class="home-nav-item"><i data-lucide="briefcase"></i><span>Trabalhe Conosco</span></a>
    </div>
  </nav>

  <div class="home-nav-actions">
    <a href="/afro_essencia/auth/login" class="btn-hero">Agendar</a>
    <button class="home-hamburger" id="home-hamburger" aria-label="Abrir menu" aria-expanded="false" aria-controls="home-nav">
      <span></span><span></span><span></span>
    </button>
  </div>

</header>
<div class="home-nav-overlay" id="home-nav-overlay" aria-hidden="true"></div>


<!-- ═══════════════════════ HERO ═══════════════════════ -->
<section class="hero-banner" id="hero">

  <div class="carousel-container" id="carousel">

    <div class="carousel-slide active">
      <img src="/afro_essencia/public/imagens/coloracao-natural.jpg" alt="AFRO ESSÊNCIA — Salão" loading="eager">
    </div>

    <div class="carousel-slide">
      <img src="/afro_essencia/public/imagens/nago.jpg" alt="AFRO ESSÊNCIA — Atendimento" loading="lazy">
    </div>

    <div class="carousel-slide">
      <img src="/afro_essencia/public/imagens/banner.jpg" alt="AFRO ESSÊNCIA — Promoção" loading="lazy">
    </div>

  </div>

  <!-- Setas de navegação -->
  <button class="carousel-arrow carousel-arrow-prev" aria-label="Slide anterior">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
      <path d="M15 18l-6-6 6-6"/>
    </svg>
  </button>
  <button class="carousel-arrow carousel-arrow-next" aria-label="Próximo slide">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
      <path d="M9 18l6-6-6-6"/>
    </svg>
  </button>

  <div class="hero-content">
    <span class="hero-label">AFRO ESSÊNCIA — Belo Horizonte</span>
    <h1>Sua beleza é<br><em>nossa prioridade</em></h1>
    <p>Especialistas em cabelo afro com atendimento personalizado<br>e resultados que transformam.</p>
    <div class="hero-actions">
      <a href="/afro_essencia/auth/login" class="btn-primary">Agendar horário</a>
      <a href="#servicos" class="btn-outline">Ver serviços</a>
    </div>
  </div>

  <div class="carousel-controls">
    <div class="carousel-dot active" data-index="0"></div>
    <div class="carousel-dot" data-index="1"></div>
    <div class="carousel-dot" data-index="2"></div>
  </div>

  <div class="scroll-hint"><span>Scroll</span></div>

</section>


<!-- ═══════════════════════ DIFERENCIAIS ═══════════════════════ -->
<section class="experience-section">
  <div class="experience-grid">

    <div class="exp-item">
      <div class="exp-number">01</div>
      <h3>Especialização</h3>
      <p>Técnicas específicas para cada tipo de cabelo afro, do 3C ao 4C.</p>
    </div>

    <div class="exp-item">
      <div class="exp-number">02</div>
      <h3>Atendimento Exclusivo</h3>
      <p>Cuidado único para cada cliente, com escuta e atenção em cada detalhe.</p>
    </div>

    <div class="exp-item">
      <div class="exp-number">03</div>
      <h3>Profissionais Qualificados</h3>
      <p>Equipe especializada, atualizada nas últimas técnicas capilares.</p>
    </div>

    <div class="exp-item">
      <div class="exp-number">04</div>
      <h3>Ambiente Premium</h3>
      <p>Espaço moderno e acolhedor onde você se sente em casa.</p>
    </div>

  </div>
</section>


<!-- ═══════════════════════ SERVIÇOS ═══════════════════════ -->
<section class="services-section" id="servicos">

  <div class="section-header">
    <div>
      <span class="section-eyebrow">Nossos serviços</span>
      <h2>Especialistas em<br>beleza afro</h2>
    </div>
    <a href="/afro_essencia/auth/login" class="btn-outline">Agendar agora</a>
  </div>

  <div class="service-grid">

    <?php if (!empty($servicos)): ?>

      <?php
      // Ícones SVG por slug — fallback genérico para serviços futuros
      $icones = [
        'box-braids'          => '<path d="M12 2C8.5 2 6 4.5 6 7.5c0 1.4.5 2.7 1.4 3.7C5.5 12.4 4 14.2 4 16.3c0 3.2 2.7 5.7 6 5.7 1.5 0 2.9-.6 4-1.5.9.6 2 .9 3 .9 2.8 0 5-2.2 5-5 0-1.7-.9-3.2-2.2-4.1.5-.9.7-1.9.7-2.8C20.5 5.3 16.7 2 12 2zm0 2c3.4 0 6.5 2.2 6.5 4.8 0 .7-.2 1.4-.5 2-1-.4-2.1-.5-3-.1l-3 1.5-3-1.5C8.2 10.3 7 9.1 7 7.5 7 5.6 9.2 4 12 4zm-1.5 14c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z"/>',
        'trancas-nago'        => '<path d="M12 2C8.5 2 6 4 6 7c0 1.5.6 2.8 1.5 3.8C5.6 12 4 13.8 4 16c0 3.3 2.7 6 6 6 1.7 0 3.2-.7 4.3-1.8.8.5 1.7.8 2.7.8 2.8 0 5-2.2 5-5 0-1.8-.9-3.3-2.3-4.2.5-.8.8-1.8.8-2.8C20.5 5.7 17.5 2 12 2zm0 2c2.8 0 6.5 2.4 6.5 5 0 .8-.2 1.5-.6 2.1-1-.4-2.1-.6-3.1-.2L12 12.4l-2.8-1.5C8.2 10.5 7 9.4 7 7c0-1.7 2.3-3 5-3zm-2 14c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z"/>',
        'twist'               => '<path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm0 18a8 8 0 1 1 8-8 8 8 0 0 1-8 8zm0-14a6 6 0 1 0 6 6 6 6 0 0 0-6-6zm0 10a4 4 0 1 1 4-4 4 4 0 0 1-4 4zm0-6a2 2 0 1 0 2 2 2 2 0 0 0-2-2z"/>',
        'dreadlocks'          => '<path d="M17 8C8 10 5.9 16.17 3.82 21.34L5.71 22l1-2.3A4.49 4.49 0 0 0 8 20C19 20 22 3 22 3c-1 2-8 2-8 2 2-2 6-2 6-2C13 1 8 6 8 14c-.39-.87-.68-1.82-.85-2.8C7.05 10.09 7 9 7 9l-1.99.99A10.88 10.88 0 0 0 4 14c0 2.21.7 4.26 1.87 5.94A17.53 17.53 0 0 1 8 15c2-6 9-8 9-8z"/>',
        'hidratacao-capilar'  => '<path d="M12 2c-5.33 4.55-8 8.48-8 11.8 0 4.98 3.8 8.2 8 8.2s8-3.22 8-8.2c0-3.32-2.67-7.25-8-11.8zm0 18c-3.35 0-6-2.57-6-6.2 0-2.34 1.95-5.44 6-9.14 4.05 3.7 6 6.79 6 9.14 0 3.63-2.65 6.2-6 6.2z"/>',
        'corte-afro'          => '<path d="M9.64 7.64c.23-.5.36-1.05.36-1.64 0-2.21-1.79-4-4-4S2 3.79 2 6s1.79 4 4 4c.59 0 1.14-.13 1.64-.36L10 12l-2.36 2.36C7.14 14.13 6.59 14 6 14c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4c0-.59-.13-1.14-.36-1.64L12 14l7 7h3v-1L9.64 7.64zM6 8c-1.1 0-2-.89-2-2s.9-2 2-2 2 .89 2 2-.9 2-2 2zm0 12c-1.1 0-2-.89-2-2s.9-2 2-2 2 .89 2 2-.9 2-2 2zm6-7.5c-.28 0-.5-.22-.5-.5s.22-.5.5-.5.5.22.5.5-.22.5-.5.5zM19 3l-6 6 2 2 7-7V3h-3z"/>',
        'coloracao-natural'   => '<path d="M12 3c-4.97 0-9 4.03-9 9s4.03 9 9 9c.83 0 1.5-.67 1.5-1.5 0-.39-.15-.74-.39-1.01-.23-.26-.38-.61-.38-.99 0-.83.67-1.5 1.5-1.5H16c2.76 0 5-2.24 5-5 0-4.42-4.03-8-9-8zm-5.5 9c-.83 0-1.5-.67-1.5-1.5S5.67 9 6.5 9 8 9.67 8 10.5 7.33 12 6.5 12zm3-4C8.67 8 8 7.33 8 6.5S8.67 5 9.5 5s1.5.67 1.5 1.5S10.33 8 9.5 8zm5 0c-.83 0-1.5-.67-1.5-1.5S13.67 5 14.5 5s1.5.67 1.5 1.5S15.33 8 14.5 8zm3 4c-.83 0-1.5-.67-1.5-1.5S16.67 9 17.5 9s1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/>',
        'penteado-para-festa' => '<path d="M5 16L3 5l5.5 5L12 4l3.5 6L21 5l-2 11H5zm14 3c0 .6-.4 1-1 1H6c-.6 0-1-.4-1-1v-1h14v1z"/>',
      ];
      $iconeGenerico = '<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>';
      ?>

      <?php foreach ($servicos as $sv):
        $svSlug  = htmlspecialchars($sv['slug'] ?? '', ENT_QUOTES);
        $svNome  = htmlspecialchars($sv['nome']  ?? '', ENT_QUOTES);
        // Trunca descrição para o card (máx. 120 chars)
        $descCard = mb_strlen($sv['descricao'] ?? '') > 120
                  ? mb_substr($sv['descricao'], 0, 117) . '…'
                  : ($sv['descricao'] ?? '');
        $descCard = htmlspecialchars($descCard, ENT_QUOTES);
        $svUrl   = !empty($svSlug) ? '/afro_essencia/servico/show/' . $svSlug : '/afro_essencia/auth/login';
        $svIcon  = $icones[$sv['slug'] ?? ''] ?? $iconeGenerico;
      ?>
      <!-- id=<?= (int)$sv['id'] ?> | slug: <?= $svSlug ?> | <?= $svNome ?> -->
      <a class="service-card" href="<?= $svUrl ?>">
        <span class="service-icon">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="white" opacity="0.85">
            <?= $svIcon ?>
          </svg>
        </span>
        <h3><?= $svNome ?></h3>
        <p><?= $descCard ?></p>
      </a>
      <?php endforeach; ?>

    <?php else: ?>
      <p class="servicos-vazios">Nenhum serviço disponível no momento.</p>
    <?php endif; ?>

  </div>

</section>


<!-- ═══════════════════════ SOBRE ═══════════════════════ -->
<section class="about-section" id="sobre">
  <div class="about-inner">

    <div class="about-image-wrap">
      <img src="/afro_essencia/public/imagens/sobre-afro-essencia.jpg" alt="AFRO ESSÊNCIA — Nosso espaço">
    </div>

    <div class="about-copy">
      <span class="eyebrow">Sobre a</span>

      <h2 class="about-title-script">Afro Essência</h2>

      <div class="about-divider" aria-hidden="true">
        <span></span>
        <img src="/afro_essencia/public/imagens/logo-flor.png" alt="">
        <span></span>
      </div>

      <p>A Afro Essência nasceu do amor, do propósito e do desejo de valorizar a beleza natural em sua forma mais autêntica.</p>

      <p>Mais que um salão, somos um espaço de acolhimento, autoestima e transformação. Cada serviço é feito com técnica, cuidado e respeito à sua essência.</p>

      <p>Aqui, celebramos sua história, suas raízes e sua beleza única.</p>

      <p class="about-highlight">Afro Essência é sobre realçar o que te faz única. É sobre você.</p>

      <div class="about-stats">
        <div class="stat-item">
          <span class="stat-number">5+</span>
          <span class="stat-label">Anos</span>
        </div>
        <div class="stat-item">
          <span class="stat-number">500+</span>
          <span class="stat-label">Clientes</span>
        </div>
        <div class="stat-item">
          <span class="stat-number">8</span>
          <span class="stat-label">Serviços</span>
        </div>
      </div>
    </div>

  </div>
</section>


<!-- ═══════════════════════ CTA ═══════════════════════ -->
<section class="cta-section">
  <div class="cta-inner">
    <span class="section-eyebrow">Comece agora</span>
    <h2>Pronta para viver<br>sua melhor versão?</h2>
    <p>Agende online em menos de 2 minutos e descubra o poder<br>do cuidado especializado.</p>
    <a href="/afro_essencia/auth/login" class="btn-primary">Agendar horário</a>
  </div>
</section>


<!-- ═══════════════════════ CONTATO ═══════════════════════ -->
<section class="contact-section" id="contato">
  <div class="contact-inner">

    <div class="contact-intro">
      <span class="section-eyebrow">Contato</span>
      <h2>Venha nos<br>visitar.</h2>
      <p>Estamos localizados em Belo Horizonte e prontas para recebê-la em um espaço feito com cuidado e dedicação.</p>
    </div>

    <div class="contact-cards">

      <div class="contact-card">
        <span class="contact-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="white" opacity="0.8"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg></span>
        <div class="contact-card-text">
          <span class="contact-card-label">Localização</span>
          <p>Belo Horizonte — MG</p>
        </div>
      </div>

      <div class="contact-card">
        <span class="contact-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="white" opacity="0.8"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67V7z"/></svg></span>
        <div class="contact-card-text">
          <span class="contact-card-label">Horário de funcionamento</span>
          <p>Terça a sábado · 09h às 18h</p>
        </div>
      </div>

      <div class="contact-card">
        <span class="contact-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="white" opacity="0.8"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H6l-2 2V4h16v12z"/></svg></span>
        <div class="contact-card-text">
          <span class="contact-card-label">WhatsApp</span>
          <a href="https://wa.me/5531999999999">(31) 99999-9999</a>
        </div>
      </div>

    </div>

  </div>
</section>


<!-- ═══════════════════════ FOOTER ═══════════════════════ -->
<footer class="home-footer">

  <div class="footer-brand">
    <img src="/afro_essencia/public/imagens/logo.png" alt="Logo">
    <span>AFRO ESSÊNCIA</span>
  </div>

  <p class="footer-copy">© 2026 AFRO ESSÊNCIA · Beleza com identidade e cuidado.</p>

  <nav class="footer-links">
    <a href="#servicos">Serviços</a>
    <a href="#sobre">Sobre</a>
    <a href="#contato">Contato</a>
    <a href="/afro_essencia/auth/login">Agendar</a>
  </nav>

</footer>


<script src="/afro_essencia/public/js/pages/home.js"></script>

<!-- Cards de serviço: links nativos <a href> -->



<script src="/afro_essencia/public/js/global/app.js"></script>
<script src="/afro_essencia/public/js/pages/home-nav.js" defer></script>
</body>
</html>
