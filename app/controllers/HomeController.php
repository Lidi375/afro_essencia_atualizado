<?php

class HomeController extends BaseController
{
    // Página inicial (site público)
    public function index()
    {
        $galeriaDAO = new GaleriaDAO();
        $galeria    = $galeriaDAO->findAll();

        // Serviços ativos para os cards clicáveis
        $servicoDAO = new ServicoDAO();
        $servicos   = $servicoDAO->findAll();

        // Indexa por slug para facilitar o mapeamento na view
        $servicosPorSlug = [];
        foreach ($servicos as $s) {
            if (!empty($s['slug'])) {
                $servicosPorSlug[$s['slug']] = $s;
            }
            $servicosPorSlug['id_' . $s['id']] = $s;
        }

        $this->view('home/index', [
            'galeria'         => $galeria,
            'servicos'        => $servicos,
            'servicosPorSlug' => $servicosPorSlug,
        ]);
    }
}
