# Mesclagem dos sistemas de `bkp-after_features` no Clientify360

Branch: `feature/merge-bkp-features` · Data: 25/09/2026

## 1. O que havia em cada sistema e o que foi aproveitado

| Sistema | Stack | Aproveitado | Descartado (e por quê) |
|---|---|---|---|
| **CrmPlus** | Laravel 12 | Funil/oportunidades, etapas com probabilidade, tags, atividades, orçamentos com itens, contas a pagar/receber, categorias, formulários de captura | Prospecção SDR/cadências, qualificação BANT, ordens de serviço, integrações, fidelidade, IA própria — sobreposição com o que o Clientify360 já tem (IA, atendimentos) ou fora do escopo desta rodada |
| **sistema-vendas-master** | PHP puro | Modelo de produto (código, estoque, NCM, preço, **promoção**, custo, último fornecedor), compras com nº de nota, contas a pagar | Carrinho/catálogo público, boletos, fotos — código com SQL concatenado e sem isolamento por usuário |
| **Sistema-de-Vendas-PHP (OSPOS)** | CodeIgniter 2 / PHP 5.5 | Conceito de `inventory` (movimentação com saldo), `reorder_level` (estoque mínimo), custo × preço (margem) | PDV de balcão, gift cards, kits, funcionários/permissões — PHP 5.5, não portável |
| **crm-master** | PHP + ADOdb | Origem do cliente ("Como chegou a nós") e histórico com **próximo passo** | O restante já existe no Clientify360 |

Nada foi copiado literalmente: as regras foram **reescritas** no padrão do Clientify360
(UUID, `BelongsToUser`, SoftDeletes, Services, nomes em inglês, Blade + Alpine + Tailwind).

## 2. Decisões de arquitetura

1. **Uma tabela financeira em vez de três.** O CrmPlus tinha `contas_pagar`, `contas_receber` e `transacoes`,
   com o mesmo pagamento gravado duas vezes. Aqui `financial_entries.direction` (`receivable`/`payable`)
   separa os tipos e o fluxo de caixa é o que tem `paid_at`. Menos tabelas, zero divergência.
2. **"Atrasado" é calculado, não gravado.** Status gravado como `atrasado` exige um cron para atualizar e
   fica errado quando o cron falha. `FinancialEntry::scopeOverdue()` = pendente + vencido.
3. **Aprovar orçamento é a venda.** Em vez de um módulo PDV separado (OSPOS), a aprovação executa, numa
   única transação: baixa de estoque → contas a receber (parcelas) → oportunidade ganha → histórico.
   Se o estoque não basta, **nada** é gravado (há opção explícita para vender com saldo negativo, útil para encomenda).
4. **Parcelas somam exatamente o total.** R$ 100 em 3x = 33,34 + 33,33 + 33,33 (o centavo de arredondamento vai na 1ª).
5. **Estoque só muda por movimentação** (`StockService`), com `lockForUpdate` para vendas simultâneas e
   `balance_after` em cada linha — o histórico é auditável.
6. **"Próximo passo" reaproveita a Agenda.** Em vez de outra tabela de tarefas, vira um `Reminder`,
   que já aparece em Notificações.
7. **Numeração de orçamento única por usuário.** No CrmPlus o `numero` era `unique` global — dois clientes
   do SaaS não podiam ter `ORC-2026-0001`. Aqui o índice é `(user_id, number)`.
8. **Validação de vínculos restrita à conta.** `exists:clientes,id` (usado no CrmPlus) aceita IDs de
   outra conta. Todas as regras novas usam `App\Support\OwnedRule::exists()`.
9. **Captação pública sem usuário logado.** O serviço define `user_id` explicitamente e remove o escopo
   global só onde precisa. Deduplica por e-mail. Se o limite de clientes do plano estourar,
   **o lead não se perde**: fica salvo no envio com um aviso.
10. **Limites de plano genéricos.** `plan.limit` (sem parâmetro) continua valendo para clientes;
    `plan.limit:products`, `:quotes_per_month`, `:lead_forms` usam `config/plans.php`.
11. **Sem dependências novas.** Impressão de orçamento é HTML + `window.print()` ("Salvar como PDF")
    em vez de dompdf; gráfico de fluxo de caixa em HTML/CSS em vez de biblioteca de gráficos.

