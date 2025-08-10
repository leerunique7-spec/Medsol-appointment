document.addEventListener('DOMContentLoaded', () => {
	const nonce = medsolAppointments.nonce;
	const ajaxUrl = medsolAppointments.ajax_url;

	// Function to open off-canvas with content
	const openModal = (html) => {
		const modal = document.getElementById('medsol-modal');
		modal.innerHTML = html;
		modal.style.display = 'block';
		document.body.classList.add('medsol-off-canvas-open');

		// Tab switching
		const tabs = modal.querySelectorAll('.nav-tab');
		tabs.forEach(tab => {
			tab.addEventListener('click', (e) => {
				e.preventDefault();
				tabs.forEach(t => t.classList.remove('nav-tab-active'));
				tab.classList.add('nav-tab-active');
				modal.querySelectorAll('.tab-content').forEach(content => content.style.display = 'none');
				document.querySelector(tab.getAttribute('href')).style.display = 'block';
			});
		});

		// Add day off
		const addDayOffBtn = modal.querySelector('.add-day-off');
		if (addDayOffBtn) {
			let dayIndex = modal.querySelectorAll('.day-off-row').length; // Start index from existing rows
			addDayOffBtn.addEventListener('click', () => {
				const list = modal.querySelector('.days-off-list');
				const row = document.createElement('div');
				row.classList.add('day-off-row');
				row.innerHTML = `
					<input type="text" name="days_off[${dayIndex}][reason]" placeholder="Reason">
					<input type="date" name="days_off[${dayIndex}][start_date]">
					<input type="date" name="days_off[${dayIndex}][end_date]">
					<button type="button" class="button remove-day-off">Remove</button>
				`;
				list.appendChild(row);
				dayIndex++;
				attachRemoveListeners();
			});
		}

		// Remove day off
		const attachRemoveListeners = () => {
			modal.querySelectorAll('.remove-day-off').forEach(btn => {
				btn.addEventListener('click', (e) => {
					const row = e.target.closest('.day-off-row');
					const dayOffId = btn.dataset.dayOffId;
					if (dayOffId) {
						// AJAX delete
						fetch(ajaxUrl, {
							method: 'POST',
							headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
							body: `action=medsol_delete_${modal.id.includes('employee') ? 'employee' : 'location'}_day_off&nonce=${nonce}&day_off_id=${dayOffId}`
						}).then(res => res.json()).then(data => {
							if (data.success) row.remove();
						});
					} else {
						row.remove();
					}
				});
			});
		};
		attachRemoveListeners();

		// Save button
		const saveBtn = modal.querySelector('.medsol-save');
		if (saveBtn) {
			saveBtn.addEventListener('click', () => {
				const form = modal.querySelector('form');
				if (!form) {
					alert('Error: Form not found in modal');
					return;
				}

				let entity = '';
				if (typeof form.id === 'string' && form.id.startsWith('medsol-') && form.id.endsWith('-form')) {
					entity = form.id.replace('medsol-', '').replace('-form', '');
				} else {
					// Fallback: Infer from active button class (e.g., if modal opened from .add-service)
					const activeBtnClass = document.querySelector('.add-appointment, .add-employee, .add-service, .add-location')?.classList[1]?.replace('add-', '');
					if (activeBtnClass) {
						entity = activeBtnClass;
					} else {
						alert('Error: Invalid form ID - contact developer with console logs.');
						return;
					}
				}

				const formData = new FormData(form);
				const params = new URLSearchParams();
				for (let [key, value] of formData) {
					params.append(key, value);
				}

				params.append('action', `medsol_save_${entity}`);
				params.append('nonce', nonce);

				fetch(ajaxUrl, {
					method: 'POST',
					body: params
				}).then(res => res.json()).then(data => {
					if (data.success) {
						location.reload(); // Reload to update table
					} else {
						alert(data.data || 'Error saving - check required fields or console.');
					}
				}).catch(err => {
					alert('Network error: ' + err.message);
				});
			});
		}

		// Cancel button
		modal.querySelector('.medsol-cancel').addEventListener('click', closeModal);
	};

	// Close modal
	const closeModal = () => {
		const modal = document.getElementById('medsol-modal');
		modal.innerHTML = '';
		modal.style.display = 'none';
		document.body.classList.remove('medsol-off-canvas-open');
	};

	// Add/Edit buttons for appointments
	document.querySelectorAll('.add-appointment, .edit-appointment').forEach(btn => {
		btn.addEventListener('click', (e) => {
			const id = e.target.dataset.id || 0;
			fetch(ajaxUrl, {
				method: 'POST',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				body: `action=medsol_get_appointment_modal&nonce=${nonce}&id=${id}`
			}).then(res => res.json()).then(data => {
				if (data.success) openModal(data.data.html);
			});
		});
	});

	// Similar for employees
	document.querySelectorAll('.add-employee, .edit-employee').forEach(btn => {
		btn.addEventListener('click', (e) => {
			const id = e.target.dataset.id || 0;
			fetch(ajaxUrl, {
				method: 'POST',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				body: `action=medsol_get_employee_modal&nonce=${nonce}&id=${id}`
			}).then(res => res.json()).then(data => {
				if (data.success) openModal(data.data.html);
			});
		});
	});

	// For services
	document.querySelectorAll('.add-service, .edit-service').forEach(btn => {
		btn.addEventListener('click', (e) => {
			const id = e.target.dataset.id || 0;
			fetch(ajaxUrl, {
				method: 'POST',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				body: `action=medsol_get_service_modal&nonce=${nonce}&id=${id}`
			}).then(res => res.json()).then(data => {
				if (data.success) openModal(data.data.html);
			});
		});
	});

	// For locations
	document.querySelectorAll('.add-location, .edit-location').forEach(btn => {
		btn.addEventListener('click', (e) => {
			const id = e.target.dataset.id || 0;
			fetch(ajaxUrl, {
				method: 'POST',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				body: `action=medsol_get_location_modal&nonce=${nonce}&id=${id}`
			}).then(res => res.json()).then(data => {
				if (data.success) openModal(data.data.html);
			});
		});
	});
});