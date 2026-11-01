{{-- /llms.txt (text/plain): raw output on purpose, the content comes from config and static strings. --}}
# Clientify360

> Clientify360 é um CRM online (micro SaaS) para pequenos negócios e profissionais autônomos do Brasil. Ele reúne clientes, funil de vendas, orçamentos, ordens de serviço, agenda, estoque e financeiro em um só lugar, funciona no celular e no computador, isola os dados de cada conta e tem um plano gratuito. É desenvolvido pelo Grupo 3W Online.

## Planos

@foreach ($plans as $plan)
- {!! $plan->name !!}: {!! $plan->isFree() ? 'gratuito (R$ 0,00)' : $plan->formattedPrice().' por mês' !!}. {!! $plan->description !!}
@foreach ($plan->features as $feature)
  - {!! $feature !!}
@endforeach
@endforeach

## Principais recursos

- Cadastro de clientes (pessoa física e jurídica), tags, histórico e anexos
- Funil de vendas (Kanban) com negociações e atividades
- Orçamentos e ordens de serviço, com PDF no plano Profissional
- Agenda, lembretes e registro de atendimentos
- Produtos, estoque e compras
- Contas a pagar e a receber e fluxo de caixa
- Formulários públicos de captação de leads
- Equipe com papéis (dono, gestor e colaborador) e permissões
- Análise de clientes com IA, exportação CSV/Excel e API REST no plano Profissional
- Privacidade: LGPD, GDPR, CCPA/CPRA e POPIA; exportação e exclusão dos dados pelo próprio usuário

## Perguntas frequentes

@foreach ($faqs as $faq)
### {!! $faq['question'] !!}

{!! $faq['answer'] !!}

@endforeach
## Desenvolvedor

- Empresa: Grupo 3W Online (sites e sistemas personalizados sob demanda)
- Site: https://www.3wonline.com.br
- E-mail: contato@3wonline.com.br
- WhatsApp: (41) 99692-4829
- Desenvolvido por: HOTWYL | WILLFROMBRASIL

## Links

- Início: {!! route('home') !!}
- Planos: {!! url('/#planos') !!}
- Política de Privacidade: {!! route('privacy') !!}
- Termos de Uso: {!! route('terms.show') !!}
