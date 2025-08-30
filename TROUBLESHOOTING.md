# Guia de Solução de Problemas - Freydy Restaurant App

## Erro de Conexão com Banco de Dados

### Problema
```
PHP Fatal error: Uncaught Exception: Erro na conexão com o banco de dados: SQLSTATE[HY000] [1045] Access denied for user 'root'@'localhost' (using password: YES)
```

### Soluções

#### 1. Diagnóstico Rápido
Execute o arquivo `test_db_connection.php` no seu navegador para diagnosticar o problema:
```
http://localhost/freydy-restaurant-app/test_db_connection.php
```

#### 2. Configuração Automática
Execute o arquivo `setup_database.php` para configurar automaticamente o banco:
```
http://localhost/freydy-restaurant-app/setup_database.php
```

#### 3. Verificar Serviços

**XAMPP:**
- Abra o painel de controle do XAMPP
- Verifique se MySQL está rodando (luz verde)
- Se não estiver, clique em "Start" ao lado de MySQL

**WAMP:**
- Verifique se o ícone do WAMP está verde
- Se estiver laranja ou vermelho, clique com botão direito e selecione "Start All Services"

**Laragon:**
- Verifique se MySQL está ativo no painel
- Se não estiver, clique em "Start All"

#### 4. Configurações de Senha

**XAMPP (sem senha):**
Edite `config/database.php`:
```php
private $password = ''; // Sem senha
```

**WAMP (sem senha):**
Edite `config/database.php`:
```php
private $password = ''; // Sem senha
```

**Laragon (sem senha):**
Edite `config/database.php`:
```php
private $password = ''; // Sem senha
```

**Senha personalizada:**
Edite `config/database.php`:
```php
private $password = 'sua_senha_aqui';
```

#### 5. Criar Banco de Dados Manualmente

Se o banco não existir, execute no phpMyAdmin ou MySQL Workbench:

```sql
CREATE DATABASE freydy_restaurant_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Depois execute o arquivo `database/schema.sql` para criar as tabelas.

#### 6. Verificar Permissões do Usuário

No MySQL, execute:
```sql
GRANT ALL PRIVILEGES ON freydy_restaurant_db.* TO 'root'@'localhost';
FLUSH PRIVILEGES;
```

#### 7. Configurações Alternativas

Use o arquivo `config/database_alternative.php` que contém configurações para diferentes ambientes:

- XAMPP: sem senha
- WAMP: sem senha  
- Laragon: sem senha
- USBW: senha 'usbw' (atual)
- Custom: sua senha personalizada

#### 8. Verificar Extensões PHP

Certifique-se de que a extensão PDO MySQL está habilitada no php.ini:
```ini
extension=pdo_mysql
```

#### 9. Testar Conexão via Linha de Comando

```bash
mysql -u root -p
```

Se pedir senha, digite a senha configurada.

#### 10. Logs de Erro

Verifique os logs do MySQL:
- XAMPP: `xampp/mysql/data/mysql_error.log`
- WAMP: `wamp/logs/mysql.log`
- Laragon: `laragon/logs/mysql.log`

### Credenciais de Teste

Após configurar o banco, você pode fazer login com:

**Administrador:**
- Email: admin@freydy.com
- Senha: password

**Garçom:**
- Email: joao@freydy.com  
- Senha: password

**Cozinheiro:**
- Email: maria@freydy.com
- Senha: password

### Próximos Passos

1. Execute `test_db_connection.php` para diagnóstico
2. Execute `setup_database.php` para configuração automática
3. Teste o login em `login.php`
4. Se ainda houver problemas, verifique os logs de erro

### Suporte

Se nenhuma das soluções funcionar, verifique:
- Versão do PHP (recomendado 7.4+)
- Versão do MySQL (recomendado 5.7+)
- Configurações do servidor web (Apache/Nginx)
- Firewall e antivírus (podem bloquear conexões)
