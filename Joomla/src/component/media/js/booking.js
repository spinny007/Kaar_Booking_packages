document.querySelectorAll('[data-kaar-booking]').forEach((root) => {
  const from = root.querySelector('[name="origin_text"]');
  const to = root.querySelector('[name="destination_text"]');
  root.querySelector('[data-kaar-swap]')?.addEventListener('click', () => {
    const value = from.value;
    from.value = to.value;
    to.value = value;
    from.focus();
  });
  const updateReturn = () => {
    const service = root.querySelector('[name="service_type"]:checked')?.value;
    const wrapper = root.querySelector('[data-kaar-return]');
    const field = wrapper?.querySelector('input');
    const required = service === 'outstation_round_trip';
    if (wrapper) wrapper.hidden = !required;
    if (field) field.required = required;
  };
  root.querySelectorAll('[name="service_type"]').forEach((item) => item.addEventListener('change', updateReturn));
  updateReturn();
});
