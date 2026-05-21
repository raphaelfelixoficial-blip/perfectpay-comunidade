<?php

declare(strict_types=1);

/** Redirecionamento legado (coloque no antigo perfectpay.agenciajob.com se necessário). */
header('Location: https://copa.agenciajob.com' . ($_SERVER['REQUEST_URI'] ?? '/'), true, 301);
exit;
