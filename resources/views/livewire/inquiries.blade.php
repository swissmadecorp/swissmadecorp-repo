<div x-data="{ isSliderVisible: false,
            selectedRow: null,
            focusSearchBox() {
                if (!this.isSliderVisible) {
                    $refs.searchbox.focus();
                    $refs.searchbox.select();
                }
            }
                }"
    x-init="focusSearchBox()"
    @keydown.window="
    if ($event.key === '=') {

                if ($refs.searchbox !== document.activeElement && !isSliderVisible) {
                    $event.preventDefault();
                    focusSearchBox();
                }
            }">
    @section('main_header')
    <!-- <link href="/css/dropzone.css" rel="stylesheet"> -->
    <link href="/editable-select/jquery-editable-select.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/css/lightgallery-bundle.min.css" integrity="sha512-nUqPe0+ak577sKSMThGcKJauRI7ENhKC2FQAOOmdyCYSrUh0GnwLsZNYqwilpMmplN+3nO3zso8CWUgu33BDag==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    @stop

    @section ('footer')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/lightgallery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/plugins/zoom/lg-zoom.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/plugins/thumbnail/lg-thumbnail.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/plugins/fullscreen/lg-fullscreen.umd.min.js"></script>
    <script src="/js/jquery.autocomplete.min.js"></script>
    <script src="/editable-select/jquery-editable-select.js"></script>
    <script src="/js/jquery.mask.js" type="text/javascript"></script>
    @stop


</div>
