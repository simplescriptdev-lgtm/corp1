document.addEventListener('click', (e)=>{
  const btnOpen = e.target.closest('[data-modal-open]');
  if (btnOpen) {
    const id = btnOpen.getAttribute('data-modal-open');
    document.getElementById(id)?.classList.add('show');
  }
  const btnClose = e.target.closest('[data-modal-close]');
  if (btnClose || e.target.classList.contains('modal')) {
    e.target.closest('.modal')?.classList.remove('show');
  }
});