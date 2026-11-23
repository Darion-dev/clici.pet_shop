<?php
require __DIR__ . '/includes/funcoes.php';
require __DIR__ . '/config/conexao.php';

exigir_login();
$u = usuario_atual();

$consulta = $pdo->prepare('SELECT id, nome, especie FROM pets WHERE usuario_id = :u ORDER BY nome');
$consulta->execute([':u' => $u['id']]);
$pets = $consulta->fetchAll();

$servicos = $pdo->query(
    'SELECT id, nome, preco, duracao_min FROM servicos WHERE ativo = 1 ORDER BY nome'
)->fetchAll();

$idsPets     = array_map('intval', array_column($pets, 'id'));
$idsServicos = array_map('intval', array_column($servicos, 'id'));

$erros    = [];
$selPet   = (int) ($_POST['pet_id'] ?? $_GET['pet'] ?? 0);
$selServ  = (int) ($_POST['servico_id'] ?? $_GET['servico'] ?? 0);
$dataHora = $_POST['data_hora'] ?? '';
$obs      = trim($_POST['observacao'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validar();

    // Só aceita pet do próprio usuário e serviço ativo.
    if (!in_array($selPet, $idsPets, true)) {
        $erros[] = 'Escolha um dos seus pets.';
    }
    if (!in_array($selServ, $idsServicos, true)) {
        $erros[] = 'Escolha um serviço válido.';
    }

    $dt = DateTime::createFromFormat('Y-m-d\TH:i', $dataHora);
    if (!$dt) {
        $erros[] = 'Informe a data e a hora.';
    } elseif ($dt <= new DateTime()) {
        $erros[] = 'Escolha uma data e hora no futuro.';
    } elseif ((int) $dt->format('H') < 8 || (int) $dt->format('H') >= 18) {
        $erros[] = 'Atendemos das 8h às 18h.';
    }

    if (!$erros) {
        try {
            $mesmoHorario = $pdo->prepare(
                "SELECT COUNT(*) FROM agendamentos
                  WHERE servico_id = :s AND data_hora = :d AND status <> 'cancelado'"
            );
            $mesmoHorario->execute([':s' => $selServ, ':d' => $dt->format('Y-m-d H:i:s')]);

            if ((int) $mesmoHorario->fetchColumn() > 0) {
                $erros[] = 'Esse horário já está ocupado para este serviço. Escolha outro.';
            } else {
                $inserir = $pdo->prepare(
                    'INSERT INTO agendamentos (pet_id, servico_id, data_hora, observacao)
                     VALUES (:pet, :servico, :data, :obs)'
                );
                $inserir->execute([
                    ':pet'     => $selPet,
                    ':servico' => $selServ,
                    ':data'    => $dt->format('Y-m-d H:i:s'),
                    ':obs'     => $obs !== '' ? $obs : null,
                ]);

                flash('Agendamento enviado! Aguarde a confirmação da loja.');
                redirecionar('/meus_agendamentos.php');
            }
        } catch (PDOException $ex) {
            error_log($ex->getMessage());
            $erros[] = 'Não foi possível agendar agora. Tente novamente.';
        }
    }
}

$titulo = 'Agendar';
require __DIR__ . '/includes/cabecalho.php';
?>
<main>
    <?php if (!$pets): ?>
        <div class="vazio">
            <span class="emoji">🐶</span>
            Para agendar, primeiro cadastre o seu pet.<br><br>
            <a class="btn" href="/pet_form.php">Cadastrar pet</a>
        </div>
    <?php else: ?>
        <form class="box centro" action="/agendar.php" method="post" novalidate>
            <h2>Agendar serviço</h2>
            <p class="sub">Atendimento das 8h às 18h.</p>

            <?php if ($erros): ?>
                <div class="msg erro" role="alert">
                    <?php foreach ($erros as $erro): ?><p><?= e($erro) ?></p><?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?= csrf_campo() ?>

            <label for="pet_id">Pet</label>
            <select id="pet_id" name="pet_id" required>
                <option value="">Selecione...</option>
                <?php foreach ($pets as $p): ?>
                    <option value="<?= (int) $p['id'] ?>" <?= $selPet === (int) $p['id'] ? 'selected' : '' ?>>
                        <?= e($p['nome']) ?> (<?= e($p['especie']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="servico_id">Serviço</label>
            <select id="servico_id" name="servico_id" required>
                <option value="">Selecione...</option>
                <?php foreach ($servicos as $s): ?>
                    <option value="<?= (int) $s['id'] ?>" <?= $selServ === (int) $s['id'] ? 'selected' : '' ?>>
                        <?= e($s['nome']) ?> - <?= e(preco_br($s['preco'])) ?> (<?= (int) $s['duracao_min'] ?> min)
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="data_hora">Data e hora</label>
            <input id="data_hora" name="data_hora" type="datetime-local"
                   min="<?= date('Y-m-d\TH:i') ?>" value="<?= e($dataHora) ?>" required>

            <label for="observacao">Observação (opcional)</label>
            <textarea id="observacao" name="observacao" rows="3" maxlength="255"><?= e($obs) ?></textarea>

            <button class="btn" type="submit">Confirmar agendamento</button>
        </form>
    <?php endif; ?>
</main>
<?php require __DIR__ . '/includes/rodape.php'; ?>
