# API do PortDEV

Backend Laravel 12 para autenticação, categorias e chamados. As regras de
acesso, endpoints, configuração local e comandos de teste estão no
[README principal](../README.md).

Este servidor usa SQLite por padrão em desenvolvimento. As contas e os dados de
demonstração são criados por `php artisan migrate --seed` somente nos ambientes
`local` e `testing`.
