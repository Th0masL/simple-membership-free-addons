document.addEventListener('DOMContentLoaded', function () {
    // Select all radio buttons with the name 'prot_auto_redirection'.
    const redirection_inputs = document.querySelectorAll('input[name="prot_auto_redirection"]');
    // Select specific url input field.
    const redirection_to_specific_url_wrap = document.getElementById('prot_auto_redirection_to_specific_page_url_wrap');

    // Add an event listener to each radio button
    redirection_inputs?.forEach(radio => {
        radio.addEventListener('change', (e) => {
            if (e.target.value === 'specific-page') {
                redirection_to_specific_url_wrap.style.display = 'block';
            } else {
                redirection_to_specific_url_wrap.style.display = 'none';
            }
        });
    });
})

document.addEventListener('DOMContentLoaded', function () {
    const specific_pages_lockdown_fields = document.getElementById('lockdown_specific_url_settings_fields');
    const domain_lockdown_checkbox = document.getElementById('lockdown_sidewide_checkbox');

    // Watch domain level protection checkbox change:
    domain_lockdown_checkbox?.addEventListener('change', function () {
        if (domain_lockdown_checkbox.checked) {
            swpm_fpp_disable_all_input_fields(specific_pages_lockdown_fields)
            specific_pages_lockdown_fields?.classList.add('disabled');
        } else {
            swpm_fpp_enable_all_input_fields(specific_pages_lockdown_fields, false)
            specific_pages_lockdown_fields?.classList.remove('disabled');
        }
    });

    // For initial page load.
    if (specific_pages_lockdown_fields?.classList.contains('disabled')) {
        swpm_fpp_disable_all_input_fields(specific_pages_lockdown_fields)
        specific_pages_lockdown_fields?.classList.add('disabled');
    }

});

function swpm_fpp_disable_all_input_fields(fieldset) {
    fieldset?.querySelectorAll('input,textarea').forEach(input => {
        input.disabled = true;
    });
}

function swpm_fpp_enable_all_input_fields(fieldset) {
    fieldset?.querySelectorAll('input,textarea').forEach(input => {
        input.disabled = false;
    });
}