$('#b-country-input').change( function() {
    _this = $(this);
    $.get("{{ route('get.state.from.country')}}",{id: $(_this).val()})
    .done (function (data) {
        $('#b-state-input').html(data);
    })
})

if ( $('#s-country-input').length > 0 ) {
  $('#s-country-input').change( function() {
        _this = $(this);
        $.get("{{ route('get.state.from.country')}}",{id: $(_this).val()})
        .done (function (data) {
            $('#s-state-input').html(data);
        })
    })  
}