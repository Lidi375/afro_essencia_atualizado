<?php

class AgendamentoService
{
    private $agendamentoDAO;

    public function __construct()
    {
        $this->agendamentoDAO = new AgendamentoDAO();
    }

    // =========================
    // CRIAR AGENDAMENTO
    // =========================
    public function create($dados)
    {
        if (empty($dados['data_agendamento'])) {
            throw new Exception("Data obrigatória");
        }

        if (empty($dados['hora_inicio'])) {
            throw new Exception("Hora obrigatória");
        }

        if (empty($dados['hora_fim'])) {
            throw new Exception("Erro no cálculo do horário");
        }

        $dataHoraSelecionada = strtotime($dados['data_agendamento'] . ' ' . $dados['hora_inicio']);
        $agora = time();

        if ($dataHoraSelecionada <= $agora) {
            throw new Exception("Não é permitido agendar horário no passado");
        }

        if (($dataHoraSelecionada - $agora) < 1800) {
            throw new Exception("Selecione um horário com pelo menos 30 minutos de antecedência");
        }

        $hora = date('H:i', $dataHoraSelecionada);
        if ($hora < "09:00" || $hora >= "18:00") {
            throw new Exception("Fora do horário de atendimento");
        }

        $horaFimTimestamp = strtotime($dados['data_agendamento'] . ' ' . $dados['hora_fim']);
        if ($horaFimTimestamp <= $dataHoraSelecionada) {
            throw new Exception("Erro no intervalo de tempo");
        }

        if ($this->agendamentoDAO->existeConflito(
            $dados['profissional_id'],
            $dados['data_agendamento'],
            $dados['hora_inicio'],
            $dados['hora_fim']
        )) {
            throw new Exception("Este horário já está ocupado");
        }

        return $this->agendamentoDAO->create($dados);
    }


    // =========================
    // CANCELAR AGENDAMENTO
    // =========================
    public function cancelar(int $id, array $agendamento): void
    {
        if ($agendamento['status'] === 'cancelado') {
            throw new Exception("Este agendamento já está cancelado.");
        }

        if ($agendamento['status'] === 'concluido') {
            throw new Exception("Não é possível cancelar um agendamento já concluído.");
        }

        $dataHora = strtotime($agendamento['data_agendamento'] . ' ' . $agendamento['hora_inicio']);
        $agora    = time();

        // Cancelamento permitido com no mínimo 2h de antecedência
        if (($dataHora - $agora) < 7200) {
            throw new Exception("Cancelamento permitido apenas com no mínimo 2 horas de antecedência.");
        }

        $this->agendamentoDAO->cancelar($id);
    }


    // =========================
    // REAGENDAR AGENDAMENTO
    // =========================
    public function reagendar(int $id, array $agendamento, string $novaData, string $novaHoraInicio, string $novaHoraFim): void
    {
        if ($agendamento['status'] === 'cancelado') {
            throw new Exception("Não é possível reagendar um agendamento cancelado.");
        }

        if ($agendamento['status'] === 'concluido') {
            throw new Exception("Não é possível reagendar um agendamento já concluído.");
        }

        $dataHoraNova = strtotime($novaData . ' ' . $novaHoraInicio);
        $agora        = time();

        if ($dataHoraNova <= $agora) {
            throw new Exception("Não é permitido reagendar para um horário no passado.");
        }

        if (($dataHoraNova - $agora) < 1800) {
            throw new Exception("Selecione um horário com pelo menos 30 minutos de antecedência.");
        }

        $hora = date('H:i', $dataHoraNova);
        if ($hora < "09:00" || $hora >= "18:00") {
            throw new Exception("Fora do horário de atendimento (09:00 às 18:00).");
        }

        if ($this->agendamentoDAO->existeConflitoExceto(
            $agendamento['profissional_id'],
            $novaData,
            $novaHoraInicio,
            $novaHoraFim,
            $id
        )) {
            throw new Exception("Este horário já está ocupado para a profissional.");
        }

        $this->agendamentoDAO->reagendar($id, $novaData, $novaHoraInicio, $novaHoraFim);
    }


    // =========================
    // GERAR HORÁRIOS DISPONÍVEIS
    // =========================
    public function gerarHorariosDisponiveis($profissionalId, $data, $duracao)
    {
        $horarios = [];

        $inicio = strtotime("09:00");
        $fim    = strtotime("18:00");

        $ocupados = $this->agendamentoDAO->findByProfissionalData($profissionalId, $data);

        $agora = time();
        $hoje  = date('Y-m-d');

        while ($inicio < $fim) {

            $horaInicio   = date("H:i", $inicio);
            $horaFim      = date("H:i", strtotime("+$duracao minutes", $inicio));
            $dataHoraSlot = strtotime($data . ' ' . $horaInicio);

            $disponivel = true;
            $motivo     = "";

            if ($data === $hoje && $dataHoraSlot <= $agora) {
                $disponivel = false;
                $motivo     = "Horário já passou";
            }

            if (($dataHoraSlot - $agora) < 1800) {
                $disponivel = false;
                $motivo     = "Muito próximo do horário atual";
            }

            if (strtotime($horaFim) > $fim) {
                $disponivel = false;
                $motivo     = "Não cabe no horário de funcionamento";
            }

            foreach ($ocupados as $o) {
                if ($horaInicio < $o['hora_fim'] && $horaFim > $o['hora_inicio']) {
                    $disponivel = false;
                    $motivo     = "Horário ocupado";
                    break;
                }
            }

            $horarios[] = [
                'hora'      => $horaInicio,
                'disponivel' => $disponivel,
                'motivo'    => $motivo,
            ];

            $inicio = strtotime("+30 minutes", $inicio);
        }

        return $horarios;
    }
}
