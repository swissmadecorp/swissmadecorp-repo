$( document ).ready(function() {
		var categoryId
		var categories = [];
		var selectors= $('#title, #price, .btn-success, .primary_category');
		var elementWithFocus
		var activeCombo
		var iframe, dialog
		var eBaySessId
		var exRuname

		$(document).on('click', '#save_field', function() {
			var field_name = $(this).prev().val();
			if(field_name=='') {
				$.alert ('Please enter field name and try again');
				return false
			} 

			$.ajax({
				type : "post",
				url: saveSpecificField,
				data: {
					_token: csrf_token,
					field_name: field_name,
					catId: categoryId
				},
				success: function(response) {
					$('.customspecificitem').remove();
					$('#custom_field').prepend('<span>'+field_name+'</span><br>');
					$('#specifics ul.topLevel').append(response.new_field)
					
					$.alert(response.alert);
				}
			});
		})
		
		$('#prodHtml').load( function () {
			iframe = $('#prodHtml').contents();
			//updateProductionTable()
		})

		$("#load_active_items").click ( function () {
			$.post(listItems, data, function(response) {
				$("#activeItems tbody").html(response);
			});		
		})
		
		$("#template").on('click', '.remove', function() {
			$(this).parents('tr').remove();
			$('#production',iframe).html('');
			updateProductionTable();
		})
	
		$(".tab").click ( function () {
			$(this).next().slideToggle();
		})
		
		$("#template").on('click', '.add', function() {
			var rows = 1 + Math.floor(Math.random() * 6000);//$(this).parents('table').find('tr:last').index()+1;
			$(this).parents('tr').after('<tr><td><input name="c0'+rows+'" class="form-control" type="text"></td><td><input name="t0'+rows+'" id="t'+rows+'" class="form-control" type="text"></td><td><input value="x" class="btn btn-outline-primary remove" type="button"></td><td><input value="+" class="btn btn-outline-primary add" type="button"></td></tr>');
			$('#production',iframe).html('');
			updateProductionTable();
		})
		
		$("#template").on('input propertychange', 'input', function() {
			var propName = $(this).attr('name');
			var txtBox = iframe.find('#'+propName+'_pr');
			txtBox.text( $(this).val() ); 
			if ($(this).css("font-weight") == "700") txtBox.css("font-weight", $(this).css("font-weight"));
		})
		
		$("#load_from_server").click(  function() {			
			var data = {
				_token: csrf_token
			};
			
			$.post(loadImages, data, function(response) {
				$( "#imageDlg table" ).html(response);
			});						
			dialog = $( "#imageDlg" ).dialog( "open" );
		})
		
		$("#imageDlg").on('click', 'input.deleteImage', function() {
			var trParent = $(this).parents('tr');
			if (confirm ('You\re about to delete this file. Are you sure you want to do this?')) {
			
				var data = {
					_token: csrf_token,
				   imageName: trParent.find('td:eq(1)').text()
				};
				
				$.post(deleteImage, data, function(response) {
					$(trParent)
					.children('td, th')
					.animate({ padding: 0 })
					.wrapInner('<div />')
					.children()
					.slideUp(function() { $(this).closest('tr').remove(); });
					
				});	
			}
		})
		
		$( "#imageDlg" ).dialog({
			modal: true,
			width: 650,
			autoOpen: false,
			buttons: {
				Ok: function() {
					$( "#imageDlg table tr td input[type='radio']" ).each(function () {
						if ( $(this).is(":checked") ) {
							var imgPath
							//if (document.location.hostname == 'localhost' ) 
							//	imgPath= '../../';
							//else imgPath= 'www.designtemplatesnow.com/server/php/files/';
							imgPath = $(this).parents('tr').find('td:eq(0) img').data('path');
							
							if ($(this).val() == 'large' ) $('.mainImage img:eq(0)', iframe).attr('src', imgPath+$(this).parents('tr').find('td:eq(1)').text());
							if ( $('.thumbcontainer ul li', iframe).length < 4  )
								$('.thumbcontainer ul', iframe).append('<li><img onmouseover="imageSelector(this)" src="'+imgPath+'thumbnail/'+$(this).parents('tr').find('td:eq(1)').text()+'" /></li>');
							else 
								$('.localThumbcontainer ul').append('<li><img src="'+imgPath+'thumbnail/'+$(this).parents('tr').find('td:eq(1)').text()+'" /></li>');
						}
					})
					$( this ).dialog( "close" );
				}
			}
		});
		
		$("#save_template").click(  function() {
			$( '#template_name' ).val($('#bs_templates option:selected').val());
			$( "#inputBox" ).dialog( "open" );
		})
		
		$('#template_name').focus(function() { 
		  this.select(); 
		});
		
		// Load a template
		$('#bs_templates').change ( function() {
			if ( $('option:selected', $(this))[0].index == 0) return;
			
			$.ajax({
				type : "get",
				cache: false,
				//async: false,
				url: loadTemplate,
				data: {
					_token: csrf_token,
					template_name: $('option:selected',this).text(),
					product_id: product_id
				},
				success: function(response) {
					$('#prodHtml').load(function() { 
						iframe = $('#prodHtml').contents();
						$('#prodtitle',iframe).text($('#title').val()) ;
						$('#elementContainer table').hide().html(response.content).fadeIn('slow');
						$('#price').val(response.price)
						$('#price').attr('title',response.cost)
						$('#descrition',iframe).html(response.description,iframe);
						$('[data-toggle="tooltip"]').tooltip();
						$('#conditions option[value="'+response.condition+'"]').attr('selected','selected')
						if (response.condition==1500)
							$('#conditionDescription').val('Item is new and comes with manufacturer box and papers. Please see description below')
							
						$('#conditions').change();
						$('.mainImage img', iframe).attr('src',response.image);
						$('#production',iframe).html('');
						$('#store_categories option:contains("'+response.StoreCategoryName +'")').attr('selected','selected');

						loadItemSpecificsFromDB();

					});
					
				}
			});			
		})
		
		function loadItemSpecificsFromDB() {
			$.get(loadItemSpecifics, {_token: csrf_token,catId: categoryId,product_id: product_id,itemId: itemId}, function(response) {
				if (response.error) {
					$.alert (response.error)
					return false;
				}
				
				$('#specifics').html(response.specifics);
				$('#ddparent').html(response.ddcategory);
				if (response.title) {
					$('#price').val(response.price);
					$('#title').val(response.title);
					//$('#descrition-input').val(response.description);
					$('#conditions option[value="'+response.condition+'"]').attr('selected','selected')
					$('#store_categories option[value="'+response.StoreCategoryID+'"]').attr('selected','selected')
					description = response.description;
					$(description).filter('table').each( function (e) {
						$(this).find('tr').each( function(e) {
							if (e > 0) {
								id1=$(this).find('td').eq(0).attr('id').replace('_pr','');
								id2=$(this).find('td').eq(1).attr('id').replace('_pr','');
								$('#elementContainer table tr input[name="'+id1+'"').val($(this).find('td').eq(0).text())
								$('#elementContainer table tr input[name="'+id2+'"').val($(this).find('td').eq(1).text())
							}
						})
					})
					$('#prodtitle',iframe).text($('#title').val()) ;
				}
				$('#description',iframe).text($('#description').val()) ;
				updateProductionTable()
			});
		};

		$('#load_template').click ( function() {
			
			$.ajax({
				type : "get",
				cache: false,
				//async: false,
				url: loadOriginalTemplate,
				data: {
					_token: csrf_token,
					template_name: $('#bs_templates :selected').text(),
					product_id: product_id
				},
				success: function(response) {
					$('#elementContainer table').hide().html(response).fadeIn('slow');
				}
			});			
		})

		$('#bs_templates option:eq(1)').prop('selected', 'selected');
		$(":input#bs_templates").trigger('change');
		
		$('#create_store_category').click ( function () {
			category_name = prompt('Enter new store category name');
			if (category_name != null) {
				var data = {
					_token: csrf_token,
					category_name: category_name,
				};

				$.get(setStoreCategories, data, function(response) {
					alert (response);
				});	
			}
		})

		$('#conditions').change ( function () {
			if ( $(':selected', this).val() == "1000" ) {
				$(this).parents('tr').next().hide();
				$(this).parents('tr').next().find('textarea').val('');
			} else $(this).parents('tr').next().show();
		})
		
		// Save a template
		$( "#inputBox" ).dialog({
			buttons: {
				"Save": function() {		
					if ($('#template_name').val() == '') {
						$('#template_name').focus();
						return false;
					}
					
					var newtable = $('#elementContainer table').clone();
					
					$('tr',newtable).each ( function () {
						var inputBox1 = $('td',this).find('input').eq(0)
						var inputBox2 = $('td',this).find('input').eq(1)

						var boxName1 = $(inputBox1).attr('name');
						var boxName2 = $(inputBox2).attr('name');

						if (boxName1.substring(0,1) == 'h' || boxName1.substring(0,1) == 'c') {
							var classname = inputBox1.attr('class');
							$('td:eq(0)',this).find(inputBox1).remove();
							$('td:eq(0)',this).append($('<input />', {
								value: $(inputBox1).val(),
								name: boxName1,
								type: 'text',
								addClass: classname
							}));

							if (boxName2 != undefined && boxName2.substring(0,1) == 't') {
								var classname = inputBox2.attr('class');
								$('td:eq(1)',this).find(inputBox2).remove();
								$('td:eq(1)',this).append($('<input />', {
									value: $(inputBox2).val(),
									name: boxName2,
									type: 'text',
									addClass: classname
								}));
							}
						} 
					})
					
					var data = {
						_token: csrf_token,
					   template_name: $('#template_name').val(),
					   dateinfo: $(newtable).html()
					};
					
					$(newtable).remove();
					$.post(saveTemplate, data, function(response) {
						$( '#inputBox' ).dialog( "close" );
						$('#bs_templates').effect("highlight", {}, 3000);
						if ($('option:contains("'+$('#template_name').val()+'")', '#bs_templates').length == 0) {
							$('#bs_templates').append($("<option/>", {
								text: $('#template_name').val()
							}));
						}
						 $('#bs_templates option:contains("'+$('#template_name').val()+'")').attr('selected', 'selected');
						$('#template_name').val('');
					});						
				},

				Close: function() {
					$( this ).dialog( "close" );
				}
			},
			width: 400,
			modal: true,
			autoOpen: false
		})
	
		function displayMessage (selector, message) {
			$('html, body').animate({
				scrollTop: $(selector).offset().top-300
			}, 500);
			
			$( "#dialog-message" ).html(message);
			
			var offset = $(selector).offset();
				
			$("#dialog-message").stop().animate({top: offset.top-50+'px', left: offset.left+'px'}, 800);
			$("#dialog-message").show();
			$(selector).focus();
			$("#dialog-message").delay(6000).fadeOut(500);
			elementWithFocus=selector;
		}
		
		selectors.hover ( function () {
			if (elementWithFocus != '') {
				if ( $(this)[0] == $(elementWithFocus)[0] ) {
					$("#dialog-message").delay(1000).hide().fadeOut(500);
					elementWithFocus='';
				} 
			}
		})

		$('#submit').click( function (e) {
			e.preventDefault();
						
			if ( categoryId == '' || typeof categoryId == 'undefined' ) {
				displayMessage ('.primary_category','Please select the appropreate category.');
				return false
			} else if ( $('#title').val() == '' ) {
				displayMessage ('#title','You must enter a title for your item.');
				return false
			} else if ( $('.mainImageContainer img', iframe).attr('src') == '' )  {
				displayMessage ('.fileinput-button','You did not select or upload any images.');
				return false
			} else if ( $('#price').val() == '' ) {
				displayMessage ('#price','You must put an initial price.');
				return false
			}
			
			var specifics = [];
			$('#specifics ul.topLevel > li').each ( function () {
				if ($('input',this).val() && typeof $('input',this).val() != 'undefined' )
					if ($('span',this).text()=='')
						specifics.push ( $('input',this).val()+'='+$('input',this).next().next().val() );
					else
						specifics.push ( $('span',this).attr('data-text')+'='+$('input',this).val() );
			})
			
			var data = {
				_token: csrf_token,
				product_id: product_id,
				title: $('#title').val(),
				desc: iframe.find('.htmlFrame').html(),
				price: $('#price').val(),
				catId: categoryId,
				offer: $('input[name=Offer]').is(":checked"),
				//images: images,
				conditionDescription: $('#conditionDescription').val(),
				listingType: $('input[name=ListingType]:checked').val(),
				duration: $('#duration option:selected').val(),
				quantity: $('#quantity').val(),
				reservedPrice: $('#reserved_price').val(),
				condition: $('#conditions option:selected').val(),
				specifics: specifics,
				schedule:  $('#schedule').val(),
				storeCatId: $('#store_categories select option:selected').last().val()
			};
			
			$.post(addItem, data, function(response) {	
				//$('#TB_load').remove();//show loader 
				
				$( "#msgInfo" ).html(response);
				$( ".ui-dialog-titlebar" ).text('New eBay Created');
				$( "#msgInfo" ).dialog( "open" );
				
			});
		})
		
		$('#schedule').datetimepicker({
			numberOfMonths: 2,
			timeFormat: "hh:mm tt",
			minDate: 0,
			maxDate: 30
		});
		
		$( ".ng-cancel").click( function () {
		 	//$('.ng-cancel').click();
			$('.ng-cancel span').text('Cancel Upload');
			$('.thumbcontainer ul li', iframe).remove();
			$('.mainImage img', iframe).attr('src','');
		})
		
		 $( "#msgInfo" ).dialog({
			modal: true,
			width: 400,
			autoOpen: false,
			buttons: {
				Ok: function() {
					$( this ).dialog( "close" );
				}
			}
		});

		$('#getToken').click ( function () {
			if (confirm('This will launch an authorization tool. Are you sure?')) {
								
				$.get(get_eBayToken, function(response) {	
					try {					
						var runame = (response.runame);
						var sessionid = (response.sessionid);
						var production = '';
						if (!FLAG_PRODUCTION) 
							production = 'sandbox.';

						//window.open('https://signin.'+ production + 'ebay.com/ws/eBayISAPI.dll?SignIn&RuName='+runame+'&SessID='+sessionid[0]);
						//window.open('https://signin.ebay.com/authorize?client_id=EdwardBa-dbe1-4a78-8848-5433a7bddb11&redirect_uri='+runame+'&response_type=code');
						window.open('https://signin.ebay.com/ws/eBayISAPI.dll?SignIn&runame=Edward_Babekov-EdwardBa-dbe1-4-wsfvhauew&SessID='+sessionid[0]);
						eBaySessId=sessionid[0];
						exRuname = runame;
					} catch (ex) {
						alert (response);
					}
				});
			}
		})

		$('#fetchToken').click ( function () {
			if (!eBaySessId) {
				alert ('You must launch a consent form first.')
				return false;
			}
			
			var data = {
				_token: csrf_token,
			   sessionid: eBaySessId,
			   runame: exRuname
			};
			
			$.get(fetchToken, data, function(response) {	
				if (response.error != 'error') {
					$('#fetchToken').attr('disabled','disabled');
					$('#getToken').attr('disabled','disabled');

				}
				$( "#msgInfo" ).html(response.message);
				$( ".ui-dialog-titlebar" ).text('Fetch Token');
				$( "#msgInfo" ).dialog( "open" );
			});
		
		})
		
		$(document).on('input propertychange', '.editCombo', function() {				
			var searchedData = $(this).val();
			$('#dropDown').find('li').each( function () {
				m = $(this).text()
				m=m.substr(0,searchedData.length)
				if (m.toUpperCase() != searchedData.toUpperCase()) {
					$(this).hide();
				} else {
					$(this).show();
				}

			})
		})
		
		$('#itemLoadTemplate').click ( function (e) {
			var data = {
				catId: categoryId
			};

			$.get(itemLoadTemplate, data, function(response) {	
				$('#specifics input').each(function(i) {
					$(this).val(response[$(this).attr('data-id')]);
				})
				debugger
			});
		})

		$('#itemSpecifics').click ( function (e) {
			if (typeof categoryId == 'undefined') {
				alert ('Please load the primary cateagory first and try again.')
				return false;
			}
			e.preventDefault();
			var data = {
				_token: csrf_token,
			   catId: categoryId
			};
			
			$.get(GetItemSpecifics, data, function(response) {	
				$('#specifics').html(response);
			});
		})

		var ddselected = null

		function hideDdCategory() {
			if ($('#ddparent').css('display')=='block')
				$('#ddparent').hide()
			else $('#ddparent').show()
		}

		$(document).on('click','.txtContainer button', function() {
			_this = $(this);
			$('#ddparent').css({
				top: $(_this).offset().top+34,
					left: $(_this).parent().find('input').offset().left-$('#specifics').offset().left+34
			})
			ddselected = $(this);
			hideDdCategory();
		})

		$(document).on('click','#ddcategory .dditem', function() {
			$(ddselected).parent().find('input').val('%'+$(this).text()+'%')
			$('#ddparent').hide()
		})

		$('#itemSaveSpecifics').click( function() {
			var els = new Array;

			$('#specifics ul li input[type="text"]').each( function(a,m) {
				els.push([$(m).val(),$(m).attr('data-id')])
			})

			var data = {
				_token: csrf_token,
			   els: els
			};

			$.post(linkSpecifics, data, function(response) {	
				debugger
			});
			
		})

		$('#itemSpecificsFromURL').click ( function (e) {
			if (typeof categoryId == 'undefined') {
				alert ('Please load the primary cateagory first and try again.')
				return false;
			}
			e.preventDefault();
			var url = prompt('Please write or paste URL here and press Enter. Put item id if it\'s from eBay.');
			if (!url) {
				alert ('You left URL path blank. In order for you to pull the information out of the web, you must enter the URL');
				return false;
			}

			var data = {
				_token: csrf_token,
				catId: categoryId,
			   	url: url
			};
			
			$.get(GetItemSpecificsURL, data, function(response) {	
				$('#specifics').html(response);
			});
		})

		$('#deleteTemplate').click ( function () {
			if ( $(' option:selected', '#settTemplates')[0].index == 0) return;
			if (!confirm ('This will permenantly delete the current template. Are you sure you want to do this?'))	return false;
			
			var data = {
				_token: csrf_token,
			   template_name: $('option:selected', '#settTemplates').val()
			};
			
			$.post(deleteTemplate, data, function(response) {	
				var selectedIndex = $('option:selected', '#settTemplates')[0].index;
				$('option:selected', '#settTemplates').remove();
				$('option:eq('+selectedIndex+')', '#bs_templates').remove();
			});
		})
		
		$('#specifics').on("focus", "input", function (e) {
			e.stopPropagation();
			ddselected = $(this);
			$('#dropDown').hide();
			var data = {
				_token: csrf_token,
			   id: $(this).attr('data-id')
			};
			_this = $(this);

			$.get(getSpecificForCategory, data, function(response) {	
				$('#dropDown').css({
					top: $(_this).offset().top+34,
					left: $(_this).parent().find('input').offset().left-$('#specifics').offset().left+34
				})
				$('#dropDown').html(response)
				$('#dropDown').show();
				//$(_this).next('div').show();
			})
		})
		
		$(document).click( function (e) {
			if (e.target.tagName=='INPUT') return
			if (e.target.tagName=='BUTTON') return

			if (document.activeElement.className == 'editCombo' || document.activeElement.className == 'comboOption') {
				return false;
			}
			$('#dropDown').hide()
			$('#ddparent').hide()
		})
		
		$(document).on("click", '.comboOption', function (e) {
			e.preventDefault();
			
			setTimeout ( function () {
				$('#dropDown').hide()
			},50)

			prev = $(ddselected).val();
			activeCombo = $(this).text();
			if(e.ctrlKey)
				$(ddselected).val(activeCombo+','+prev);
			else $(ddselected).val(activeCombo);
		})
		
		$('#settings').click ( function (e) {
			e.preventDefault();
			$("#settTemplates option").remove();
			$('#bs_templates option').each ( function() {
				$("#settTemplates").append($("<option/>", {
						text: $(this).text()
					})
				)
			})
			
			$.ajax({
				type : "get",
				cache: false,
				//async: false,
				url: getSettings,
				
				success: function(response) {
					if (response) {					
						var shiping = response['shipping'];
						var shiping1 = response['shipping1'];
						var shiping2 = response['shipping2'];
						var return_details = response['return_details'];
						var return_days = response['return_days'];
						var restocking_fee = response['restocking_fee'];
						var handle_time = response['handle_time'];
						var paypal_email = response['paypal_email'];
						var sales_tax = response['sales_tax'];
						var state_sales_tax = response['state_sales_tax'];
						has_store = response['has_store'];
						
						$('input[name=shipping]').val(shiping);
						$('input[name=shipping1]').val(shiping1);
						$('input[name=shipping2]').val(shiping2);
						$('textarea[name=return_details]').val(return_details);
						$('input[name=return_days]').val(return_days);
						$('#restocking').val(restocking_fee)
						$('input[name=handle_time]').val(handle_time);
						$('input[name=paypal_email]').val(paypal_email);
						$('input[name=sales_tax]').val(sales_tax);
						$('input[name=state_sales_tax]').val(state_sales_tax);
						$('input[name=has_store]').prop("checked", has_store == 1 ? true : false)

					}
				}
			});				
			$( "#settingsDialog" ).dialog( "open" );
		})
		
		$( "#settingsDialog" ).dialog({
			buttons: {
				"Save": function() {		
					var data = {
						_token: csrf_token,
					   datainfo: $('#formSettings').serialize()
					};
					$.post(saveSettings, data, function(response) {
						$( '#settingsDialog' ).dialog( "close" );
						if ($('input[name=has_store]').prop("checked")==true) {

							if ($(".store_category_container").children().length==0) {
								$(".store_category_container").append('<br>Store Category:<br><div id="store_categories" style="margin-left: 6px"><select size="8"></select></div></div>');
								getStoreCategories(0);
							}
						} else
							$(".store_category_container").empty();

						alert (response);
					});						
				},

				Close: function() {
					$( this ).dialog( "close" );
				}
			},
			width: 600,
			modal: true,
			autoOpen: false
		})
		
		$('#categories').on('change', '.primary_category', function () {
			if ( $(this).next().length > 0 ) {
				$(this).nextAll().remove();
				categories = [];
				$('#categories .primary_category').each ( function () {
					categories.push ($('option:selected',this).val())
				})
				categoryId = '';
			} 

			categoryId = $('option:selected',this).val();
			var data = {
				_token: csrf_token,
			   catId: categoryId
			};
			
			$.get(getCategoryNode, data, function(response) {	
				if ($.inArray(categoryId, categories) == -1) categories.push (categoryId);
				// if (response.indexOf ('ErrorCode')!=-1) {
				// 	alert (response);
				// } else 
				if ( response.conditions!=undefined) {
					//$.cookie('the_category', categories, { expires: 1 });
					localStorage.setItem("the_category", categories);
					$('#conditions').children().remove();
					$('#conditions').append(response['conditions']);
					loadItemSpecificsFromDB();
				} else {
					categoryId = '';
					$('#categories').append(response);
				}
			});		
		})

		if (window.location.href.indexOf('edit')==-1) {
			if ( localStorage.getItem('the_category') ) {
				var cats = localStorage.getItem('the_category');
				cats = cats.toString().split(',');
				var lastSelection = 0;
				
				$.each(cats, function( index, value ) {			
					$.ajax({
						type : "get",
						cache: false,
						async: false,
						url: getCategoryNode,
						data: {
							_token: csrf_token,
							catId: value
						},
						success: function(response) {
							if ( typeof response == 'object') {
								$('#conditions').children().remove();
								$('#conditions').append(response['conditions']);
								lastSelection = value;
							} else {						
								$('#categories').append(response);
								$('#category_'+value).prev().val(value);
							}
						}
					})
					
				});
				$('.primary_category').last().val(lastSelection);
				categoryId = $('.primary_category option:selected').last().val();
				
			}
		} else {
			categoryId = $('.primary_category option:selected').last().val();
		}
		// 718-663-9454 misha
		// Like new 3 months used for man.
		
		$('#update_store_categories').click( function () {
			if (confirm('Are you sure you want to update all the store categories?'))
				getStoreCategories(1)
		})

		if (has_store) getStoreCategories(0);
		
		function getStoreCategories(refresh) {
			$.ajax({
				type : "get",
				cache: false,
				//async: false,
				url: storeCategories,
				data: {
					_token: csrf_token,
					refresh: refresh
				},
				success: function(response) {
					$("#store_categories select").html(response);
				}
			})		
			
		}
		
		$("#Chinese").change ( function () {
			if ( $("#Chinese").is(':checked')  ) {
				$("#reserved_price").removeAttr("disabled");
				$("#reserved_price").removeClass("reserved_price_disabled");
			}
		})

		var client = new ZeroClipboard( $("#generateHtml") );
		var times;
		client.on( "ready", function( readyEvent ) {
		  //alert( "ZeroClipboard SWF is ready!" );

		  client.on( "copy", function( event ) {
			
			var txt = $(".htmlFrame",iframe).html()
			event.clipboardData.setData('text/plain', txt);
			times = 0;
			client.on( 'aftercopy', function(event) {
			  if (times > 0) return
			  alert("Copied text to clipboard");
			  times++;
			} );
		  } );
		} );
		
		//Init();
		function Init() {
			clip = new ZeroClipboard.Client();
			clip.glue( 'generateHtml' );
			
			var txt = $(".htmlFrame",iframe).html()
			clip.setText(txt);
			clip.addEventListener('complete', function(client, text) {
				alert("Copied text to clipboard");
			});
		}
		
		$("#FixedPriceItem").change ( function () {
			if ( $("#FixedPriceItem").is(':checked')  ) {
				$("#reserved_price").attr("disabled","disabled");
				$("#reserved_price").addClass("reserved_price_disabled");
				$("#reserved_price").val('');
			}
		})
				
		// $('#prodtitle',iframe).text($('#title').val()) ;
		// $('#description-input',iframe).html($('#description').val());

		// Copy Description when browser refresh initiated //
		//var desc = $('#description').val().replace(/\r?\n/g, "<br>\n");
		
		//$('#description',iframe).html(desc) ;
		
		// Determine if line below the description is visible or not //
		if ($('#description').val()) {
			$('.descriptionContainer',iframe).show();
		} else $('.descriptionContainer',iframe).hide();
			
		$('#title').on('input propertychange', function() {
			$('#prodtitle',iframe).text($('#title').val()) 
		})
		
		// place text in iframe's description when writing a descrition text  //
		$('#description').on('input propertychange', function() {
			// Determine if line below the description is visible or not //
			var desc = $('#description').val().replace(/\r?\n/g, "<br>\n");
			//var $text = '';
			//$text = desc.replace(/\*+(.*)?/i,"<ul><li>$1</li></ul>",$text);
			//desc = $text.replace(/(\<\/ul\>\n(.*)\<ul\>*)+/,"",$text);
	
			$('#description',iframe).html(desc);
			if ($('#description').val()) {
				$('.descriptionContainer',iframe).show();
			} else $('.descriptionContainer',iframe).hide();
		})
		
		//updateProductionTable() ;
		function updateProductionTable () {
			$('#the-form table tr').each ( function () {
				var elementName =  $(this).find('td').find('input').attr('name');
				var elementID =  elementName+'_pr';
				
				if (elementName.substring(0,1) == 'h')
					$('#production',iframe).append ( '<tr><td colspan="2" id="'+elementID+'" class="head">' + $(this).find('input').val() + '</td></tr>');
				else if (elementName.substring(0,1) == 'c') {
					var nextInput = $('td',this).next().find('input')
					var boldText = nextInput.css("font-weight") == "700" ? "style='font-weight: 700'" : "";
					if ( nextInput.val().indexOf('%')==-1)
						$('#production',iframe).append ( '<tr><td id="'+elementID+'" class="contents" '+boldText+'>' + $(this).find('input').val()+'</td><td id="'+nextInput.attr('name')+'_pr" class="contents">' + nextInput.val()+'</td></tr>');
				}	
			})
			if ( $("#Chinese").is(':checked')  ) {
				$("#reserved_price").removeAttr("disabled");
				$("#reserved_price").removeClass("reserved_price_disabled");
				}
			else {
				$("#reserved_price").attr("disabled","disabled");
				$("#reserved_price").addClass("reserved_price_disabled");
				$("#reserved_price").val('');
			}
			
		}
		
	})