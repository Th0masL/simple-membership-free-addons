/* global swpm_cft_messages, turnstile */

// Marks the widgets that were rendered by this script (so they don't get rendered twice).
const SWPM_CFT_RENDERED_ATTR = 'data-swpm-cft-rendered';
const SWPM_CFT_RETRY_DELAY = 250;
const SWPM_CFT_MAX_ATTEMPTS = 40;

function onSwpmCFTLoadCB(token) {
    console.log(`Turnstile challenge successful, token received`);
}

function onSwpmCFTLoadECB(err) {
    console.debug('CFT Error');
    console.debug(err);
    swpm_cft_error(swpm_cft_messages.config_error);
}

function swpm_cft_error(msg) {
    document.querySelectorAll('.swpm-cf-turnstile')?.forEach(node => {
        // check if msg already printed.
        if (!node.querySelector('.swpm-cft-error-msg')) {
            let cft_msg = document.createElement('div');
            cft_msg.classList.add('swpm-cft-error-msg');
            cft_msg.innerText = msg;
            node.appendChild(cft_msg);
        }
    })
}

/**
 * Renders a turnstile widget that got added to the page after the initial page load.
 * Some themes load the pages via AJAX. The implicit rendering of the turnstile api script
 * only scans the page once (on load), so those widgets need to be rendered manually.
 */
const swpm_cft_render_widget = (node, attempt = 0) => {
    if (node.getAttribute(SWPM_CFT_RENDERED_ATTR) === '1') {
        // Already rendered by this script.
        return;
    }

    if (node.hasChildNodes()) {
        // Turnstile puts an iframe and a hidden input in the container. So this widget
        // has already been rendered (by the implicit rendering on the initial page load).
        node.setAttribute(SWPM_CFT_RENDERED_ATTR, '1');
        return;
    }

    if (typeof window.turnstile === 'undefined' || typeof window.turnstile.render !== 'function') {
        // The turnstile api script is not ready yet. Retry for a short while, then leave it to the implicit rendering.
        if (attempt < SWPM_CFT_MAX_ATTEMPTS) {
            window.setTimeout(() => swpm_cft_render_widget(node, attempt + 1), SWPM_CFT_RETRY_DELAY);
        }
        return;
    }

    node.setAttribute(SWPM_CFT_RENDERED_ATTR, '1');
    window.turnstile.render(node);
}

/**
 * Watches the document for turnstile widgets that get added to the page dynamically.
 */
const swpm_cft_watch_for_new_widgets = () => {
    if (typeof window.MutationObserver === 'undefined') {
        return;
    }

    const observer = new MutationObserver(mutations => {
        mutations.forEach(mutation => {
            mutation.addedNodes.forEach(node => {
                if (node.nodeType !== 1) {
                    // Not an element node.
                    return;
                }
                if (node.classList?.contains('swpm-cf-turnstile')) {
                    swpm_cft_render_widget(node);
                }
                node.querySelectorAll?.('.swpm-cf-turnstile')?.forEach(widget => swpm_cft_render_widget(widget));
            });
        });
    });

    observer.observe(document.body, {childList: true, subtree: true});
}

/**
 * Calls the given callback once the turnstile api script is loaded.
 */
const swpm_cft_when_turnstile_ready = (callback, attempt = 0) => {
    if (typeof window.turnstile !== 'undefined' && typeof window.turnstile.render === 'function') {
        callback();
        return;
    }
    if (attempt < SWPM_CFT_MAX_ATTEMPTS) {
        window.setTimeout(() => swpm_cft_when_turnstile_ready(callback, attempt + 1), SWPM_CFT_RETRY_DELAY);
    }
}

const swpm_cft_init = () => swpm_cft_when_turnstile_ready(swpm_cft_watch_for_new_widgets);

if (document.body) {
    swpm_cft_init();
} else {
    document.addEventListener('DOMContentLoaded', swpm_cft_init);
}
