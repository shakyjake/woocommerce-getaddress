
function getaddressio_select(event){
	const select = event.target;
	select.classList.add('address__select--loading');
	const root = select.parentNode.parentNode;
	const address_type = select.parentNode.id.replace(/([a-z]+)\_address\_search\_field/gi, '$1');
	const url = wcgaio.ajax_url;
	const post_params = [];
	post_params.push('action=' + encodeURIComponent('wcgaio_address_details'));
	post_params.push('token=' + encodeURIComponent(wcgaio.detail_token));
	post_params.push('address_id=' + encodeURIComponent(select.value));
		
	const old_err = root.querySelector('.address__error');
	if(old_err){
		old_err.remove();
	}
	
	if(select.value.length){
	
		fetch(url, {
			method: 'POST',
			mode: 'same-origin',
			cache: 'no-cache',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			redirect: 'follow',
			referrerPolicy: 'no-referrer',
			body: post_params.join('&')
		})
		.then((response) => {
			if(!response.ok){
				throw new Error(response.status);
			}
			return response.json();
		})
		.then((data) => {

			select.classList.remove('address__select--loading');

			const address_line1 = root.querySelector('#' + address_type + '_address_1');
			if(address_line1){
				address_line1.value = data.line_1;
			}
			const address_line2 = root.querySelector('#' + address_type + '_address_2');

			const line_2 = [];
			if(data.line_2.length){
				line_2.push(data.line_2);
			}
			if(data.line_3.length){
				line_2.push(data.line_3);
			}
			if(data.line_4.length){
				line_2.push(data.line_4);
			}

			if(line_2.length){
				/* clients love to disable the second line field then ask why part of the address is missing */
				if(address_line2){
					address_line2.value = line_2.join(', ');
				} else {
					address_line1.value += ', ' + line_2.join(', ');
				}
			} else if(address_line2){
				address_line2.value = '';
			}


			const address_city = root.querySelector('#' + address_type + '_city');
			if(address_city){
				address_city.value = data.town_or_city;
			}

			const address_state = root.querySelector('#' + address_type + '_state');
			if(address_state){
				address_state.value = data.county;
			}

			const address_postcode = root.querySelector('#' + address_type + '_postcode');
			if(address_postcode){
				address_postcode.value = data.postcode;
			}
		
			getaddressio_show_fields(address_type);


		})
		.catch((err_detail) => {

			select.classList.remove('address__select--loading');
		
			getaddressio_show_fields(address_type);

		});
	} else {

		select.classList.remove('address__select--loading');
		
		getaddressio_show_fields(address_type);
	}
}

function getaddressio_manual(event){
	
	event.stopImmediatePropagation();
	
	const btn = event.target;
	const root = btn.parentNode;
	const address_type = root.id.replace(/([a-z]+)\_address\_search\_field/gi, '$1');
	getaddressio_show_fields(address_type);

}

function getaddressio_search(event){
	
	event.stopImmediatePropagation();
	
	const btn = event.target;
	const root = btn.parentNode;
	const address_type = root.id.replace(/([a-z]+)\_address\_search\_field/gi, '$1');
	const input = root.querySelector('.input-text');
	const url = wcgaio.ajax_url;
	const post_params = [];
	post_params.push('action=' + encodeURIComponent('wcgaio_address_search'));
	post_params.push('token=' + encodeURIComponent(wcgaio.search_token));
	post_params.push('search=' + encodeURIComponent(input.value));
	while(btn.childNodes.length){
		btn.firstChild.remove();
	}
	btn.append('Searching');
	btn.disabled = true;
		
	const old_err = root.querySelector('.address__error');
	if(old_err){
		old_err.remove();
	}
	
	if(input.value.length){
	
		fetch(url, {
			method: 'POST',
			mode: 'same-origin',
			cache: 'no-cache',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			redirect: 'follow',
			referrerPolicy: 'no-referrer',
			body: post_params.join('&')
		})
		.then((response) => {
			if(!response.ok){
				throw new Error(response.status);
			}
			return response.json();
		})
		.then((data) => {

			let select = root.querySelector('.address__select');

			if(!('suggestions' in data)){
				data = {"suggestions" : []};
			}

			if(data.suggestions.length){
				if(!select){
					select = document.createElement('select');
					select.className = 'address__select';
					select.addEventListener('change', getaddressio_select);
					root.append(select);
				}
				while(select.childNodes.length){
					select.firstChild.remove();
				}

				const default_option = document.createElement('option');
				default_option.value = '';
				default_option.append('Select your address');
				default_option.selected = true;
				default_option.disabled = true;
				select.append(default_option);

				data.suggestions.forEach((address_data) => {
					const option = document.createElement('option');
					option.value = address_data.id;
					option.append(address_data.address);
					select.append(option);
				});

				while(btn.childNodes.length){
					btn.firstChild.remove();
				}
				btn.append('Search');
				btn.disabled = false;

			} else {

				if(select){
					select.remove();
				}

				const err = document.createElement('div');
				err.className = 'address__error';
				err.append('Error: no addresses found. Please enter your address manually below');
				root.append(err);

				while(btn.childNodes.length){
					btn.firstChild.remove();
				}
				btn.append('Search');
				btn.disabled = false;
		
				getaddressio_show_fields(address_type);

			}
		
		})
		.catch((err_detail) => {

			const err = document.createElement('div');
			err.className = 'address__error';
			err.append('Error: unable to obtain full address details. Please enter your address manually below');
			root.append(err);

			while(btn.childNodes.length){
				btn.firstChild.remove();
			}
			btn.append('Search');
			btn.disabled = false;
		
			getaddressio_show_fields(address_type);

		});
			
	} else {

		while(btn.childNodes.length){
			btn.firstChild.remove();
		}
		btn.append('Search');
		btn.disabled = false;
		
		getaddressio_show_fields(address_type);
		
	}
}

