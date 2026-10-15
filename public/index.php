<?php
$headTitle = "Beranda - Dapur AdeMS";
$headMeta = [
    "description" => "Tempat Ade M Saragih untuk belajar dan bermain membuat website",
    "keywords"    => "dapur adems, tempat adems, adems, belajar dan bermain, belajar membuat website",
    "author"      => "Ade M Saragih",
];

?>
<!DOCTYPE html>
<html lang="id" class="h-100" data-bs-theme="dapur">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
<? foreach($headMeta as $name => $content): ?>
        <meta name="<?=$name;?>" content="<?=$content;?>" />
<? endforeach ?>
        <title><?=$headTitle;?></title>
        <link href="/asset/adems/img/favicon.ico" rel="shortcut icon" />
        <link href="/asset/bootstrap/css/bootstrap.min.css" rel="stylesheet">
        <link href="/asset/bootstrap/font/bootstrap-icons.min.css" rel="stylesheet">
        <link href="/asset/dapur/css/style.css" rel="stylesheet">
    </head>
    <body class="d-flex flex-column h-100">
        <header>
            <nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
                <div class="container-fluid">
                    <a class="navbar-brand py-0" href="https://github.com/adems/adems-dapur">
                        <img src="/asset/dapur/img/brand-logo.png" alt="Dapur AdeMS" height="40" />
                    </a>
                    <button class="navbar-toggler collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="toggler-icon top-bar"></span>
                        <span class="toggler-icon middle-bar"></span>
                        <span class="toggler-icon bottom-bar"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarCollapse">
                        <ul class="navbar-nav ms-auto mb-2 mb-md-0">
                            <li class="nav-item">
                                <a class="nav-link active" aria-current="page" href="/"><i class="bi bi-house-heart"></i> Beranda</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
        </header>
        <main class="flex-shrink-0 mt-5 pt-4">
            <div class="container">
                <div class="jumbotron">
                    <h1 class="mb-0">Selamat datang di Dapur AdeMS</h1>
                    <h5>Tempat AdeMS untuk bermain dan belajar membuat website.</h5>
                    <p class="lead mt-4">
                        Selamat! Anda telah berhasil memasang
                        <a href="https://github.com/adems/adems-dapur" target="_blank">Dapur AdeMS</a>.
                    </p>
                </div>
            </div>
        </main>
        <footer class="footer mt-auto py-3 bg-body-tertiary">
            <div class="container">
                <span class="text-body-secondary">Hak cipta &copy;<?= date('Y') ?> <a href="https://www.adems.id/" class="fw-bold text-decoration-none">Tempat AdeMS</a> untuk bermain dan belajar.</span>
            </div>
        </footer>

        <script src="/asset/bootstrap/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
    </body>
</html>