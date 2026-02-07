# 🐾 Andressa Pet - Sistema de Clínica Veterinária

Sistema completo para gerenciamento de clínica veterinária com agendamento de consultas, cadastro de pets e prontuários médicos.

## 📱 Screenshots

```
┌─────────────────────────────┐
│  🐾 Andressa Pet            │
├─────────────────────────────┤
│  📊 Dashboard               │
│  ┌─────────┐ ┌─────────┐    │
│  │ 12 Pets │ │ 8 Donos │    │
│  └─────────┘ └─────────┘    │
│  ┌─────────┐ ┌─────────┐    │
│  │ 3 Hoje  │ │ 5 Sem   │    │
│  └─────────┘ └─────────┘    │
│                             │
│  ⚡ Ações Rápidas           │
│  [🐕 Novo] [👤 Novo] [📅]  │
└─────────────────────────────┘
```

## ✨ Funcionalidades

- **Dashboard** - Estatísticas e agenda do dia
- **Pets** - Cadastro e histórico médico
- **Donos** - Cadastro com联系方式
- **Consultas** - Agendamento com status
- **Agenda** - Visualização diária
- **Prontuários** - Registros médicos completos

## 🚀 Instalação

### Servidor Web (PHP + MySQL)

```bash
# Criar banco de dados
mysql -u root -p < database/schema.sql

# Configurar Apache
cp andressapet.conf /etc/apache2/sites-available/
a2ensite andressapet.conf
systemctl restart apache2

# Acessar
http://localhost/
```

### APK Android

O app Android está disponível em:
- `/var/www/andressapet/public/andressapet.apk` (1.7MB)
- Compile com Android Studio na pasta `android/`

```bash
# Para compilar
cd android
./gradlew assembleDebug
```

## 📁 Estrutura

```
andressapet/
├── app/
│   ├── config.php          # Configuração BD
│   ├── controllers/        # API REST
│   └── models/             # Modelos
├── database/
│   └── schema.sql          # Banco MySQL
├── public/
│   ├── index.html          # Frontend
│   ├── css/style.css       # Estilos
│   └── js/app.js           # JavaScript
└── android/                # App Android
    └── app/src/main/
        └── java/.../MainActivity.java
```

## 🔌 API Endpoints

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/api/dashboard` | Estatísticas |
| GET/POST | `/api/donos` | Donos |
| GET/POST | `/api/pets` | Pets |
| GET/POST | `/api/consultas` | Consultas |
| GET/POST | `/api/prontuarios` | Prontuários |
| GET | `/api/agenda?data=YYYY-MM-DD` | Agenda |

## 📱 App Android

O app Android usa WebView para carregar o sistema web. Para usar em rede local:

1. Descubra seu IP: `hostname -I`
2. Edite `android/src/main/java/com/andressapet/app/MainActivity.java`
3. Altere a URL para `http://SEU_IP/`

## 🛠️ Technologies

- **Backend:** PHP 8, MySQL 8
- **Frontend:** HTML5, CSS3, Vanilla JS
- **Android:** Java, WebView
- **Build:** Gradle 7.5

## 📄 License

MIT License - Feito com ❤️ para a Andressa Pet
