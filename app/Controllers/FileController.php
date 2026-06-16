<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\ProtectedFile;

class FileController extends Controller {
    public function serve(): void {
        Auth::middleware();

        // Accept both ?p= (legacy) and ?path= (W9 panel links)
        $path = (string)($_GET['p'] ?? $_GET['path'] ?? '');
        ProtectedFile::stream($path);
    }
}
