<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        return session()->get('usuario_id')
            ? redirect()->to('/consulta')
            : redirect()->to('/login');
    }
}
