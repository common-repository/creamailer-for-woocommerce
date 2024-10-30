//cfw-plugin-autoresponders-field
window.onload = function () {
	function sanitycheck_for_autoresponders () {
		if (document.getElementById('cfw-plugin-send-autoresponders-field').checked) {
			document.getElementById('cfw-plugin-send-autoresponders-if-exists-field').disabled = false;
		} else {
			document.getElementById('cfw-plugin-send-autoresponders-if-exists-field').checked = false;
			document.getElementById('cfw-plugin-send-autoresponders-if-exists-field').disabled = true;
		}

	}

	document.getElementById('cfw-plugin-send-autoresponders-field').addEventListener('change', (event) => {
		sanitycheck_for_autoresponders();
	});

	document.getElementById('cfw-plugin-send-autoresponders-text').addEventListener('click', (event) => {
		document.getElementById('cfw-plugin-send-autoresponders-field').checked = !document.getElementById('cfw-plugin-send-autoresponders-field').checked;
		sanitycheck_for_autoresponders();
	});

	document.getElementById('cfw-plugin-send-autoresponders-if-exists-text').addEventListener('click', (event) => {
		if (document.getElementById('cfw-plugin-send-autoresponders-field').checked) {
			document.getElementById('cfw-plugin-send-autoresponders-if-exists-field').checked =  !document.getElementById('cfw-plugin-send-autoresponders-if-exists-field').checked;
		}
	});

	document.getElementById('cfw-plugin-error-log-text').addEventListener('click', (event) => {
		document.getElementById('cfw-plugin-error-log-field').checked = !document.getElementById('cfw-plugin-error-log-field').checked;
	});


	sanitycheck_for_autoresponders();
}