## 3. Correções em código existente (encontradas pelos testes)

- **`/clients/create` retornava 404**: a rota `Route::resource('clients')` estava antes de `clients/create`,
  então "create" era tratado como ID de cliente. Ordem corrigida em `routes/web.php`.
- **Preço errado no banner de upgrade** do dashboard (R$ 29,90 fixo; o plano custa R$ 39,90). Agora lê `config/plans.php`.
- **Menu "Financeiro"** apontava para a assinatura Stripe — renomeado para **Assinatura**; "Financeiro"
  agora é o módulo financeiro.
- **Dashboard** saiu de uma closure em `routes/web.php` para `DashboardController`.
- **7 testes do Breeze que falhavam** (não consideravam username, LGPD, aceite de termos e soft delete) foram ajustados.

## 4. Como publicar

```bash
git checkout feature/merge-bkp-features
composer install
php artisan migrate          # 4 migrations novas, todas com down() — reversível: migrate:rollback --step=4
php artisan view:clear
php artisan test             # 35 testes
```

## 5. Pendência que depende de decisão sua

**Formulário de captação dentro de iframe no site do cliente.** Hoje funciona por link direto
(`/f/slug`), que cobre bio do Instagram, WhatsApp, e-mail e botão no site. Para incorporar via
`<iframe>` seria preciso liberar `frame-ancestors` e remover a verificação CSRF só nas rotas `/f/*`
(o cookie de sessão não é enviado dentro de iframe de outro domínio). Isso reduz proteções de segurança,
então **não foi feito** — fica a seu critério.

---

# Rodada 2 — Correções e novos recursos (30/09/2026)

Continuação do desenvolvimento após a mesclagem. Foco em corrigir o que estava
quebrado e completar funcionalidades que ficaram pela metade.

## Correções

1. **Resumo financeiro do mês retornava zero** (`FinanceService::monthSummary()` e
   `cashFlow()`). As colunas `paid_at`/`due_date` têm cast `date` e são gravadas como
   `Y-m-d 00:00:00`; o `whereBetween` usava o fim do mês como `Y-m-d` (sem hora), então
   o último dia do mês caía fora do intervalo. Os limites agora usam `startOfDay()`/`endOfDay()`.
   Isso fazia o teste `finance entries can be settled and overdue is computed` falhar.

2. **Notificações "marcar como lida" eram ilusórias.** As notificações são geradas em
   tempo real (lembretes, financeiro, estoque, leads, oportunidades) e não ficavam
   persistidas, então o botão de marcar como lida não fazia nada e a notificação reaparecia.
   Agora existe a tabela `dismissed_notifications` (chave estável por usuário): dispensar
   uma notificação a esconde de verdade; "Dispensar todas" some com todas as atuais.
   Se o mesmo alerta mudar de conteúdo (a chave muda), ele reaparece — correto.

## Novos recursos

3. **Badge de notificações no menu lateral.** Mostra a contagem de alertas pendentes
   sem precisar abrir a tela. A geração foi extraída para `NotificationService` (singleton
   com cache por request), reaproveitada pela página e pelo badge — sem recomputar.

4. **Relatórios comerciais.** A tela de Relatórios só cobria clientes/atendimentos. Agora
   inclui, no período selecionado: oportunidades ganhas × perdidas, valor ganho, taxa de
   conversão, orçamentos aprovados e aguardando, resumo financeiro do mês (recebido, pago,
   saldo, a receber), produtos mais vendidos e principais motivos de perda. Lógica em
   `ReportService`, mantendo o controller fino.

## Testes

- Corrigido o teste financeiro que falhava.
- Novos: `NotificationsTest` (4 casos — geração, dispensar individual, dispensar todas,
  isolamento por usuário) e `ReportTest` (2 casos — render com métricas comerciais e
  cálculo da taxa de conversão).
- Removido o placeholder `tests/Unit/ExampleTest.php`.
- Suíte: **40 testes, 203 asserções, tudo verde** (`php artisan test`).

## Publicar

```bash
php artisan migrate      # 1 migration nova: create_dismissed_notifications_table (com down())
php artisan view:clear
php artisan test
```
