# Sistema de Backup Automático - Freydy Restaurant App

## 📋 Visão Geral

O Sistema de Backup Automático é uma funcionalidade completa que permite criar, gerenciar e restaurar backups do sistema de restaurante, incluindo banco de dados e arquivos importantes.

## 🚀 Funcionalidades

### ✅ Funcionalidades Implementadas

#### 🔧 **Backup Manual**
- ✅ Criação de backup completo (banco + arquivos)
- ✅ Interface visual para gerenciamento
- ✅ Verificação de integridade dos backups
- ✅ Download de backups em formato ZIP
- ✅ Exclusão de backups antigos

#### 🔄 **Backup Automático**
- ✅ Configuração de frequência (diário, semanal, mensal)
- ✅ Agendamento de horário de execução
- ✅ Limpeza automática de backups antigos
- ✅ Script de execução via cron job
- ✅ Logs detalhados de execução

#### 📊 **Gerenciamento**
- ✅ Listagem de todos os backups
- ✅ Detalhes completos de cada backup
- ✅ Estatísticas de uso de espaço
- ✅ Filtros e busca
- ✅ Interface responsiva

#### 🔒 **Segurança**
- ✅ Controle de permissões por tipo de usuário
- ✅ Validação de integridade dos backups
- ✅ Verificação de disponibilidade de espaço
- ✅ Backup de metadados

## 🗄️ Estrutura do Banco de Dados

### Tabelas Criadas

#### `backups`
```sql
CREATE TABLE backups (
    id VARCHAR(32) PRIMARY KEY,
    restaurante_id INT NOT NULL,
    usuario_id INT NOT NULL,
    tipo ENUM('completo', 'banco', 'arquivos') NOT NULL,
    caminho VARCHAR(500) NOT NULL,
    metadados JSON,
    tamanho BIGINT DEFAULT 0,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurante_id) REFERENCES restaurantes(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);
```

#### `restauracoes_backup`
```sql
CREATE TABLE restauracoes_backup (
    id INT AUTO_INCREMENT PRIMARY KEY,
    backup_id VARCHAR(32) NOT NULL,
    restaurante_id INT NOT NULL,
    usuario_id INT NOT NULL,
    data_restauracao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (backup_id) REFERENCES backups(id),
    FOREIGN KEY (restaurante_id) REFERENCES restaurantes(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);
```

#### `configuracoes_backup`
```sql
CREATE TABLE configuracoes_backup (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurante_id INT NOT NULL,
    frequencia ENUM('diario', 'semanal', 'mensal') DEFAULT 'diario',
    hora_execucao TIME DEFAULT '02:00:00',
    manter_backups INT DEFAULT 10,
    ativo BOOLEAN DEFAULT TRUE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurante_id) REFERENCES restaurantes(id),
    UNIQUE KEY unique_config_restaurante (restaurante_id)
);
```

## 📁 Estrutura de Arquivos

### Diretórios Criados
```
backups/
├── backup_YYYY-MM-DD_HH-MM-SS_[ID]/
│   ├── metadata.json
│   ├── database.sql
│   └── files/
│       ├── uploads/
│       ├── config/
│       ├── assets/
│       └── includes/
```

### Arquivos do Sistema
```
models/
└── Backup.php                    # Modelo principal do sistema

api/backups/
├── criar.php                     # Criar backup manual
├── detalhes.php                  # Buscar detalhes do backup
├── restaurar.php                 # Restaurar backup
├── excluir.php                   # Excluir backup
├── configurar.php                # Configurar backup automático
├── verificar-integridade.php     # Verificar integridade
└── download.php                  # Download de backup

scripts/
└── backup_automatico.php         # Script de execução automática

pages/
└── backups.php                   # Interface de gerenciamento
```

## 🔧 Configuração

### 1. Configuração do Cron Job

Para ativar o backup automático, configure um cron job:

```bash
# Backup diário às 2h da manhã
0 2 * * * /usr/bin/php /path/to/freydy-restaurant-app/scripts/backup_automatico.php

# Backup semanal (domingo às 2h)
0 2 * * 0 /usr/bin/php /path/to/freydy-restaurant-app/scripts/backup_automatico.php

# Backup mensal (primeiro dia do mês às 2h)
0 2 1 * * /usr/bin/php /path/to/freydy-restaurant-app/scripts/backup_automatico.php
```

### 2. Permissões de Diretório

```bash
# Criar diretório de backups
mkdir backups
chmod 755 backups

# Criar diretório de logs
mkdir logs
chmod 755 logs
```

### 3. Configuração do MySQL

Certifique-se de que o usuário MySQL tem permissões para:
- `mysqldump` (para criar backups)
- `mysql` (para restaurar backups)
- Acesso ao banco de dados

## 🎯 Como Usar

### Interface Web

1. **Acesse a página de Backups**
   - URL: `http://localhost:8000/backups.php`
   - Apenas usuários admin/gerente têm acesso

2. **Criar Backup Manual**
   - Clique em "Criar Backup Manual"
   - Confirme a operação
   - Aguarde a conclusão

