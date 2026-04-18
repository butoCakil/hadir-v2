<?php
namespace App\Controllers;
use App\Core\Auth;
use App\Core\Response;

class PanduanController
{
    public function index(): void
    {
        Auth::start();
        Response::view('panduan/index', ['title' => 'Panduan Penggunaan']);
    }
}