<?php

class HorarioController extends BaseController
{
    public function index()
    {
        $this->middleware('auth');
        // Placeholder - redireciona ao dashboard enquanto funcionalidade não implementada
        $this->redirect('dashboard');
    }
}
