# Andressa Pet - Gerar APK Android

## 📱 Métodos para Gerar APK

### **Método 1: PWA Builder (Mais Rápido)**

1. Acesse: **https://www.pwabuilder.com**

2. Em "URL", digite: `http://localhost/` (ou seu IP local)

3. Clique no botão **"Build"**

4. Clique em **"Download APK"**

### **Método 2: PWA to APK**

1. Acesse: **https://appmaker.xyz/pwa-to-apk/**

2. Cole a URL do seu servidor

3. Clique em **"Generate APK"**

### **Método 3: Android Studio (Compilar Localmente)**

```bash
# Instalar Android Studio
# https://developer.android.com/studio

# Clone ou copie a pasta android/ para seu PC

# No Android Studio:
# File → Open → selecione a pasta android

# Conecte seu celular via USB (ativa Depuração USB)

# Build → Build Bundle(s) / APK(s) → Build APK(s)

# APK gerado em: app/build/outputs/apk/debug/app-debug.apk
```

---

## 🔧 Configuração do IP

O app precisa saber onde está seu servidor PHP:

**No Android Studio, edite:**
```
android/src/main/java/com/andressapet/app/MainActivity.java
```

Mude a linha:
```java
webView.loadUrl("http://SEU_IP_AQUI/");
```

**Para descobrir seu IP:**
```bash
hostname -I
```

---

## 📋 Estrutura do Projeto Android

```
/var/www/andressapet/
├── android/
│   ├── app/
│   │   └── src/main/
│   │       ├── java/com/andressapet/app/
│   │       │   └── MainActivity.java
│   │       ├── res/
│   │       │   ├── layout/activity_main.xml
│   │       │   └── values/strings.xml, themes.xml
│   │       └── AndroidManifest.xml
│   ├── build.gradle
│   ├── settings.gradle
│   ├── manifest.json
│   └── README.md
└── public/  (sua aplicação PHP)
```

---

## ⚠️ IMPORTANTE: IP do Servidor

O celular precisa acessar o servidor PHP:

```bash
# No servidor (Linux):
hostname -I

# Exemplo de saída: 192.168.1.100

# No app Android (MainActivity.java):
webView.loadUrl("http://192.168.1.100/");
```

**Para acesso local (mesma rede WiFi):**
- Celular e servidor devem estar na mesma rede
- Use o IP local do servidor

---

## ✅ Checklist

- [ ] Servidor PHP rodando (`php -S 0.0.0.0:80`)
- [ ] Banco de dados MySQL criado (`schema.sql`)
- [ ] IP do servidor configurado no app
- [ ] APK gerado
- [ ] Instalado no celular

---

## 🚀 Instalação no Celular

1. Ative **"Fontes desconhecidas"** em Configurações > Segurança
2. Copie o APK para o celular
3. Toque no APK para instalar
4. Abra o app!

---

## 📞 Suporte

O app carrega o sistema Andressa Pet via WebView e funciona offline (com cache).
