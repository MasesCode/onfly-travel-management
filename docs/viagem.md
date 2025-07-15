# Funcionalidade de Viagem (Travel)

## O que é?
A funcionalidade de Viagem foi criada para permitir que, após a aprovação de um pedido de viagem corporativa, o usuário possa agendar a retirada e entrega do item relacionado ao pedido, informando dados semelhantes a um envio de encomenda (como nos Correios).

## Como funciona
- **Criação automática:**
  - Quando um pedido é aprovado, uma viagem é criada automaticamente, mas sem dados de endereço ou destinatário preenchidos.
  - O usuário recebe uma notificação (e-mail e sistema) informando que pode agendar a viagem.
- **Criação manual:**
  - O usuário pode criar a viagem manualmente, preenchendo os campos obrigatórios:
    - Endereço de retirada
    - Endereço de entrega
    - Nome do destinatário
    - E-mail do destinatário (validação obrigatória)
    - CPF do destinatário (validação obrigatória, 11 dígitos)
    - Peso, altura, largura, comprimento (opcionais)
- **Atualização:**
  - O usuário pode atualizar os dados da viagem a qualquer momento, desde que seja o dono do pedido.
- **Envio próprio:**
  - Se após 24h da aprovação do pedido a viagem não for agendada, o sistema marca automaticamente o envio como "envio próprio" (`is_private_send = true`) e notifica o usuário.

## Rotas disponíveis
- `POST /orders/{orderId}/travel` — Cria uma viagem para o pedido (campos obrigatórios: ver acima)
- `PUT /orders/{orderId}/travel` — Atualiza os dados da viagem

## Regras de permissão
- Apenas o usuário dono do pedido pode criar ou atualizar a viagem.
- Todos os acessos são protegidos por autenticação.

## Notificações
- O usuário recebe notificações (e-mail e sistema) quando:
  - A viagem fica disponível para agendamento
  - O envio é marcado como próprio após 24h sem agendamento

## Observações
- Essa funcionalidade foi criada para simular um fluxo real de logística e não estava prevista originalmente no escopo do desafio.
- Todos os eventos relevantes são registrados em logs de atividade para auditoria.
