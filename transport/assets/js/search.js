function initAutocompleteSearch(inputId, resultsId, url, onSelect) {
    let timeout;
    $(`#${inputId}`).on('input', function() {
        clearTimeout(timeout);
        let term = $(this).val();
        if(term.length < 2) return;
        timeout = setTimeout(() => {
            $.getJSON(`${url}?q=${term}`, function(data) {
                let html = '';
                data.forEach(item => {
                    html += `<div class="autocomplete-suggestion" onclick="selectSuggestion(this)">${item.label}</div>`;
                });
                $(`#${resultsId}`).html(html).show();
            });
        }, 300);
    });
}
