(function () {
    'use strict';
    var pending = [];
    var ready = false;
    var region;
    var activeConfirmation = null;

    function notify(message, type) {
        if (!ready) { pending.push([message, type]); return; }
        type = ['success', 'error', 'warning', 'info'].indexOf(type) >= 0 ? type : 'info';
        var card = document.createElement('div');
        card.className = 'novel-toast novel-toast-' + type;
        card.setAttribute('role', type === 'error' || type === 'warning' ? 'alert' : 'status');
        card.setAttribute('aria-atomic', 'true');
        var content = document.createElement('div');
        var title = document.createElement('strong');
        title.textContent = {success: 'Tudo certo', error: 'Não foi possível continuar', warning: 'Atenção', info: 'Para continuar'}[type];
        var text = document.createElement('p');
        text.textContent = String(message);
        content.appendChild(title);
        content.appendChild(text);
        var close = document.createElement('button');
        close.type = 'button';
        close.className = 'novel-toast-close';
        close.setAttribute('aria-label', 'Fechar notificação');
        close.textContent = '×';
        close.addEventListener('click', function () { card.remove(); });
        card.appendChild(content);
        card.appendChild(close);
        region.appendChild(card);
        // Errors and prerequisite messages stay visible until dismissed.
        if (type === 'success') {
            var timer = setTimeout(function () { if (!card.contains(document.activeElement)) { card.remove(); } }, 8000);
            card.addEventListener('mouseenter', function () { clearTimeout(timer); });
            card.addEventListener('focusin', function () { clearTimeout(timer); });
        }
    }

    function confirmExit(url) {
        if (activeConfirmation) { return; }
        var dialog = document.createElement('dialog');
        activeConfirmation = dialog;
        dialog.className = 'novel-confirm';
        dialog.setAttribute('aria-labelledby', 'novel-confirm-title');
        dialog.setAttribute('aria-describedby', 'novel-confirm-description');
        dialog.innerHTML = '<h2 id="novel-confirm-title">Sair desta atividade?</h2><p id="novel-confirm-description">Se você sair, as respostas desta rodada não serão salvas.</p><div class="novel-confirm-actions"><button type="button" class="btn btn-default" data-stay>Continuar atividade</button><button type="button" class="btn btn-danger" data-leave>Sair da atividade</button></div>';
        var previousFocus = document.activeElement;
        function dismiss() {
            dialog.remove();
            activeConfirmation = null;
            if (previousFocus) { previousFocus.focus(); }
        }
        dialog.querySelector('[data-stay]').addEventListener('click', dismiss);
        dialog.querySelector('[data-leave]').addEventListener('click', function () { window.location.assign(url); });
        dialog.addEventListener('cancel', function (event) { event.preventDefault(); dismiss(); });
        document.body.appendChild(dialog);
        dialog.showModal();
        dialog.querySelector('[data-stay]').focus();
    }

    function setupActivity() {
        var screen = document.querySelector('.novel-activity');
        if (!screen) { return; }
        var carousel = screen.querySelector('#carrosselPalavras, #carrosselTestes');
        var form = screen.querySelector('form');
        if (!form) { return; }
        // Hidden carousel fields cannot receive focus during native validation.
        form.noValidate = true;
        var started = Date.now() - (Number(screen.dataset.elapsed || 0) * 1000);
        var clock = document.getElementById('tempo');
        var duration = document.getElementById('duracao');
        if (clock && duration) {
            function updateClock() {
                var seconds = Math.floor((Date.now() - started) / 1000);
                var pad = function (n) { return String(n).padStart(2, '0'); };
                clock.value = pad(Math.floor(seconds / 3600)) + ':' + pad(Math.floor(seconds / 60) % 60) + ':' + pad(seconds % 60);
                duration.value = seconds;
            }
            updateClock();
            var timer = setInterval(updateClock, 1000);
            window.addEventListener('pagehide', function () { clearInterval(timer); }, {once: true});
        }
        function updateProgress() {
            if (!carousel) { return; }
            var items = Array.from(carousel.querySelectorAll('.carousel-inner > .item'));
            var index = items.findIndex(function (item) { return item.classList.contains('active'); });
            var progress = screen.querySelector('.novel-question-progress');
            if (progress) { progress.textContent = index === items.length - 1 ? 'Revise e envie suas respostas' : 'Questão ' + (index + 1) + ' de ' + (items.length - 1); }
            carousel.querySelector('[data-slide="prev"]').disabled = index <= 0;
            carousel.querySelector('[data-slide="next"]').disabled = index >= items.length - 1;
        }
        if (carousel && window.jQuery) { window.jQuery(carousel).on('slid.bs.carousel', updateProgress); }
        if (carousel) {
            carousel.querySelector('[data-slide="next"]').addEventListener('click', function (event) {
                var active = carousel.querySelector('.item.active');
                var invalid = Array.from(active.querySelectorAll('input[required]')).find(function (input) { return !input.checkValidity(); });
                if (invalid) {
                    event.preventDefault(); event.stopPropagation();
                    notify('Complete esta questão antes de continuar.', 'warning'); invalid.focus();
                }
            }, true);
        }
        updateProgress();
        form.addEventListener('submit', function (event) {
            if (event.submitter && event.submitter.value === 'saveExit') return;
            var fields = Array.from(form.querySelectorAll('input[required]'));
            var invalid = fields.find(function (field) { return !field.checkValidity(); });
            if (!invalid) {
                if (duration) { duration.value = Math.max(1, Math.floor((Date.now() - started) / 1000)); }
                return;
            }
            event.preventDefault();
            notify('Ainda há respostas em branco. Complete a questão indicada antes de enviar.', 'warning');
            var item = invalid.closest('.item');
            if (item && carousel && window.jQuery) {
                var index = Array.from(carousel.querySelectorAll('.carousel-inner > .item')).indexOf(item);
                var focus = function () { invalid.focus(); };
                if (item.classList.contains('active')) { focus(); }
                else { window.jQuery(carousel).one('slid.bs.carousel', focus).carousel(index); }
            } else { invalid.focus(); }
        });
    }

    window.Novel = {notify: notify, confirmExit: confirmExit};
    document.addEventListener('DOMContentLoaded', function () {
        region = document.createElement('section');
        region.className = 'novel-notifications';
        region.setAttribute('aria-label', 'Notificações');
        document.body.appendChild(region);
        ready = true;
        pending.splice(0).forEach(function (entry) { notify(entry[0], entry[1]); });
        document.querySelectorAll('img').forEach(function (img) {
            var fallback = document.body.getAttribute('data-image-fallback');
            if (!fallback) { return; }
            var replace = function () {
                if (img.getAttribute('src') !== fallback) {
                    img.src = fallback;
                    img.alt = (img.alt ? img.alt + ': ' : '') + 'ilustração indisponível';
                }
            };
            img.addEventListener('error', replace, {once: true});
            if (img.complete && img.naturalWidth === 0) { replace(); }
        });
        setupActivity();
    });
}());

