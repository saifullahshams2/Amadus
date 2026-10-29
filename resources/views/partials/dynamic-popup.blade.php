<!-- Dynamic Custom Popup Component (Replaces native browser alert, confirm, prompt) -->
<div id="dynamic-popup-container" class="relative z-[9999]" style="display: none;" aria-live="assertive">
    <!-- Dimmed Backdrop -->
    <div id="dynamic-popup-backdrop" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity duration-200 opacity-0"></div>

    <!-- Dialog Wrapper -->
    <div class="fixed inset-0 z-10 overflow-y-auto flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div id="dynamic-popup-card"
             class="relative transform overflow-hidden rounded-2xl bg-white border border-slate-200 text-left shadow-2xl transition-all duration-200 opacity-0 scale-95 sm:my-8 sm:w-full sm:max-w-lg p-5 sm:p-6 select-none"
             role="dialog"
             aria-modal="true">

            <!-- Top Row: Icon + Title + Close Button -->
            <div class="flex items-start justify-between gap-3 mb-3">
                <div class="flex items-center gap-3">
                    <div id="dynamic-popup-icon-wrapper" class="w-10 h-10 rounded-full flex items-center justify-center shrink-0">
                        <!-- Icon injected by JS -->
                    </div>
                    <div>
                        <h3 id="dynamic-popup-title" class="text-sm sm:text-base font-bold text-slate-900 leading-snug"></h3>
                        <div id="dynamic-popup-subtitle" class="text-[11px] text-slate-500 font-mono hidden"></div>
                    </div>
                </div>
                <button type="button"
                        id="dynamic-popup-btn-close"
                        class="text-slate-400 hover:text-slate-700 hover:bg-slate-100 p-1.5 rounded-lg transition-colors"
                        aria-label="Close">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Body Message -->
            <div class="mt-2 mb-4">
                <div id="dynamic-popup-message" class="text-xs sm:text-sm text-slate-600 leading-relaxed whitespace-pre-wrap select-text font-sans"></div>

                <!-- Optional Prompt Input / Textarea -->
                <div id="dynamic-popup-prompt-group" class="mt-3 hidden">
                    <textarea id="dynamic-popup-prompt-input"
                              rows="4"
                              class="w-full bg-slate-50 border border-slate-300 rounded-lg p-2.5 text-xs font-mono text-slate-800 outline-none focus:border-blue-500 focus:bg-white focus:ring-1 focus:ring-blue-500 select-all"></textarea>
                    <div class="mt-2 flex items-center justify-between">
                        <span id="dynamic-popup-copy-hint" class="text-[10px] text-slate-400 font-mono">Click copy to save to clipboard</span>
                        <button type="button"
                                id="dynamic-popup-btn-copy"
                                class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-xs font-semibold flex items-center gap-1 transition-colors">
                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                            <span id="dynamic-popup-copy-text">Copy All</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Footer Action Buttons -->
            <div class="mt-5 sm:mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button"
                        id="dynamic-popup-btn-cancel"
                        class="w-full sm:w-auto px-4 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 rounded-lg text-xs font-semibold transition-colors shadow-xs">
                    Cancel
                </button>
                <button type="button"
                        id="dynamic-popup-btn-confirm"
                        class="w-full sm:w-auto px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition-colors shadow-sm flex items-center justify-center gap-1.5">
                    <span id="dynamic-popup-confirm-label">Confirm</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    let currentResolver = null;
    let isPromptMode = false;

    function getContainer() {
        return document.getElementById('dynamic-popup-container');
    }
    function getBackdrop() {
        return document.getElementById('dynamic-popup-backdrop');
    }
    function getCard() {
        return document.getElementById('dynamic-popup-card');
    }
    function getTitle() {
        return document.getElementById('dynamic-popup-title');
    }
    function getMessage() {
        return document.getElementById('dynamic-popup-message');
    }
    function getIconWrapper() {
        return document.getElementById('dynamic-popup-icon-wrapper');
    }
    function getBtnCancel() {
        return document.getElementById('dynamic-popup-btn-cancel');
    }
    function getBtnConfirm() {
        return document.getElementById('dynamic-popup-btn-confirm');
    }
    function getConfirmLabel() {
        return document.getElementById('dynamic-popup-confirm-label');
    }
    function getBtnClose() {
        return document.getElementById('dynamic-popup-btn-close');
    }
    function getPromptGroup() {
        return document.getElementById('dynamic-popup-prompt-group');
    }
    function getPromptInput() {
        return document.getElementById('dynamic-popup-prompt-input');
    }
    function getBtnCopy() {
        return document.getElementById('dynamic-popup-btn-copy');
    }

    const icons = {
        success: `<div class="w-10 h-10 rounded-full bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-600 shadow-xs"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></div>`,
        error: `<div class="w-10 h-10 rounded-full bg-rose-50 border border-rose-200 flex items-center justify-center text-rose-600 shadow-xs"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg></div>`,
        warning: `<div class="w-10 h-10 rounded-full bg-amber-50 border border-amber-200 flex items-center justify-center text-amber-600 shadow-xs"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg></div>`,
        info: `<div class="w-10 h-10 rounded-full bg-blue-50 border border-blue-200 flex items-center justify-center text-blue-600 shadow-xs"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>`,
        danger: `<div class="w-10 h-10 rounded-full bg-rose-50 border border-rose-200 flex items-center justify-center text-rose-600 shadow-xs"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></div>`
    };

    function showModal() {
        const container = getContainer();
        const backdrop = getBackdrop();
        const card = getCard();
        if (!container) return;

        container.style.display = 'block';
        requestAnimationFrame(() => {
            backdrop.classList.remove('opacity-0');
            backdrop.classList.add('opacity-100');
            card.classList.remove('opacity-0', 'scale-95');
            card.classList.add('opacity-100', 'scale-100');
        });
    }

    function hideModal() {
        const container = getContainer();
        const backdrop = getBackdrop();
        const card = getCard();
        if (!container) return;

        backdrop.classList.remove('opacity-100');
        backdrop.classList.add('opacity-0');
        card.classList.remove('opacity-100', 'scale-100');
        card.classList.add('opacity-0', 'scale-95');

        setTimeout(() => {
            container.style.display = 'none';
        }, 200);
    }

    function resolveModal(val) {
        hideModal();
        if (currentResolver) {
            const res = currentResolver;
            currentResolver = null;
            res(val);
        }
    }

    // Dynamic Alert Popup
    window.customAlert = function(message, title, type = 'info') {
        return new Promise((resolve) => {
            currentResolver = resolve;
            isPromptMode = false;

            const t = type.toLowerCase();
            const iconKey = (t === 'error' || t === 'fail' || t === 'failed') ? 'error'
                          : (t === 'success' || t === 'ok') ? 'success'
                          : (t === 'warning' || t === 'warn') ? 'warning'
                          : 'info';

            getTitle().textContent = title || (iconKey === 'success' ? 'Success' : (iconKey === 'error' ? 'Notice' : (iconKey === 'warning' ? 'Warning' : 'Information')));
            getMessage().textContent = message || '';
            getIconWrapper().innerHTML = icons[iconKey];

            getPromptGroup().classList.add('hidden');
            getBtnCancel().classList.add('hidden');

            const btnConfirm = getBtnConfirm();
            btnConfirm.className = 'w-full sm:w-auto px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition-colors shadow-sm flex items-center justify-center';
            getConfirmLabel().textContent = 'OK';

            showModal();
            setTimeout(() => btnConfirm.focus(), 50);
        });
    };

    // Dynamic Confirm Popup
    window.customConfirm = function(message, title, options = {}) {
        return new Promise((resolve) => {
            currentResolver = resolve;
            isPromptMode = false;

            const lowerMsg = (message || '').toLowerCase();
            const isDanger = options.type === 'danger' || lowerMsg.includes('delete') || lowerMsg.includes('reject') || lowerMsg.includes('reset') || lowerMsg.includes('purge');
            const iconKey = isDanger ? 'danger' : (options.type === 'warning' ? 'warning' : 'info');

            getTitle().textContent = title || (isDanger ? 'Confirm Dangerous Action' : 'Please Confirm');
            getMessage().textContent = message || '';
            getIconWrapper().innerHTML = icons[iconKey];

            getPromptGroup().classList.add('hidden');
            getBtnCancel().classList.remove('hidden');
            getBtnCancel().textContent = options.cancelText || 'Cancel';

            const btnConfirm = getBtnConfirm();
            if (isDanger) {
                btnConfirm.className = 'w-full sm:w-auto px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-bold transition-colors shadow-sm flex items-center justify-center';
            } else {
                btnConfirm.className = 'w-full sm:w-auto px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition-colors shadow-sm flex items-center justify-center';
            }
            getConfirmLabel().textContent = options.confirmText || (isDanger ? 'Yes, Proceed' : 'Confirm');

            showModal();
            setTimeout(() => btnConfirm.focus(), 50);
        });
    };

    // Dynamic Prompt Popup
    window.customPrompt = function(message, defaultValue = '', title = 'Information Input') {
        return new Promise((resolve) => {
            currentResolver = resolve;
            isPromptMode = true;

            getTitle().textContent = title;
            getMessage().textContent = message || '';
            getIconWrapper().innerHTML = icons.info;

            const promptGroup = getPromptGroup();
            promptGroup.classList.remove('hidden');

            const input = getPromptInput();
            input.value = defaultValue || '';

            getBtnCancel().classList.remove('hidden');
            getBtnCancel().textContent = 'Close';

            const btnConfirm = getBtnConfirm();
            btnConfirm.className = 'w-full sm:w-auto px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition-colors shadow-sm flex items-center justify-center';
            getConfirmLabel().textContent = 'Done';

            showModal();
            setTimeout(() => {
                input.focus();
                input.select();
            }, 50);
        });
    };

    // Global alias & override
    window.appAlert = window.customAlert;
    window.appConfirm = window.customConfirm;
    window.appPrompt = window.customPrompt;

    // Direct browser alert hook so any native alert call uses the dynamic custom popup
    const nativeAlert = window.alert;
    window.alert = function(msg) {
        return window.customAlert(msg);
    };

    document.addEventListener('DOMContentLoaded', () => {
        const btnConfirm = getBtnConfirm();
        const btnCancel = getBtnCancel();
        const btnClose = getBtnClose();
        const backdrop = getBackdrop();
        const btnCopy = getBtnCopy();

        if (btnConfirm) {
            btnConfirm.addEventListener('click', () => {
                if (isPromptMode) {
                    const val = getPromptInput()?.value || '';
                    resolveModal(val);
                } else {
                    resolveModal(true);
                }
            });
        }

        if (btnCancel) {
            btnCancel.addEventListener('click', () => {
                resolveModal(isPromptMode ? null : false);
            });
        }

        if (btnClose) {
            btnClose.addEventListener('click', () => {
                resolveModal(isPromptMode ? null : false);
            });
        }

        if (backdrop) {
            backdrop.addEventListener('click', () => {
                resolveModal(isPromptMode ? null : false);
            });
        }

        if (btnCopy) {
            btnCopy.addEventListener('click', () => {
                const text = getPromptInput()?.value || '';
                if (text) {
                    navigator.clipboard.writeText(text).then(() => {
                        const copyText = document.getElementById('dynamic-popup-copy-text');
                        if (copyText) {
                            const orig = copyText.textContent;
                            copyText.textContent = 'Copied!';
                            setTimeout(() => { copyText.textContent = orig; }, 1500);
                        }
                    });
                }
            });
        }

        document.addEventListener('keydown', (e) => {
            const container = getContainer();
            if (!container || container.style.display === 'none') return;

            if (e.key === 'Escape') {
                e.preventDefault();
                resolveModal(isPromptMode ? null : false);
            } else if (e.key === 'Enter' && !e.shiftKey && e.target.tagName !== 'TEXTAREA') {
                e.preventDefault();
                if (isPromptMode) {
                    resolveModal(getPromptInput()?.value || '');
                } else {
                    resolveModal(true);
                }
            }
        });
    });
})();
</script>
