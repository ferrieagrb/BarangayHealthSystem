<?php

namespace App\Http\Controllers;

class QRScannerController extends Controller
{
    public function index()
    {
        return view('bhw.qr_scanner');
    }
}