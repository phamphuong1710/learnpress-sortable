;( function ($) {
	'use strict';

	$( window ).on(
		'load',
		function() {
			// $(".loader_boostify").delay(0).fadeOut("slow");
			// $("#overlayer").delay(0).fadeOut("slow");
			// var sortable = $( '.question-item.sortable' );
			// var tbody = sortable.find( '.ui-sortable' );
			// sortable.each( function( index ) {
			// 	var item       = $(this);
			// 	var questionId = item.attr( 'data-item-id' );
			// 	var tbody = item.find( '.ui-sortable' );
			// 	console.log( tbody );
			// 	var data       = {
			// 		action: 'htc_all_answer_option',
			// 		_ajax_nonce: admin.nonce,
			// 		question_id: questionId,
			// 	};
			// 	$.ajax(
			// 		{
			// 			type: 'GET',
			// 			url: admin.url,
			// 			data: data,
			// 			beforeSend: function (response) {
			// 			},
			// 			success: function (response) {
			// 				tbody.html( response );
			// 			},
			// 		}
			// 	);
			// } );

			var sortItem = $( document ).find( 'tbody.ui-sortable' );
			$( 'body tbody.ui-sortable' ).sortable(
				{
					cursor: "move",
					update: function(event, ui) {
						var result = $(this).sortable('toArray', {attribute: 'data-postion'});
						var item = $(this).find( 'tr' );
						$.each(result, function(index, value){
							item.each( function() {
								if ( $(this).attr('data-postion') == value ) {
									$(this).find('.order').html(index + 1);
								}
							} );
						});

						item.each( function( index ) {
							var option = $(this);
							var questionId = option.attr( 'data-answer-id' );
							var postion = option.find( '.order' ).html();
							option.attr('data-postion', postion);
							var data       = {
								action: 'htc_update_answer_position',
								_ajax_nonce: admin.nonce,
								answer_id: questionId,
								postion: postion,
							};
							$.ajax(
								{
									type: 'POST',
									url: admin.url,
									data: data,
									beforeSend: function (response) {
									},
									success: function (response) {
										
									},
								}
							);
						} );
					}
				}
			);
		}
	);


	$(document).on(
		'click',
		'.add-sortable-question-option-button',
		function(e) {
			var btn = $(this);
			var question = btn.parents( '.question-item' );
			var questionId = question.attr( 'data-item-id' );
			if ( question.length == 0 ) {
				questionId = $('input[name=post_ID]').val();
			} else {
				questionId = question.attr( 'data-item-id' );
			}
			var editer = btn.parents('.admin-quiz-sortable-question-editor');
			var table = editer.find( 'tbody' );
			var number = table.find('tr').length;
			var n = parseInt(number) + 1;
			console.log( questionId );
			var data     = {
				action: 'htc_new_answer_option',
				_ajax_nonce: admin.nonce,
				index: number,
				question_id: questionId,
			};
			$.ajax(
				{
					type: 'GET',
					url: admin.url,
					data: data,
					beforeSend: function (response) {
						console.log( response );
						

					},
					success: function (response) {
						table.append( response );
					},
				}
			);
		}
	);

	$(document).on(
		'click',
		'.admin-sortable-question-editor .remove-answer',
		function(e) {
			alert('msg');
			var btn = $(this);
			var question = btn.parents( '.question-item' );
			var questionId = question.attr( 'data-item-id' );
			if ( questionId == 'undefined' ) {
				questionId = $('input[name=post_ID]').val();
			}
			if ( question.length == 0 ) {
				questionId = $('input[name=post_ID]').val();
			} else {
				questionId = question.attr( 'data-item-id' );
			}
			var answer   = btn.parents( '.answer-option' );
			var answerId = answer.attr( 'data-answer-id' );

			var sort     = answer.find( '.lp-sortable-handle' );
			var data     = {
				action: 'htc_delete_answer_option',
				_ajax_nonce: admin.nonce,
				answer_id: answerId,
				question_id: questionId,
			};
			$.ajax(
				{
					type: 'POST',
					url: admin.url,
					data: data,
					beforeSend: function (response) {
						sort.addClass('delete');
					},
					success: function (response) {
						sort.removeClass('delete');
						answer.remove();
						var tr = question.find('tbody.ui-sortable').find('tr');
						tr.each( function( index ) {
							$(this).find('.order').html( index + 1 );
						} )
					},
				}
			);
		}
	);

	$(document).on(
		'keyup',
		'.admin-sortable-question-editor input[type=text]',
		function() {
			var input = $(this);
			var answer = input.parents( '.answer-option' ).attr( 'data-answer-id' );
			var parents = input.parents( '.answer-text' ).siblings( '.answer-text' );
			var nextInput = parents.find( 'input[type=text]' );
			var label, sort;
			console.log( input );
			console.log( nextInput );
			if ( input.hasClass( 'label' ) ) {
				label = input.val();
				sort = nextInput.val();
			} else {
				sort = input.val();
				label = nextInput.val();
			}
			var data     = {
				action: 'htc_update_answer_label',
				_ajax_nonce: admin.nonce,
				answer_id: answer,
				data_label: label,
				data_sort: sort,
			};
			$.ajax(
				{
					type: 'POST',
					url: admin.url,
					data: data,
					beforeSend: function (response) {
					},
					success: function (response) {
					},
				}
			);
		}
	);




} )( jQuery );