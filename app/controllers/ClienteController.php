<?php

class ClienteController extends BaseController
{
    public function index()
    {
        $this->middleware('admin');
        $clientes = (new ClienteDAO())->findAll();
        $this->view('dashboard/admin/clientes', ['clientes' => $clientes]);
    }

    public function create()
    {
        $this->middleware('admin');
        $this->redirect('dashboard/clientes');
    }
}