function getaddressio_show_fields(address_type){
	if(typeof(jQuery) === 'function'){
		(($) => {
			$('#' + address_type + '_address_1_field').slideDown(200);
			$('#' + address_type + '_address_2_field').slideDown(200);
			$('#' + address_type + '_city_field').slideDown(200);
			$('#' + address_type + '_state_field').slideDown(200);
			$('#' + address_type + '_postcode_field').slideDown(200);
		})(jQuery);
	} else {
		getaddressio_show_node('#' + address_type + '_address_1_field');
		getaddressio_show_node('#' + address_type + '_address_2_field');
		getaddressio_show_node('#' + address_type + '_city_field');
		getaddressio_show_node('#' + address_type + '_state_field');
		getaddressio_show_node('#' + address_type + '_postcode_field');
	}
}

function getaddressio_hide_fields(address_type){
	if(typeof(jQuery) === 'function'){
		(($) => {
			$('#' + address_type + '_address_1_field').slideUp(200);
			$('#' + address_type + '_address_2_field').slideUp(200);
			$('#' + address_type + '_city_field').slideUp(200);
			$('#' + address_type + '_state_field').slideUp(200);
			$('#' + address_type + '_postcode_field').slideUp(200);
		})(jQuery);
	} else {
		getaddressio_show_node('#' + address_type + '_address_1_field');
		getaddressio_show_node('#' + address_type + '_address_2_field');
		getaddressio_show_node('#' + address_type + '_city_field');
		getaddressio_show_node('#' + address_type + '_state_field');
		getaddressio_show_node('#' + address_type + '_postcode_field');
	}
}

function getaddressio_show_node(selector){
	const nodes = document.querySelectorAll(selector);
	nodes.forEach((node) => {
		node.style.display = 'block';
	});
}

function getaddressio_hide_node(selector){
	const nodes = document.querySelectorAll(selector);
	nodes.forEach((node) => {
		node.style.display = 'none';
	});
}

function getaddressio_search_toggle(address_type){

	const input_fields = [
		address_type + '_address_1',
		address_type + '_address_2',
		address_type + '_city',
		address_type + '_state',
		address_type + '_postcode'
	];

	let show_input_fields = true;
	let show_search = false;
	let country = 'GB';

	const country_input = document.getElementById(address_type + '_country');
	if(country_input){
		country = country_input.value.toUpperCase();
	}

	if(country === 'GB'){
		show_search = true;
		show_input_fields = false;
	}

	input_fields.forEach((id) => {
		const field = document.getElementById(id);
		if(field){
			show_input_fields = show_input_fields || !!field.value.length;
		}
	});

	input_fields.forEach((id) => {
		const field = document.getElementById(id);
		if(field){
			let holder = field;
			while(!holder.classList.contains('form-row')){
				holder = holder.parentNode;
				if(holder.nodeName.toLowerCase() === 'body'){
					holder = null;
					break;
				}
			}
			if(holder){
				holder.style.display = show_input_fields ? 'block' : 'none';
			}
		}
	});

	const search_field = document.getElementById(address_type + '_address_search_field');
	if(search_field){
		search_field.style.display = show_search ? 'flex' : 'none';
	}

}

function getaddressio_search_toggle_all(){
	getaddressio_search_toggle('billing');
	getaddressio_search_toggle('shipping');
}

function getaddressio_search_toggle_evt(event){
	const input = event.target;
	const address_type = input.id.replace('_country', '');
	getaddressio_search_toggle(address_type);
}

function getaddressio_load(){

	(($) => {
		$('#billing_country').on('change', getaddressio_search_toggle_evt);
		$('#shipping_country').on('change', getaddressio_search_toggle_evt);
		$(window).on('hosted_fields_loaded', getaddressio_search_toggle_all);
	})(jQuery);

	getaddressio_search_toggle('billing');
	getaddressio_search_toggle('shipping');

}

function getaddressio_init(){

	getaddressio_search_toggle_all();

	const searches = document.querySelectorAll('.address__search');
	searches.forEach((search) => {

		const btn = document.createElement('button');
		btn.className = 'address__button address__button--search';
		btn.append('Search');
		btn.type = 'button';
		btn.addEventListener('click', getaddressio_search);
		btn.addEventListener('tap', getaddressio_search);
		search.append(btn);

		if(wcgaio.show_manual_btn === 'true'){/* wp_localize script erroneously converts everything to a string */
			const btn_manual = document.createElement('button');
			btn_manual.className = 'address__button address__button--manual';
			btn_manual.append('Enter Address Manually');
			btn_manual.type = 'button';
			btn_manual.addEventListener('click', getaddressio_manual);
			btn_manual.addEventListener('tap', getaddressio_manual);
			search.append(btn_manual);
		}

	});
	
}

(() => {

	window.addEventListener('load', getaddressio_load);
	
	getaddressio_init();
	
})();
