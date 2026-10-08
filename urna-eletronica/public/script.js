(() => {
  'use strict';

  const MAX_DIGITS = 2;
  const candidates = {
    '13': { name: 'Ana Silva', party: 'LISTA VERDE · 13' },
    '22': { name: 'Bruno Costa', party: 'MOVIMENTO CIDADE · 22' },
    '45': { name: 'Carla Mendes', party: 'FRENTE ABERTA · 45' }
  };

  const state = { digits: '', mode: 'input' };
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

  function announce(message) {
    els.liveRegion.textContent = '';
    window.setTimeout(() => { els.liveRegion.textContent = message; }, 20);
  }

  function setMode(mode) {
    state.mode = mode;
  }

  function addDigit(digit) {
    if (state.mode === 'confirmed') return;
    if (state.mode === 'blank') setMode('input');
    if (state.digits.length >= MAX_DIGITS) {
      announce('Limite de dois dígitos atingido. Confira sua escolha ou use Corrige.');
      return;
    }
    state.digits += digit;
    setMode(state.digits.length === MAX_DIGITS ? 'review' : 'input');
    render();
    announce(state.digits.length === MAX_DIGITS ? 'Código preenchido. Confira a escolha antes de confirmar.' : `Número ${digit} inserido.`);
  }

  function correct() {
    const hadSelection = state.digits.length > 0 || state.mode === 'blank' || state.mode === 'confirmed';
    state.digits = '';
    setMode('input');
    render();
    announce(hadSelection ? 'Entrada corrigida. Digite um novo código.' : 'A entrada já está vazia.');
  }

  function chooseBlank() {
    if (state.mode === 'confirmed') return;
    state.digits = '';
    setMode('blank');
    render();
    announce('Voto em branco selecionado. Pressione Confirma para concluir a simulação.');
  }

  function confirm() {
    if (state.mode === 'blank') {
      setMode('confirmed');
      render();
      announce('Voto em branco confirmado. Demonstração concluída.');
      return;
    }
    const candidate = candidates[state.digits];
    if (state.digits.length !== MAX_DIGITS || !candidate) {
      announce('Escolha um código de demonstração válido: 13, 22 ou 45.');
      els.screenSurface.classList.add('shake');
      window.setTimeout(() => els.screenSurface.classList.remove('shake'), 360);
      return;
    }
    setMode('confirmed');
    render();
    announce(`Voto simulado para ${candidate.name} confirmado. Demonstração concluída.`);
  }

  function renderDigits() {
    if (state.mode === 'blank') {
      els.digitRow.innerHTML = '<span class="blank-display">VOTO EM BRANCO</span>';
      els.digitRow.setAttribute('aria-label', 'Voto em branco selecionado');
      return;
    }
    els.digitRow.innerHTML = Array.from({ length: MAX_DIGITS }, (_, index) => {
      const digit = state.digits[index] || '';
      return `<span class="digit-slot ${digit ? 'is-filled' : ''}" aria-hidden="true">${digit}</span>`;
    }).join('');
    els.digitRow.setAttribute('aria-label', state.digits ? `Código digitado: ${state.digits}` : 'Nenhum número digitado');
  }

  function renderCandidate() {
    const candidate = candidates[state.digits];
    const hasFullCode = state.digits.length === MAX_DIGITS;
    const showCard = state.mode !== 'blank' && state.mode !== 'confirmed' && hasFullCode;
    els.candidateCard.hidden = !showCard;
    els.candidateCard.classList.toggle('is-unknown', showCard && !candidate);
    if (candidate) {
      els.candidateLabel.textContent = 'CANDIDATO DE DEMONSTRAÇÃO';
      els.candidateName.textContent = candidate.name;
      els.candidateParty.textContent = candidate.party;
    } else if (showCard) {
      els.candidateLabel.textContent = 'CÓDIGO NÃO RECONHECIDO';
      els.candidateName.textContent = 'Nenhum resultado';
      els.candidateParty.textContent = 'USE 13 · 22 · 45 PARA CONTINUAR';
    }
  }

  function renderCopy() {
    const candidate = candidates[state.digits];
    const validCandidate = Boolean(candidate && state.digits.length === MAX_DIGITS);
    const confirmedCandidate = state.mode === 'confirmed' && validCandidate;

    els.screenSurface.classList.toggle('is-confirmed', state.mode === 'confirmed');
    els.screenSurface.classList.toggle('is-blank', state.mode === 'blank');

    if (state.mode === 'confirmed') {
      els.screenOverline.textContent = 'DEMONSTRAÇÃO CONCLUÍDA';
      els.screenTitle.innerHTML = confirmedCandidate ? 'Voto confirmado.' : 'Voto em branco<br />confirmado.';
      els.screenHint.innerHTML = confirmedCandidate ? `Registro simulado para <b>${candidate.name}</b>.<br />Nenhum dado foi enviado ou armazenado.` : 'Nenhum dado foi enviado ou armazenado.<br />A demonstração pode ser reiniciada.';
      els.screenHelper.innerHTML = '<span class="helper-mark" aria-hidden="true">✓</span><span>Fluxo encerrado com sucesso</span>';
      els.screenModeLabel.textContent = 'CONCLUÍDO';
      els.screenStatusCode.textContent = 'FINALIZADO';
      return;
    }

    if (state.mode === 'blank') {
      els.screenOverline.textContent = 'SELEÇÃO MANUAL';
      els.screenTitle.textContent = 'Voto em branco';
      els.screenHint.innerHTML = 'Nenhum candidato será selecionado.<br />Confirme para concluir a simulação.';
      els.screenHelper.innerHTML = '<span class="helper-mark" aria-hidden="true">↳</span><span>Revise e pressione Confirma</span>';
      els.screenModeLabel.textContent = 'REVISÃO';
      els.screenStatusCode.textContent = 'REVISAR';
      return;
    }

    if (state.mode === 'review') {
      els.screenOverline.textContent = candidate ? 'CANDIDATO LOCALIZADO' : 'CÓDIGO DE DEMONSTRAÇÃO';
      els.screenTitle.innerHTML = candidate ? 'Confira sua<br />escolha' : 'Código não<br />reconhecido';
      els.screenHint.innerHTML = candidate ? 'Se estiver correto, pressione <b>Confirma</b>.<br />Para trocar, pressione <b>Corrige</b>.' : 'Use um dos códigos disponíveis:<br /><b>13</b>, <b>22</b> ou <b>45</b>.';
      els.screenHelper.innerHTML = `<span class="helper-mark" aria-hidden="true">${candidate ? '↳' : '!'}</span><span>${candidate ? 'Aguardando confirmação' : 'Corrija a entrada para continuar'}</span>`;
      els.screenModeLabel.textContent = 'REVISÃO';
      els.screenStatusCode.textContent = candidate ? 'REVISAR' : 'ATENÇÃO';
      return;
    }

    els.screenOverline.textContent = 'CARGO DE DEMONSTRAÇÃO';
    els.screenTitle.innerHTML = state.digits ? 'Continue<br />digitando' : 'Digite o código<br />do candidato';
    els.screenHint.innerHTML = state.digits ? 'Falta um dígito para localizar<br />o candidato de demonstração.' : 'Use o teclado numérico para começar.<br />Experimente <b>13</b>, <b>22</b> ou <b>45</b>.';
    els.screenHelper.innerHTML = '<span class="helper-mark" aria-hidden="true">↳</span><span>Você está no modo de entrada</span>';
    els.screenModeLabel.textContent = 'ENTRADA';
    els.screenStatusCode.textContent = 'AGUARDANDO';
  }

  function renderControls() {
    const candidate = candidates[state.digits];
    const canConfirm = state.mode === 'blank' || (state.mode === 'review' && Boolean(candidate));
    els.confirmButton.disabled = !canConfirm;
    els.entryCounter.textContent = state.mode === 'blank' ? 'BRANCO' : `${state.digits.length} / ${MAX_DIGITS}`;
    els.keypad.querySelectorAll('.number-key').forEach((button) => {
      button.disabled = state.mode === 'confirmed';
    });
    els.blankButton.disabled = state.mode === 'confirmed';
  }

  function renderSteps() {
    const currentStep = state.mode === 'confirmed' ? 3 : (state.mode === 'review' || state.mode === 'blank' ? 2 : 1);
    els.stepItems.forEach((item) => {
      const step = Number(item.dataset.step);
      item.classList.toggle('is-active', step === currentStep);
      item.classList.toggle('is-done', step < currentStep);
    });
  }

  function render() {
    renderDigits();
    renderCandidate();
    renderCopy();
    renderControls();
    renderSteps();
  }

  els.keypad.addEventListener('click', (event) => {
    const button = event.target.closest('[data-digit]');
    if (button) addDigit(button.dataset.digit);
  });
  els.correctButton.addEventListener('click', correct);
  els.blankButton.addEventListener('click', chooseBlank);
  els.confirmButton.addEventListener('click', confirm);

  document.addEventListener('keydown', (event) => {
    if (/^\d$/.test(event.key)) {
      event.preventDefault();
      addDigit(event.key);
    } else if (event.key === 'Backspace') {
      event.preventDefault();
      correct();
    } else if (event.key === 'Enter') {
      event.preventDefault();
      confirm();
    } else if (event.key === 'Escape') {
      event.preventDefault();
      correct();
    }
  });

  render();
})();
