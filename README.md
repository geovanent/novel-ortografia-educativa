# Novel - Ortografia Educativa

> Software educacional gamificado para apoiar estudantes do ensino medio na aprendizagem autonoma de ortografia.

![Status](https://img.shields.io/badge/STATUS-EM%20MODERNIZACAO-blue?style=for-the-badge)
![CodeIgniter](https://img.shields.io/badge/CodeIgniter-3.1.13-EF4223?style=for-the-badge&logo=codeigniter&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MariaDB](https://img.shields.io/badge/MariaDB-10.6-003545?style=for-the-badge&logo=mariadb&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-2496ED?style=for-the-badge&logo=docker&logoColor=white)

## Sobre o Projeto

O **Novel** e um jogo educacional voltado ao ensino de ortografia em lingua portuguesa. A proposta e auxiliar estudantes do ensino medio a praticarem regras ortograficas por meio de fases, narrativas, personagens literarios, pontuacao e conquistas.

O projeto trabalha dificuldades recorrentes de escrita, como uso de **G/J**, **CH/X**, **S/Z/X**, **M/N**, **R/RR**, **E/I** e **O/U/L**, organizando a aprendizagem em modos de jogo progressivos.

## Objetivo Educacional

O Novel busca transformar o estudo de ortografia em uma experiencia mais autonoma e motivadora. Em vez de apresentar apenas conteudo teorico, o sistema coloca o estudante em ciclos curtos de leitura, decisao, resposta e feedback.

## Modos de Jogo

* **Palavra:** o estudante completa palavras com o grafema correto.
* **Texto:** o estudante aplica regras ortograficas em contexto.
* **Teste:** o estudante revisa o conteudo e acompanha sua pontuacao.
* **Historia:** cenas narrativas apresentam progresso, contexto e personagens.
* **Conquistas:** recompensas acompanham o avanco do jogador.

## Tecnologias Utilizadas

### Aplicacao

* **CodeIgniter 3.1.13:** framework PHP MVC usado pelo projeto original.
* **PHP 8.4:** runtime verificado com ajustes documentados de compatibilidade do CodeIgniter.
* **Bootstrap 3:** base visual legada ainda presente em formularios, modais e carrosseis.
* **JavaScript local:** notificações, navegação, validação e rascunhos, sem dependências de CDN.
* **Progresso por questão:** tabelas de acertos e histórico, com tempo separado de desempenho.

### Banco de Dados

* **MariaDB 10.6:** banco relacional usado no ambiente Docker.
* **MySQLi:** extensao PHP usada pelo CodeIgniter para conexao com o banco.

### Infraestrutura

* **Docker & Docker Compose:** ambiente local com Apache, PHP e MariaDB.
* **Apache + mod_rewrite:** URLs sem `index.php` e rotas amigaveis.

## Rodando com Docker

Este projeto utiliza Docker para facilitar a execucao local sem configurar PHP, Apache e MariaDB manualmente.

### Pre-requisitos

* Docker instalado e em execucao.
* Docker Compose v2.

### Passo a Passo

1. Clone o repositorio:

```bash
git clone <url-do-repositorio>
cd novel-ortografia-educativa
```

2. Suba a aplicacao:

```bash
docker compose up -d --build
```

3. Aplique as migrações (necessárias também em volumes existentes):

```bash
docker compose exec -e DB_USER=root -e DB_PASSWORD=admnovel web php index.php manutencao migrar
```

4. Acesse:

* **Aplicacao:** `http://localhost:8080`

Na primeira subida local, `database/schema.sql`, `database/content.sql` e `database/demo.sql` criam o banco, conteúdo e contas de demonstração. Produção usa somente schema e conteúdo em bancos novos; bancos existentes recebem migrações. O arquivo SQL original fica como referência do legado.

## Credenciais de Demonstracao

| Acesso | Login | Senha |
| --- | --- | --- |
| Jogador | `teste` | `123@Mudar` |
| Administrador | `teste123` | `123@Mudar` |

## Comandos Uteis

```bash
docker compose logs -f web
docker compose exec db mysql -uroot -padmnovel novel
docker compose down
```

Para atualizar um volume existente, preserve o banco e execute as migrações. O aplicativo usa `novel_app` com permissões de leitura e escrita; migrações usam uma conexão administrativa temporária. Consulte [operação e implantação](docs/CORRECOES-ANALISE.md).

## Estrutura Principal

| Caminho | Papel |
| --- | --- |
| `application/controllers` | Controllers do CodeIgniter |
| `application/models` | Models de jogador, palavras, textos, testes e administracao |
| `application/views` | Telas renderizadas em PHP |
| `application/config/routes.php` | Rotas da aplicacao |
| `assets/css/estilo.css` | Tema visual e responsividade |
| `assets/img` | Personagens, fases, historias, regras e conquistas |
| `docker-compose.yml` | Servicos web e banco |
| `Dockerfile` | Imagem PHP 7.4 + Apache |
| `novel_create_insert.sql` | Criacao e carga inicial do banco |

## Rotas Principais

| Rota | Funcao |
| --- | --- |
| `/login` | Entrada do jogador |
| `/menu` | Selecao de modo de jogo |
| `/palavra` | Menu do nivel Palavra |
| `/texto` | Menu do nivel Texto |
| `/teste` | Menu do nivel Teste |
| `/meus-pontos` | Historico e desempenho |
| `/minha-conta` | Perfil do jogador |
| `/sobre-o-novel` | Informacoes do projeto |
| `/sair` | Encerrar sessao |

## Verificacoes

Como o projeto nao possui uma suite automatizada completa, uma checagem rapida pode ser feita com:

```bash
php -l application/views/index.php
php -l application/views/menu.php
php -l application/views/footer.php
php -l application/models/ModelJogador.php
```

Tambem e recomendado testar manualmente:

* Login do jogador.
* Abertura dos niveis Palavra, Texto e Teste.
* Visualizacao da historia.
* Salvamento da conta.
* Tela de pontos.

## Observacoes Tecnicas

O CodeIgniter 3 esta em fim de vida e nao e compativel com PHP 8 sem ajustes profundos. Por isso, o ambiente Docker usa PHP 7.4.

A modernizacao visual atual foi feita de forma incremental para preservar as regras de negocio e a estrutura existente. Uma migracao completa para uma stack como React/Vite exigiria reescrever controllers, views, rotas e boa parte da integracao com sessao.

## Autores

Projeto desenvolvido por:

* Italo Miranda de Novais
* Breno Antonivaldo Lessa Andrade
* Leandro Lopes Ramos

Orientacao:

* Msc. Pablo Freire Matos
* Esp. Sinval Araujo Medeiros Junior

## Rodadas e domínio

A pontuação usa um snapshot privado das questões, associado ao jogador e válido por 24 horas. O servidor calcula duração e acertos; reenvios e conclusões concorrentes retornam o resultado já salvo. Tentativas de zero acerto também são registradas.

O critério de domínio é 60% para todos os modos. Cada missão única concede avanço uma vez. A conclusão de todo o currículo libera o nível 24 e a história final; os antigos grupos duplicados “total” não concedem duas vezes a mesma recompensa. Revisões de questões erradas ajudam a praticar, mas o domínio exige a missão completa.

“Salvar e sair” guarda respostas e permite continuar a mesma rodada. O tempo inclui leitura e o intervalo entre a abertura e a conclusão; não é usado como medida de desempenho.

## Validação e implantação

Veja [correções e evidências](docs/CORRECOES-ANALISE.md), [diagnóstico das imagens e Coolify](docs/COOLIFY-E-INTERFACE.md) e [patches do framework](docs/PHP84.md).
