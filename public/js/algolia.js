(function() {
    var client = algoliasearch('VPBCCIZ29A', 'fc8677ee236999a6b2eab266dfe55868');
    var index = client.initIndex('products');

    autocomplete('#aa-search-input',
        {hint: false}, {
            source: autocomplete.sources.hits(index, {hitsPerPage: 10}),
            display: 'details',
            templates: {
                // header: '<div class="aa-suggestions-category">Products</div>',
                suggestion: function(suggestion) {
                    if (suggestion._highlightResult.case_size === undefined)
                        case_size = '';
                    else case_size = suggestion._highlightResult.case_size.value;

                    if (suggestion._highlightResult.reference === undefined)
                        reference = '';
                    else reference = suggestion._highlightResult.reference.value;

                    if (suggestion._highlightResult.model === undefined)
                        model = '';
                    else model = suggestion._highlightResult.model.value;

                    return '<div class="aa-search-container">' +
                        '<div class="aa-search-image"><img src="'+suggestion.image+'"></div>' +
                        '<div class="aa-search-title"><span>'+ suggestion.condition + '</span> '+
                        '<div>'+ suggestion._highlightResult.category.value + '</div> '+
                        '<span>'+ model + '</span> '+
                        '<span>'+ case_size + '</span> '+
                        '<span>'+ reference + '</span> ' +
                        '</div><div class="aa-search-id"><span>'+ suggestion.id + '</span></div></div>';

                    },
                    empty: function(result) {
                        return "<span class='aa-error'>Didn't find any product results for '" + result.query + "'</span>";
                    },
                    footer: '<div class="aa-footer">Powered by <a href="http://www.algolia.com"><img src="https://www.algolia.com/assets/algolia128x40.png"/></a></div>'
                    
            }
        }).on('autocomplete:selected', function (event, suggestion,dataset) {
            window.location.href = window.location.origin + suggestion.slug
        }).on('autocomplete:opened', function (e) {
            //alert('opened on focus')
           // e.preventDefault()
        //    debugger
        });
})();