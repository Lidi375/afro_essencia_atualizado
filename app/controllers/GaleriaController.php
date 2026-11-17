<?php

class GaleriaController extends BaseController {

    // Listar imagens (slider da home)
    public function index() {
        $this->view('galeria/index');
    }
}