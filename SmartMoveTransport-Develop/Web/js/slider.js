// Step slider carousel controller
document.addEventListener('DOMContentLoaded', () => {
  initStepSlider();
});

function initStepSlider() {
  const page1 = document.getElementById('stepsPage1');
  const page2 = document.getElementById('stepsPage2');
  const indicator = document.getElementById('stepPageIndicator');
  const prevBtn = document.getElementById('prevStepBtn');
  const nextBtn = document.getElementById('nextStepBtn');

  if (!page1 || !page2 || !indicator || !prevBtn || !nextBtn) return;

  let currentPage = 1;

  function updatePage() {
    if (currentPage === 1) {
      page1.classList.add('active');
      page2.classList.remove('active');
      indicator.textContent = '1/2';
    } else {
      page1.classList.remove('active');
      page2.classList.add('active');
      indicator.textContent = '2/2';
    }
  }

  prevBtn.addEventListener('click', () => {
    currentPage = currentPage === 1 ? 2 : 1;
    updatePage();
  });

  nextBtn.addEventListener('click', () => {
    currentPage = currentPage === 2 ? 1 : 2;
    updatePage();
  });
}
