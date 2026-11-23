<?php
require __DIR__ . '/includes/funcoes.php';
require __DIR__ . '/config/conexao.php';

$servicos = $pdo->query(
    'SELECT id, nome, descricao, preco, duracao_min
       FROM servicos WHERE ativo = 1 ORDER BY nome'
)->fetchAll();

$produtos = $pdo->query(
    'SELECT id, nome, descricao, preco, estoque
     FROM produtos ORDER BY nome'
)->fetchAll();

$titulo = 'Início';
require __DIR__ . '/includes/cabecalho.php';
?>
<main>
    <section class="hero">
        <span class="tag">Agendamento online</span>
        <h1>Seu pet merece o melhor cuidado</h1>
        <p>Banho, tosa, consultas e vacinas. Escolha o serviço e marque o horário em poucos cliques.</p>
        <a class="btn" href="/agendar.php">Agendar agora</a>
    </section>

    <section class="secao">
        <div class="secao-titulo">
            <h2>Nossos serviços</h2>
        </div>
        <p class="sub">Tudo o que o seu amigo precisa em um só lugar.</p>

        <?php if (!$servicos): ?>
            <div class="vazio"><span class="emoji">🐾</span>Nenhum serviço disponível no momento.</div>
        <?php else: ?>
            <div class="cards">
                <?php foreach ($servicos as $s): ?>
                    <article class="card">
                        <div class="icone"><?= icone_servico($s['nome']) ?></div>
                        <h3><?= e($s['nome']) ?></h3>
                        <p><?= e($s['descricao']) ?></p>
                        <span class="preco"><?= preco_br($s['preco']) ?></span>
                        <small><?= (int) $s['duracao_min'] ?> min</small>
                        <a class="btn menta" href="/agendar.php?servico=<?= (int) $s['id'] ?>">Agendar</a>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
    <section class="secao">
    <div class="secao-titulo">
        <h2>Nossos produtos</h2>
    </div>
    <p class="sub">Tudo para o conforto e a saúde do seu pet.</p>

    <?php if (!$produtos): ?>
        <div class="vazio">Nenhum produto disponível</div>
    <?php else: ?>
        <div class="cards">
            <?php foreach ($produtos as $p): ?>
                <article class="card">
                    <h3><?= e($p['nome']) ?></h3>
                    <p><?= e($p['descricao']) ?></p>
                    <span class="preco"><?= preco_br($p['preco']) ?></span>
                    <small><?= (int) $p['estoque'] ?> em estoque</small>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
</main>
<?php require __DIR__ . '/includes/rodape.php'; ?>