3. **Configurar Backup Automático**
   - Clique em "Configurar Backup Automático"
   - Defina frequência, horário e quantidade de backups
   - Salve a configuração

4. **Gerenciar Backups**
   - Visualize todos os backups na lista
   - Use filtros para encontrar backups específicos
   - Acesse detalhes, restaure ou exclua backups

### API Endpoints

#### Criar Backup
```http
POST /api/backups/criar.php
Content-Type: application/json
```

#### Buscar Detalhes
```http
GET /api/backups/detalhes.php?id={backup_id}
```

#### Restaurar Backup
```http
POST /api/backups/restaurar.php
Content-Type: application/json

{
    "backup_id": "backup_id_here"
}
```

#### Excluir Backup
```http
POST /api/backups/excluir.php
Content-Type: application/json

{
    "backup_id": "backup_id_here"
}
```

#### Configurar Backup Automático
```http
POST /api/backups/configurar.php
Content-Type: application/json

{
    "ativo": true,
    "frequencia": "diario",
    "hora_execucao": "02:00:00",
    "manter_backups": 10
}
```

## 🔒 Controle de Permissões

### Tipos de Usuário

- **Admin**: Acesso completo (criar, restaurar, excluir, configurar)
- **Gerente**: Pode criar, excluir e configurar backups
- **Garçom/Cozinheiro**: Apenas visualização

### Operações Restritas

- **Restauração**: Apenas administradores
- **Configuração Automática**: Admin e Gerente
- **Exclusão**: Admin e Gerente
- **Criação**: Todos os usuários logados

## 📊 Monitoramento

### Logs

Os logs são salvos em:
```
logs/backup_automatico.log
```

### Exemplo de Log
```
[2024-01-15 02:00:01] Iniciando backup automático...
[2024-01-15 02:00:02] Processando backup para restaurante: Restaurante Freydy
[2024-01-15 02:00:03] Executando backup para restaurante ID: 1
[2024-01-15 02:00:45] Backup criado com sucesso. ID: 507f1f77bcf86cd799439011
[2024-01-15 02:00:46] Backup automático concluído com sucesso.
```

### Estatísticas

O sistema mantém estatísticas de:
- Total de backups
- Espaço utilizado
- Último backup realizado
- Status do backup automático

## 🛠️ Manutenção

### Limpeza de Backups Antigos

O sistema automaticamente:
- Remove backups antigos baseado na configuração
- Mantém apenas os N backups mais recentes
- Libera espaço em disco

### Verificação de Integridade

Execute periodicamente:
```bash
php scripts/verificar_integridade.php
```

### Backup de Configurações

As configurações são salvas em:
- Banco de dados (tabela `configuracoes_backup`)
- Arquivo de metadados em cada backup

## 🚨 Troubleshooting

### Problemas Comuns

1. **Erro de Permissão**
   ```
   Erro: Não foi possível criar o arquivo ZIP
   ```
   **Solução**: Verificar permissões do diretório `backups/`

2. **Erro de MySQL**
   ```
   Erro ao criar backup do banco de dados
   ```
   **Solução**: Verificar credenciais e permissões do MySQL

3. **Espaço Insuficiente**
   ```
   Erro: Espaço em disco insuficiente
   ```
   **Solução**: Limpar backups antigos ou aumentar espaço

4. **Cron Job Não Executa**
   ```
   Script não é executado automaticamente
   ```
   **Solução**: Verificar configuração do cron e permissões do script

### Comandos de Diagnóstico

```bash
# Verificar espaço em disco
df -h

# Verificar logs
tail -f logs/backup_automatico.log

# Testar script manualmente
php scripts/backup_automatico.php

# Verificar permissões
ls -la backups/
ls -la scripts/backup_automatico.php
```

## 📈 Performance

### Otimizações Implementadas

- **Compressão**: Backups são compactados em ZIP
- **Limpeza Automática**: Remove backups antigos automaticamente
- **Verificação de Integridade**: Valida arquivos antes de salvar
- **Logs Estruturados**: Facilita monitoramento e debug

### Recomendações

1. **Horário de Execução**: Configure para horários de baixo tráfego
2. **Frequência**: Ajuste baseado na criticidade dos dados
3. **Retenção**: Mantenha pelo menos 7 backups para segurança
4. **Monitoramento**: Configure alertas para falhas de backup

## 🔮 Próximas Melhorias

- [ ] Backup incremental
- [ ] Compressão avançada
- [ ] Backup para nuvem (AWS S3, Google Cloud)
- [ ] Notificações por email
- [ ] Interface de monitoramento em tempo real
- [ ] Backup de logs do sistema
- [ ] Restauração seletiva (apenas tabelas específicas)

## 📞 Suporte

Para dúvidas ou problemas:
1. Verifique os logs em `logs/backup_automatico.log`
2. Consulte esta documentação
3. Teste o script manualmente
4. Verifique permissões e configurações

---

**Sistema de Backup Automático - Freydy Restaurant App**  
*Versão 1.0 - Janeiro 2024*
