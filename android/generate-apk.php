#!/usr/bin/env php
<?php
/**
 * Script para gerar APK usando PWA Builder API
 * https://pwabuilder.com
 */

$apiKey = getenv('PWA_BUILDER_KEY') ?: '';
$url = $argv[1] ?? 'http://localhost/';

echo "Andressa Pet - APK Generator\n";
echo "===========================\n\n";

echo "URL: $url\n\n";

// Método 1: Usar PWA Builder Online
echo "Opções para gerar APK:\n\n";

echo "1. Acesse: https://www.pwabuilder.com\n";
echo "2. Cole a URL: $url\n";
echo "3. Clique em 'Build'\n";
echo "4. Baixe o APK para Android\n\n";

// Criar script HTML que facilita
$html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Andressa Pet - Gerar APK</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
        .btn { display: inline-block; padding: 15px 30px; background: #e91e63; color: white; 
               text-decoration: none; border-radius: 8px; font-size: 18px; margin: 10px; }
        .info { background: #f5f5f5; padding: 20px; border-radius: 8px; margin: 20px 0; }
    </style>
</head>
<body>
    <h1>🐕 Andressa Pet - Clínica Veterinária</h1>
    <div class="info">
        <p><strong>URL do sistema:</strong> $url</p>
        <p>Seu sistema deve estar rodando para o app funcionar.</p>
    </div>
    <h2>Gerar APK:</h2>
    <a href="https://www.pwabuilder.com" target="_blank" class="btn">PWA Builder</a>
    <a href="https://appmaker.xyz/pwa-to-apk/" target="_blank" class="btn">PWA to APK</a>
    <a href="https://www.andromo.com/website-to-app" target="_blank" class="btn">Andromo</a>
</body>
</html>
HTML

file_put_contents('/var/www/andressapet/android/apk-generator.html', $html);

echo "Página de ajuda criada: /var/www/andressapet/android/apk-generator.html\n\n";

// Gerar script batch para Windows
$batch = <<<BATCH
@echo off
echo ====================================
echo Andressa Pet - Gerar APK
echo ====================================
echo.
echo Opcao 1: Gerar APK online
echo - Acesse: https://www.pwabuilder.com
echo - Cole a URL: http://localhost/
echo - Clique em Build
echo.
echo Opcao 2: Instalar APK ja gerado
echo - Copie app-debug.apk para o celular
echo - Ative 'Fontes desconhecidas' nas configuracoes
echo - Instale o APK
echo.
echo O servidor PHP deve estar rodando!
echo.
pause
BATCH;

file_put_contents('/var/www/andressapet/android/gerar-apk.bat', $batch);

echo "Script Windows criado: /var/www/andressapet/android/gerar-apk.bat\n";
