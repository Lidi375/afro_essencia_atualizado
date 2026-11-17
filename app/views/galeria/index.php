<?php
$pageTitle  = 'Galeria';
$activePage = 'galeria';
$extraCss   = ['galeria.css'];
require_once __DIR__ . '/../dashboard/partials/_admin-header.php';
?>

<nav class="adm-breadcrumb" aria-label="breadcrumb">
    <a href="/afro_essencia/dashboard">Dashboard</a>
    <span>›</span>
    <span>Galeria</span>
</nav>

<div class="adm-page-header">
    <div>
        <h1 class="adm-page-title"><i data-lucide="image"></i> Galeria</h1>
        <p class="adm-page-subtitle">Gerencie as imagens exibidas no site</p>
    </div>
</div>

<div class="galeria-aviso">
    <div class="galeria-aviso-icon">
        <i data-lucide="construction"></i>
    </div>
    <h2>Galeria em desenvolvimento</h2>
    <p>Esta seção está sendo preparada e em breve permitirá o upload e gerenciamento de imagens do salão.</p>
    <a href="/afro_essencia/dashboard" class="btn-voltar-dashboard">
        <i data-lucide="arrow-left"></i>
        Voltar ao Dashboard
    </a>
</div>

<?php require_once __DIR__ . '/../dashboard/partials/_admin-footer.php'; ?>
