# Freydy Restaurant App

Sistema completo de gerenciamento para restaurantes desenvolvido em PHP com arquitetura moderna e banco de dados MySQL.

## 🚀 Características

- **Sistema de Autenticação**: Login seguro com diferentes níveis de usuário
- **Dashboard Interativo**: Visão geral em tempo real do restaurante
- **Gestão de Pedidos**: Controle completo do fluxo de pedidos, histórico detalhado
- **Gestão de Mesas**: Controle de ocupação e status das mesas
- **Sistema de Reservas**: Reservas inteligentes com verificação de disponibilidade
- **Cardápio Digital**: Gerenciamento de produtos, categorias e upload de imagens
- **Relatórios**: Estatísticas, gráficos e exportação CSV
- **Backup Automático**: Backup manual e automático do banco e arquivos, restauração e logs
- **Notificações**: Sistema animado de notificações para o usuário
- **Interface Responsiva**: Design moderno e adaptável a dispositivos móveis

## 📊 Status das Funcionalidades

| Funcionalidade                        | Status      | Observação                                 |
|---------------------------------------|-------------|--------------------------------------------|
| Autenticação/Login                    | ✅ Completo | Sessão, permissões, múltiplos tipos        |
| Dashboard                             | ✅ Completo | Estatísticas, cards, dados em tempo real   |
| Pedidos (CRUD + Histórico)            | ✅ Completo | Criação, edição, status, histórico         |
| Produtos (CRUD + Imagem)              | ✅ Completo | Upload de imagem, categorias, status       |
| Categorias (CRUD)                     | ✅ Completo | Edição, exclusão, contagem de produtos     |
| Mesas (CRUD)                          | ✅ Completo | Capacidade, status, ocupação, liberação    |
| Reservas (CRUD + Disponibilidade)      | ✅ Completo | Filtros, status, verificação de conflitos  |
| Usuários (CRUD + Permissões)          | ✅ Completo | Tipos de usuário, edição, exclusão         |
| Relatórios (Dashboard + Exportação)   | ✅ Completo | Gráficos, filtros, exportação CSV          |
| Configurações do Sistema              | ✅ Completo | Dados do restaurante, senha, gerais        |
| Notificações (Frontend)               | ✅ Completo | Sistema animado, tipos                     |
| Modais Reutilizáveis                  | ✅ Completo | Sistema de modais para formulários         |
| Upload de Imagens                     | ✅ Completo | Produtos, preview, validação               |
| Backup Manual                         | ✅ Completo | Criação, download, exclusão, integridade   |
| Backup Automático                     | ✅ Completo | Frequência, script cron, logs, limpeza     |
| Restauração de Backup                 | ✅ Completo | Apenas admin, restauração total            |
| Exportação de Dados (CSV)             | ✅ Completo | Pedidos, produtos, mesas, relatórios       |
| Logs de Backup                        | ✅ Completo | Log detalhado em arquivo                   |
| Verificação de Integridade            | ✅ Completo | Checagem de arquivos e estrutura           |
| Documentação do Backup                | ✅ Completo | Arquivo BACKUP_SYSTEM.md                   |
| Responsividade/UX                     | ✅ Completo | Interface adaptativa, sidebar, cards       |
| Controle de Permissões                | ✅ Completo | Acesso restrito por tipo de usuário        |
| Banco de Dados (Schema)               | ✅ Completo | Todas as tabelas, índices, relacionamentos |
| Scripts de Diagnóstico                | ✅ Completo | Testes de conexão, diagnóstico             |
| Integração com Pagamentos             | ❌ Pendente | Não implementado (próxima etapa)           |
| Backup Incremental/Cloud              | ❌ Pendente | Não implementado (melhoria futura)         |
| Notificações por Email                | ❌ Pendente | Não implementado (melhoria futura)         |

## 📋 Pré-requisitos

- PHP 7.4 ou superior
- MySQL 5.7 ou superior
- Servidor web (Apache/Nginx)
- Extensões PHP: PDO, PDO_MySQL, JSON

## 🛠️ Instalação

1. **Clone o repositório**
   ```bash
   git clone https://github.com/seu-usuario/freydy-restaurant-app.git
   cd freydy-restaurant-app
   ```

