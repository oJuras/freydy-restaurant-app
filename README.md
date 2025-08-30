# Freydy Restaurant App

Sistema completo de gerenciamento para restaurantes desenvolvido em PHP com arquitetura moderna e banco de dados MySQL.

## 🚀 Características

- **Sistema de Autenticação**: Login seguro com diferentes níveis de usuário
- **Dashboard Interativo**: Visão geral em tempo real do restaurante
- **Gestão de Pedidos**: Controle completo do fluxo de pedidos
- **Gestão de Mesas**: Controle de ocupação e status das mesas
- **Cardápio Digital**: Gerenciamento de produtos e categorias
- **Relatórios**: Estatísticas e relatórios de vendas
- **Interface Responsiva**: Design moderno e adaptável a dispositivos móveis

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
├── api/                    # APIs REST
│   └── pedidos/
├── assets/                 # Recursos estáticos
│   ├── css/
│   └── js/
├── config/                 # Configurações
│   └── database.php
├── database/               # Esquemas de banco
│   └── schema.sql
├── includes/               # Includes do sistema
│   ├── auth.php
│   ├── header.php
│   └── sidebar.php
├── models/                 # Modelos de dados
│   ├── Usuario.php
│   ├── Pedido.php
│   ├── Produto.php
│   └── Mesa.php
├── index.php              # Redirecionamento
├── login.php              # Página de login
├── dashboard.php          # Dashboard principal
└── logout.php             # Logout
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
- **configuracoes**: Configurações do sistema

## 👥 Tipos de Usuário

- **Admin**: Acesso total ao sistema
- **Gerente**: Gestão de usuários, relatórios e configurações
- **Garçom**: Criação e gestão de pedidos
- **Cozinheiro**: Visualização e atualização de pedidos

## 🔧 Funcionalidades

### Dashboard
- Estatísticas em tempo real
- Pedidos pendentes, em preparo e prontos
- Produtos mais vendidos
- Status das mesas

### Gestão de Pedidos
- Criação de novos pedidos
- Atualização de status
- Histórico completo
- Impressão de comandas

### Gestão de Mesas
- Controle de ocupação
- Status em tempo real
- Capacidade das mesas

### Cardápio
- Categorias de produtos
- Gestão de preços
- Tempo de preparo
- Status ativo/inativo

## 🎨 Interface

- Design moderno e responsivo
- Cores profissionais
- Ícones Font Awesome
- Animações suaves
- Compatível com dispositivos móveis

## 🔒 Segurança

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

## 🚀 Melhorias Futuras

- [ ] Sistema de notificações push
- [ ] Integração com delivery
- [ ] App mobile
- [ ] Sistema de reservas
- [ ] Integração com pagamentos
- [ ] Relatórios avançados
- [ ] Backup automático

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