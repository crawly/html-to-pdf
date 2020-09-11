# HtmlToPdf
Lib para converter HTML em PDF de uma maneira segura para as necessidades dos crawlers.

## Instalação
```
composer require crawly/html-to-pdf
```

## Exemplo de uso
```
<?php

use Crawly\HtmlToPdf;

$html = file_get_contents('my.html');

$htp = new HtmlToPdf($html);

$htp->setBaseUrl('http://www.detran.mg.gov.br')
    ->safeStripTags()
    ->sanitizeHtmlHeader()
    ->sanitizeHtmlText()
    ->setOptions([
         'commandOptions' => [
             'useExec' => false,
         ],
         'javascript-delay' => '10000',
         'ignoreWarnings' => false
    ])
    ->customTransform(function ($html) {
        return str_replace('abrir-fechar-table', 'abrir-fechar-table aberto', $html);
    });

$pdfBase64 = $htp->convert();
```