2. **Configure o banco de dados**
   - Crie um banco de dados MySQL
   - Execute o arquivo `database/schema.sql` para criar as tabelas
   - Configure as credenciais em `config/database.php`

3. **Configure o servidor web**
   - Aponte o document root para a pasta do projeto
   - Certifique-se que o mod_rewrite está habilitado (Apache)

4. **Acesse o sistema**
   - Acesse `http://localhost/freydy-restaurant-app`
   - Use as credenciais padrão:
     - **Admin**: admin@freydy.com / password
     - **Garçom**: joao@freydy.com / password
     - **Cozinheiro**: maria@freydy.com / password

## 🏗️ Estrutura do Projeto

```
freydy-restaurant-app/
├── api/                    # APIs REST (pedidos, produtos, reservas, backups...)
├── assets/                 # Recursos estáticos (css, js, imagens)
├── backups/                # Backups gerados pelo sistema
├── config/                 # Configurações
├── database/               # Esquemas de banco
├── includes/               # Includes do sistema
├── logs/                   # Logs de backup automático
├── models/                 # Modelos de dados
├── scripts/                # Scripts utilitários (backup automático)
├── uploads/                # Imagens de produtos
├── index.php               # Redirecionamento
├── login.php               # Página de login
├── dashboard.php           # Dashboard principal
├── backups.php             # Gerenciamento de backups
└── ...                     # Demais páginas e APIs
```

## 🗄️ Banco de Dados

### Tabelas Principais

- **restaurantes**: Informações dos restaurantes
- **usuarios**: Usuários do sistema com diferentes níveis
- **mesas**: Mesas do restaurante e seus status
- **categorias**: Categorias de produtos
- **produtos**: Produtos do cardápio
- **pedidos**: Pedidos dos clientes
- **itens_pedido**: Itens de cada pedido
- **historico_pedidos**: Histórico de mudanças de status
- **reservas**: Reservas de mesas
- **backups**: Backups do sistema
- **restauracoes_backup**: Histórico de restaurações
- **configuracoes_backup**: Configuração de backup automático
- **configuracoes**: Configurações do sistema

## 👥 Tipos de Usuário

- **Admin**: Acesso total ao sistema
- **Gerente**: Gestão de usuários, relatórios, backups e configurações
- **Garçom**: Criação e gestão de pedidos
- **Cozinheiro**: Visualização e atualização de pedidos

## 🔧 Funcionalidades Detalhadas

### Dashboard
- Estatísticas em tempo real
- Pedidos pendentes, em preparo e prontos
- Produtos mais vendidos
- Status das mesas

### Gestão de Pedidos
- Criação de novos pedidos
- Atualização de status
- Histórico completo (timeline)
- Impressão de comandas

### Gestão de Mesas
- Controle de ocupação
- Status em tempo real
- Capacidade das mesas

### Sistema de Reservas
- CRUD completo de reservas
- Filtros por data, status e mesa
- Verificação de disponibilidade
- Estatísticas de reservas

### Cardápio
- Categorias de produtos
- Gestão de preços
- Tempo de preparo
- Status ativo/inativo
- Upload de imagens

### Relatórios
- Gráficos dinâmicos (Chart.js)
- Filtros por período
- Exportação CSV

### Backup
- Backup manual e automático
- Restauração total
- Logs e verificação de integridade
- Download ZIP
- Configuração de frequência e retenção

### Notificações
- Sistema animado e responsivo
- Tipos: sucesso, erro, info, warning

### Segurança
- Autenticação segura com hash de senhas
- Controle de sessões
- Validação de permissões
- Proteção contra SQL Injection
- Sanitização de dados

## 📱 Responsividade

O sistema é totalmente responsivo e funciona em:
- Desktop
- Tablet
- Smartphone

## 🔮 Melhorias Futuras

- [ ] Integração com pagamentos
- [ ] Backup incremental/cloud
- [ ] Notificações por email
- [ ] Integração com delivery
- [ ] App mobile
- [ ] Relatórios avançados

## 🤝 Contribuição

1. Faça um fork do projeto
2. Crie uma branch para sua feature (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

## 📄 Licença

Este projeto está sob a licença MIT. Veja o arquivo `LICENSE` para mais detalhes.

## 📞 Suporte

Para suporte, envie um email para suporte@freydy.com ou abra uma issue no GitHub.

---

**Desenvolvido com ❤️ para restaurantes**