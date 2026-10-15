
(() => {
  'use strict';

  const config = window.URNA_CONFIG || {};
  const candidates = config.candidates || {};
  const MAX_DIGITS = 2;

  const state = {
    digits: '',
    mode: 'input',
    error: '',
    sending: false
  };

  const els = {
    digitRow: document.querySelector('#digitRow'),
    candidateCard: document.querySelector('#candidateCard'),
    candidateLabel: document.querySelector('#candidateLabel'),
    candidateName: document.querySelector('#candidateName'),
    candidateParty: document.querySelector('#candidateParty'),
    screenSurface: document.querySelector('#screenSurface'),
    screenOverline: document.querySelector('#screenOverline'),
    screenTitle: document.querySelector('#screenTitle'),
    screenHint: document.querySelector('#screenHint'),
    screenHelper: document.querySelector('#screenHelper'),
    screenModeLabel: document.querySelector('#screenModeLabel'),
    screenStatusCode: document.querySelector('#screenStatusCode'),
    entryCounter: document.querySelector('#entryCounter'),
    liveRegion: document.querySelector('#liveRegion'),
    confirmButton: document.querySelector('#confirmButton'),
    keypad: document.querySelector('#keypad'),
    correctButton: document.querySelector('#correctButton'),
    blankButton: document.querySelector('#blankButton'),
    stepItems: [...document.querySelectorAll('.step-item')]
  };

  if (!els.keypad || !els.confirmButton) {
    return;
  }

  // Eleitores e mesários podem votar.
  // O bloqueio depende somente do estado da eleição
  // e da participação anterior do usuário.
  const locked = () =>
    state.mode === 'confirmed' ||
    state.sending ||
    config.status !== 'ABERTA' ||
    config.hasVoted;

  function announce(message) {
    if (els.liveRegion) {
      els.liveRegion.textContent = message;
    }
  }

  function addDigit(digit) {
    if (locked()) return;

    if (state.mode === 'blank') {
      state.mode = 'input';
    }

    if (state.digits.length >= MAX_DIGITS) {
      return;
    }

    state.digits += digit;

    state.mode =
      state.digits.length === MAX_DIGITS
        ? 'review'
        : 'input';

    state.error = '';

    render();
  }

  function correct() {
    if (locked()) return;

    state.digits = '';
    state.mode = 'input';
    state.error = '';

    render();
  }

  function chooseBlank() {
    if (locked()) return;

    state.digits = '';
    state.mode = 'blank';
    state.error = '';

    render();
  }

  async function confirmVote() {
    if (locked()) return;

    const selection =
      state.mode === 'blank'
        ? 'BRANCO'
        : state.digits;

    if (
      selection !== 'BRANCO' &&
      !(
        selection.length === MAX_DIGITS &&
        candidates[selection]
      )
    ) {
      return;
    }

    state.sending = true;
    state.error = '';

    render();

    try {
      const response = await fetch(config.voteUrl, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': config.csrf
        },
        body: JSON.stringify({
          numero: selection
        })
      });

      const payload = await response.json();

      if (!response.ok || !payload.ok) {
        throw new Error(
          payload.erro ||
          'Não foi possível registrar seu voto.'
        );
      }

      state.mode = 'confirmed';
      config.hasVoted = true;

      announce(
        'Voto registrado. Obrigado pela participação.'
      );

    } catch (error) {
      state.error =
        error.message ||
        'Falha de conexão. Verifique antes de tentar novamente.';

      announce(state.error);

    } finally {
      state.sending = false;
      render();
    }
  }

  function renderDigits() {
    if (state.mode === 'blank') {
      els.digitRow.innerHTML =
        '<span class="blank-display">VOTO EM BRANCO</span>';
    } else {
      els.digitRow.innerHTML = Array.from(
        { length: MAX_DIGITS },
        (_, i) => {
          const digit = state.digits[i] || '';

          return `
            <span
              class="digit-slot ${digit ? 'is-filled' : ''}"
              aria-hidden="true"
            >
              ${digit}
            </span>
          `;
        }
      ).join('');
    }

    els.digitRow.setAttribute(
      'aria-label',
      state.digits
        ? `Código digitado: ${state.digits}`
        : 'Sem dígitos'
    );
  }

  function renderCandidate() {
    const candidate = candidates[state.digits];
    const show = state.mode === 'review';

    els.candidateCard.hidden = !show;

    els.candidateCard.classList.toggle(
      'is-unknown',
      show && !candidate
    );

    if (candidate) {
      els.candidateLabel.textContent = 'CANDIDATO';
      els.candidateName.textContent = candidate.name;
      els.candidateParty.textContent = candidate.party;

    } else if (show) {
      els.candidateLabel.textContent =
        'NÚMERO NÃO RECONHECIDO';

      els.candidateName.textContent =
        'Nenhum candidato';

      els.candidateParty.textContent =
        'PRESSIONE CORRIGE';
    }
  }

  function renderCopy() {
    const candidate = candidates[state.digits];

    els.screenSurface.classList.toggle(
      'is-confirmed',
      state.mode === 'confirmed'
    );

    els.screenSurface.classList.toggle(
      'is-blank',
      state.mode === 'blank'
    );

    els.screenHelper.textContent = state.error || '';

    if (
      state.mode === 'confirmed' ||
      config.hasVoted
    ) {
      els.screenOverline.textContent =
        'REGISTRO CONCLUÍDO';

      els.screenTitle.textContent =
        'Voto registrado.';

      els.screenHint.textContent =
        'Sua participação foi registrada. Sua escolha não é exibida aqui.';

      els.screenModeLabel.textContent =
        'CONCLUÍDO';

      els.screenStatusCode.textContent =
        'FINALIZADO';

    } else if (config.status !== 'ABERTA') {
      els.screenOverline.textContent =
        'ELEIÇÃO ' + config.status;

      els.screenTitle.textContent =
        config.status === 'ENCERRADA'
          ? 'Votação encerrada'
          : 'Aguarde a abertura';

      els.screenHint.textContent =
        'O mesário controla o período de votação.';

      els.screenModeLabel.textContent =
        'AGUARDANDO';

      els.screenStatusCode.textContent =
        config.status;

    } else if (state.sending) {
      els.screenOverline.textContent =
        'GRAVAÇÃO PROTEGIDA';

      els.screenTitle.textContent =
        'Registrando voto...';

      els.screenHint.textContent =
        'Aguarde a confirmação do servidor. Não feche esta página.';

      els.screenModeLabel.textContent =
        'PROCESSANDO';

      els.screenStatusCode.textContent =
        'ENVIANDO';

    } else if (state.mode === 'blank') {
      els.screenOverline.textContent = 'REVISÃO';
      els.screenTitle.textContent = 'Voto em branco';

      els.screenHint.textContent =
        'Pressione Confirma para registrar seu voto em branco.';

      els.screenModeLabel.textContent = 'REVISÃO';
      els.screenStatusCode.textContent = 'REVISAR';

    } else if (state.mode === 'review') {
      els.screenOverline.textContent =
        candidate
          ? 'CANDIDATO LOCALIZADO'
          : 'NÚMERO INVÁLIDO';

      els.screenTitle.textContent =
        candidate
          ? 'Confira sua escolha'
          : 'Não localizado';

      els.screenHint.textContent =
        candidate
          ? 'Confirme para registrar o voto ou pressione Corrige.'
          : 'Pressione Corrige e informe um número cadastrado.';

      els.screenModeLabel.textContent = 'REVISÃO';

      els.screenStatusCode.textContent =
        candidate ? 'REVISAR' : 'ATENÇÃO';

    } else {
      els.screenOverline.textContent = 'VOTAÇÃO';

      els.screenTitle.textContent =
        state.digits
          ? 'Continue digitando'
          : 'Digite o número do candidato';

      els.screenHint.textContent =
        'Use o teclado para informar dois dígitos ou escolha Branco.';

      els.screenModeLabel.textContent = 'ENTRADA';
      els.screenStatusCode.textContent = 'AGUARDANDO';
    }
  }

  function renderControls() {
    const disabled = locked();

    els.confirmButton.disabled =
      disabled ||
      !(
        state.mode === 'blank' ||
        (
          state.mode === 'review' &&
          !!candidates[state.digits]
        )
      );

    els.correctButton.disabled = disabled;
    els.blankButton.disabled = disabled;

    els.keypad
      .querySelectorAll('.number-key')
      .forEach(button => {
        button.disabled = disabled;
      });

    els.entryCounter.textContent =
      state.mode === 'blank'
        ? 'BRANCO'
        : `${state.digits.length} / ${MAX_DIGITS}`;
  }

  function renderSteps() {
    const current =
      state.mode === 'confirmed' || config.hasVoted
        ? 3
        : (
          state.mode === 'review' ||
          state.mode === 'blank'
            ? 2
            : 1
        );

    els.stepItems.forEach(item => {
      const step = Number(item.dataset.step);

      item.classList.toggle(
        'is-active',
        step === current
      );

      item.classList.toggle(
        'is-done',
        step < current
      );
    });
  }

  function render() {
    renderDigits();
    renderCandidate();
    renderCopy();
    renderControls();
    renderSteps();
  }

  els.keypad.addEventListener('click', event => {
    const button = event.target.closest('[data-digit]');

    if (button) {
      addDigit(button.dataset.digit);
    }
  });

  els.correctButton.addEventListener(
    'click',
    correct
  );

  els.blankButton.addEventListener(
    'click',
    chooseBlank
  );

  els.confirmButton.addEventListener(
    'click',
    confirmVote
  );

  document.addEventListener('keydown', event => {
    const target = event.target;

    if (
      target?.closest(
        'input, textarea, select, button, a, [role="dialog"], [contenteditable="true"]'
      )
    ) {
      return;
    }

    if (/^\d$/.test(event.key)) {
      event.preventDefault();
      addDigit(event.key);

    } else if (event.key === 'Backspace') {
      event.preventDefault();
      correct();

    } else if (event.key === 'Enter') {
      event.preventDefault();
      confirmVote();

    } else if (event.key === 'Escape') {
      event.preventDefault();
      correct();
    }
  });

  render();
})();
