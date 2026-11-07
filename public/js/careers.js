(() => {
  const form = document.querySelector('[data-careers-form]');
  if (!form) return;

  const resumeInput = form.querySelector('#resume');
  const resumeName = form.querySelector('[data-resume-name]');
  const resumeBtn = form.querySelector('[data-resume-trigger]');
  const statusBox = form.querySelector('[data-careers-status]');
  const submitBtn = form.querySelector('[data-careers-submit]');

  if (resumeBtn && resumeInput) {
    resumeBtn.addEventListener('click', () => resumeInput.click());
  }

  if (resumeInput && resumeName) {
    resumeInput.addEventListener('change', () => {
      const file = resumeInput.files && resumeInput.files[0];
      resumeName.textContent = file ? file.name : '';
      resumeInput.classList.toggle('is-invalid', false);
    });
  }

  const clearErrors = () => {
    form.querySelectorAll('.is-invalid').forEach((el) => el.classList.remove('is-invalid'));
    form.querySelectorAll('[data-field-error]').forEach((el) => {
      el.textContent = '';
      el.hidden = true;
    });
  };

  const setFieldError = (name, message) => {
    const field = form.querySelector(`[name="${name}"]`);
    if (field) field.classList.add('is-invalid');
    const err = form.querySelector(`[data-field-error="${name}"]`);
    if (err) {
      err.textContent = message;
      err.hidden = false;
    }
  };

  const validate = () => {
    clearErrors();
    let ok = true;
    const name = (form.name?.value || '').trim();
    const email = (form.email?.value || '').trim();
    const phone = (form.phone?.value || '').replace(/\D+/g, '');
    const role = (form.role?.value || '').trim();
    const about = (form.message?.value || '').trim();
    const file = resumeInput?.files?.[0];

    if (name === '' || name.length > 120) {
      setFieldError('name', 'Please enter your full name.');
      ok = false;
    }
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email) || email.length > 190) {
      setFieldError('email', 'Please enter a valid email address.');
      ok = false;
    }
    if (!/^\d{8,15}$/.test(phone)) {
      setFieldError('phone', 'Enter a valid mobile number.');
      ok = false;
    }
    if (role === '') {
      setFieldError('role', 'Please select a role.');
      ok = false;
    }
    if (about.length > 5000) {
      setFieldError('message', 'Please keep this under 5000 characters.');
      ok = false;
    }
    if (!file) {
      setFieldError('resume', 'Please upload your resume.');
      ok = false;
    } else if (file.size > 5 * 1024 * 1024) {
      setFieldError('resume', 'Resume must be 5 MB or smaller.');
      ok = false;
    }

    return ok;
  };

  form.addEventListener('submit', (event) => {
    if (!validate()) {
      event.preventDefault();
      if (statusBox) {
        statusBox.hidden = false;
        statusBox.className = 'pg-careers-alert is-err';
        statusBox.textContent = 'Please correct the highlighted fields and try again.';
      }
      return;
    }
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.textContent = 'Submitting…';
    }
  });

})();
