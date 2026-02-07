## Andressa Pet - Guia de Geração do APK Android

### Opção 1: Usar PWA Builder Online (Mais Fácil)

1. Acesse: **https://www.pwabuilder.com**

2.URL do site: `http://localhost/` ou seu IP local

3. Clique em **Build**

4. Baixe o APK gerado

### Opção 2: Usar Android Studio

1. **Instale Android Studio** (https://developer.android.com/studio)

2. **Importe o projeto**: File → Open → `/var/www/andressapet/android`

3. **Conecte seu celular** via USB (ativa depuração USB)

4. **Build**: Run → Build Bundle(s) / APK(s) → Build APK(s)

5. O APK estará em: `app/build/outputs/apk/debug/app-debug.apk`

### Opção 3: Gerar APK Online (Sem Android Studio)

1. Acesse **https://appmaker.xyz/pwa-to-apk/**

2.URL: `http://localhost/` ou IP da sua rede

3. Faça download do APK

### Para usar no celular:

O servidor PHP deve estar rodando e acessível na rede:

```bash
# No servidor:
php -S 0.0.0.0:80

# No app Android, altere o IP em MainActivity.java:
# webView.loadUrl("http://SEU_IP_LOCAL/");
```

### Estrutura do Projeto:

```
/var/www/andressapet/
├── android/
│   ├── app/
│   │   └── src/main/
│   │       ├── java/com/andressapet/app/
│   │       │   └── MainActivity.java
│   │       ├── res/
│   │       │   ├── layout/activity_main.xml
│   │       │   ├── values/strings.xml, themes.xml
│   │       │   └── drawable/, mipmap-*/
│   │       └── AndroidManifest.xml
│   ├── build.gradle
│   ├── settings.gradle
│   └── manifest.json
├── public/
│   ├── index.html
│   ├── css/style.css
│   └── js/app.js
└── database/schema.sql
```

### Banco de Dados (MySQL):

```bash
mysql -u root -p < database/schema.sql
```

### Para rodar localmente:

```bash
# Servidor PHP
cd /var/www/andressapet/public
php -S 0.0.0.0:80
```

### IP da rede para acessar do celular:

```bash
hostname -I
```

Use esse IP no app Android em vez de `localhost`.
