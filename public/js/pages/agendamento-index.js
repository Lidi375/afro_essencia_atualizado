var reagendarAgendamentoId = null;
var fpReagendar = null;

function abrirReagendar(id, profissional, servico) {
  reagendarAgendamentoId = id;

  document.getElementById("reagendar-id").value = id;
  document.getElementById("modal-info").textContent =
    profissional + " — " + servico;

  document.getElementById("reagendar-hora").innerHTML =
    '<option value="">Selecione a data primeiro</option>';

  if (fpReagendar) fpReagendar.destroy();

  fpReagendar = flatpickr("#reagendar-data", {
    locale: "pt",
    dateFormat: "Y-m-d",
    altInput: true,
    altFormat: "d/m/Y",
    minDate: "today",
    disable: [
      function (date) {
        return date.getDay() === 0;
      },
    ],

    onChange: function (selectedDates, dateStr) {
      if (!dateStr) return;
      carregarHorariosReagendar(id, dateStr);
    },
  });

  var modal = document.getElementById("modal-reagendar");
  if (modal) modal.style.display = "flex";
}

/* =========================
   BOTÃO ÍCONE DATA
========================= */

var dateIconReagendar = document.querySelector("#modal-reagendar .date-icon");

if (dateIconReagendar) {
  dateIconReagendar.style.cursor = "pointer";

  dateIconReagendar.addEventListener("click", function () {
    if (fpReagendar) {
      fpReagendar.open();
    } else {
      var input = document.getElementById("reagendar-data");
      if (input) input.focus();
    }
  });
}

/* =========================
   FECHAR MODAL
========================= */

function fecharReagendar() {
  var modal = document.getElementById("modal-reagendar");

  if (modal) modal.style.display = "none";

  document.getElementById("reagendar-data").value = "";

  document.getElementById("reagendar-hora").innerHTML =
    '<option value="">Selecione a data primeiro</option>';

  if (fpReagendar) {
    fpReagendar.clear();
  }
}

/* =========================
   CARREGAR HORÁRIOS
========================= */

function carregarHorariosReagendar(agendamentoId, data) {
  var select = document.getElementById("reagendar-hora");

  if (!select) return;

  select.innerHTML = '<option value="">Carregando...</option>';

  fetch(
    "/afro_essencia/index.php?url=agendamento/horariosReagendar&agendamento_id=" +
      agendamentoId +
      "&data=" +
      data
  )
    .then(function (response) {

      // ✅ verifica se deu erro HTTP
      if (!response.ok) {
        throw new Error("Erro na requisição: " + response.status);
      }

      // ✅ tenta converter pra JSON
      return response.json();
    })

    .then(function (horarios) {

      select.innerHTML = '<option value="">Selecione o horário</option>';

      // ✅ valida retorno
      if (!Array.isArray(horarios) || horarios.length === 0) {
        select.innerHTML =
          '<option value="">Nenhum horário disponível</option>';
        return;
      }

      horarios.forEach(function (h) {
        var opt = document.createElement("option");
        opt.value = h;
        opt.textContent = h;
        select.appendChild(opt);
      });
    })

    .catch(function (erro) {

      // ✅ mostra erro real no console
      console.error("Erro ao carregar horários:", erro);

      select.innerHTML =
        '<option value="">Erro ao carregar horários</option>';
    });
}

/* =========================
   FECHAR MODAL AO CLICAR FORA
========================= */

var modalReagendar = document.getElementById("modal-reagendar");

if (modalReagendar) {
  modalReagendar.addEventListener("click", function (e) {
    if (e.target === this) {
      fecharReagendar();
    }
  });
}