// Navegação local: funções essenciais não dependem de CDN.
document.addEventListener('DOMContentLoaded', function () {
    var toggles = Array.from(document.querySelectorAll('[data-novel-toggle]'));
    function closeAll(restoreFocus) {
        toggles.forEach(function (button) {
            var target = document.getElementById(button.dataset.novelToggle);
            if (button.getAttribute('aria-expanded') === 'true' && restoreFocus) button.focus();
            button.setAttribute('aria-expanded', 'false');
            if (target.classList.contains('novel-navbar-menu')) target.classList.remove('is-open');
            else target.hidden = true;
        });
    }
    toggles.forEach(function (button) {
        button.addEventListener('click', function () {
            var target = document.getElementById(button.dataset.novelToggle);
            var open = button.getAttribute('aria-expanded') !== 'true';
            button.setAttribute('aria-expanded', String(open));
            if (target.classList.contains('novel-navbar-menu')) target.classList.toggle('is-open', open);
            else target.hidden = !open;
        });
    });
    document.addEventListener('keydown', function (event) { if (event.key === 'Escape') closeAll(true); });
    document.addEventListener('click', function (event) { if (!event.target.closest('.novel-navbar')) closeAll(false); });
    document.querySelectorAll('[data-novel-auth]').forEach(function (button) {
        button.addEventListener('click', function () {
            var reset = button.dataset.novelAuth === 'reset';
            document.getElementById('novel-login-form').hidden = reset;
            document.getElementById('novel-reset-form').hidden = !reset;
            var first = document.querySelector((reset ? '#novel-reset-form' : '#novel-login-form') + ' input:not([type=hidden])');
            if (first) first.focus();
        });
    });
});
