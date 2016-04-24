function ajaxSubmit($form, options) {
    options = options || {};
    options.type = $form.attr('method') || 'POST';
    options.url = $form.attr('action');
    options.data = $form.serialize();

    $.ajax(options);
}