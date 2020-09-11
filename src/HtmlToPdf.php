<?php

namespace Crawly\HtmlToPdf;

use mikehaertl\wkhtmlto\Pdf;

class HtmlToPdf
{
    /**
     * Buffer com o conteúdo em html parcial para parsing/conversão
     * @var string
     */
    private $buffer;
    /**
     * Opções para a classe Pdf
     * @var array
     */
    private $options;

    public function __construct (string $html)
    {
        $this->buffer = $html;
        $this->options = [
            'commandOptions' => [
                'useExec' => true,
            ],
            'javascript-delay' => '20000',
            'ignoreWarnings' => true
        ];
    }

    public function getBuffer(): string
    {
        return $this->buffer;
    }

    public function setOptions (array $opts = []): self
    {
        $this->options = array_merge($this->options, $opts);

        return $this;
    }

    public function convert (bool $base64 = true): string
    {
        $pdf = new Pdf($this->buffer);
        $pdf->setOptions($this->options);

        $pdfString = $pdf->toString();

        return $base64 ? base64_encode($pdfString) : $pdfString;
    }

    public function setBaseUrl(string $baseUrl): self
    {
        $this->buffer = str_replace('<head>', '<head><base href="'.$baseUrl.'" />', $this->buffer);

        return $this;
    }

    public function sanitizeHtmlHeader(): self
    {
        $this->buffer = preg_replace('/<\?xml .*?\?>/s', "", $this->buffer);
        $this->buffer = str_replace('<!DOCTYPE html>', '', $this->buffer);

        return $this;
    }

    public function sanitizeHtmlText(bool $stripDoubleSpace = true,
                                     bool $stripDoubleBreak = true,
                                     bool $fixCharset = true): self
    {
        // Removendo espaços duplos
        if ($stripDoubleSpace) {
            $this->buffer = preg_replace('/ +/s', " ", $this->buffer);
        }

        // Removendo quebra de linha dupla
        if ($stripDoubleBreak) {
            $this->buffer = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $this->buffer);
            $this->buffer = trim($this->buffer, "\r\n ");
        }

        // Arrumando charset
        if ($fixCharset) {
            libxml_use_internal_errors(true);
            $doc = new \DOMDocument();
            $doc->loadHTML(mb_convert_encoding($this->buffer, 'HTML-ENTITIES', 'UTF-8'));
            $this->buffer = $doc->saveHTML();
        }

        return $this;
    }

    public function customTransform(callable $function): self
    {
        $this->buffer = $function($this->buffer);

        return $this;
    }

    public function safeStripTags(bool $stripMeta = true,
                                  bool $stripNoScript = true,
                                  bool $stripComments = true,
                                  bool $stripHidden = true): self
    {
        if ($stripMeta) {
            $this->buffer = preg_replace('/<meta.*?>/s', '', $this->buffer);
        }
        if ($stripNoScript) {
            $this->buffer = preg_replace('/<noscript>.*?<\/noscript>/s', '', $this->buffer);
        }
        if ($stripComments) {
            $this->buffer = preg_replace('/<!--.*?-->/s', '', $this->buffer);
        }
        if ($stripHidden) {
            $this->buffer = preg_replace("/<input.*?type=[\"']hidden.*?>/s", "", $this->buffer);
        }

        return $this;
    }
}