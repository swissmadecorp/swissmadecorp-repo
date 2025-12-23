@extends('layouts.admin-default')

@section ('header')
<link href="/css/dropzone.css" rel="stylesheet">
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<!-- <link href="{{ asset('/public/fancybox/jquery.fancybox.min.css') }}" rel="stylesheet"> -->
@endsection

@section ('content')

 {{$token}}

@endsection

@section ("footer")
<!-- <script src="{{ asset('/public/fancybox/jquery.fancybox.min.js') }}"></script> -->
<script src="/js/dropzone.js') }}"></script>
@endsection

@section ('jquery')
<script>

	$(document).ready( function() {
		$('#upc-input').focus();
		var amazonProducts = Array;
		var itemIndex = 0;

        Dropzone.autoDiscover = false;
		
		@if(Session::has('message'))
			$.alert("{{Session::get('message')}}");
		@endif

        var myDropzone = new Dropzone("div#dropzoneFileUpload", {
            url: "{{route('upload.image')}}",
            maxFilesize: 10, // MB
            maxFiles: 4,
            parallelUploads: 4,
            dictDefaultMessage:'Drop files here or click to upload manually',
            addRemoveLinks: true,
            autoProcessQueue:false,
            uploadMultiple: true,
            sending: function(file, xhr, formData) {

                $("form").find("input").each(function(){
                    if ($(this).attr("name") !==undefined && $(this).attr("name")!='_method')
                        formData.append($(this).attr("name"), $(this).val());
                });

            }
        });

        myDropzone.on("successmultiple", function(file,resp){
            if(resp.message=="success"){
                //alert("Faild to upload image!");
                
                if (resp.message == "success") {
					$(resp.content).insertAfter('#retail-input'); 
                }

                $('#amazonForm').submit();
            }
        });

		$('.search').click( function (e) {
			e.preventDefault();
			$.ajax({
				type : "get",
				cache: false,
				url: "https://swissmadecorp.com/admin/amazon/getSimilarProductByName?title="+$('#title').val()+'&'+$('#marketLocation').val(), 
				success: function(response) {
					//$('#elementContainer table').hide().html(response).fadeIn('slow');
					amazonProducts = response;
					itemIndex = 0;
					$.fancybox.open({
						src  : '#amazonProduct',
						type : 'inline',
						beforeShow: function() { setProduct(itemIndex); },
						nextEffect: 'elastic', // elastic, fade or none. default: elastic
						prevEffect: 'elastic',
						fitToView : true,
						autoSize : true,
						helpers		: {
							title	: { type : 'outside' }
						}
					});
				}
			});	
		})

		var setProduct = function (index) {
			$('#amazonProduct img').attr('src',amazonProducts[itemIndex]["image"]);
			$('#amazonProduct span.asin').html(amazonProducts[itemIndex]["ASIN"]);
			$('#amazonProduct span.info').html(amazonProducts[itemIndex]["product"]);
			$('.totalProducts').html((index+1) + ' of ' +amazonProducts.length)
		}

		$('.next').click( function (e) {
			e.preventDefault()
			itemIndex++;
			if (itemIndex>=amazonProducts.length){
				itemIndex=amazonProducts.length-1;
				return
			}
			setProduct(itemIndex);
		}) 

		$('.previous').click( function (e) {
			e.preventDefault()
			itemIndex--;
			if (itemIndex<0) {
				itemIndex=0;
				return
			}
			setProduct(itemIndex);
		}) 

		$('.use').click( function (e) {
			e.preventDefault()
			$('#upc-input').val(amazonProducts[itemIndex]["ASIN"]);
			$.fancybox.close();
		})

		$('.totalchars').html($('#title').val().length);
		$('#title').on('input',function() {
			$('.totalchars').html($('#title').val().length);
		})

		$(".uploadPhoto").click( function(e) {
            if ($('.dz-image-preview').length > 0)
                e.preventDefault();
            
            //if ($('.activeSnapshot').is(':visible'))
                myDropzone.processQueue();
        })
	})

</script>

@endsection