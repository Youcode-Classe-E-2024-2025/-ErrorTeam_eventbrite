<?php
namespace App\Controllers\Front;

use App\Core\Controller; // Assurez-vous que cette ligne est présente
// ... autres use statements ...

namespace App\Core;

use App\Core\Session;
use App\Core\Security;

class Controller {

    public function __construct() {
        Session::start();
    }

    protected function validateCsrfToken($token) : bool {
        return Security::validateCsrfToken($token);
    }
}