<script>
    grecaptcha.ready(function() {
    grecaptcha.execute("{{config('recapcha.key') }}", {action: 'homepage'}).then(function(token) {
        $.ajax ( {
            type: 'get',
            dataType: 'json',
            url: "{{ action('GoogleRecapchaController@verify') }}",
            data: {token: token},
            success: function(response) {
                //alert (response);
            }
        })
    });
});
</script>