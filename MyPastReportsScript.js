const filterBtn = document.querySelector('.filter-btn');
const filterPanel = document.getElementById('filter-panel');
const filterCheckboxes = document.querySelectorAll('.filter-checkbox');
const reportCards = document.querySelectorAll('.report-card');
 
/* ----- Opening/closing the panel ----- */
 
filterBtn.addEventListener('click', function () {
 
 
 
  const isCurrentlyHidden = filterPanel.hidden;
 
  filterPanel.hidden = !isCurrentlyHidden;
  filterBtn.setAttribute('aria-expanded', String(isCurrentlyHidden));
 
});
 

document.addEventListener('click', function (event) {
 
  
  const clickedInsideButton = filterBtn.contains(event.target);
  const clickedInsidePanel = filterPanel.contains(event.target);
 
  if (!clickedInsideButton && !clickedInsidePanel && !filterPanel.hidden) {
    filterPanel.hidden = true;
    filterBtn.setAttribute('aria-expanded', 'false');
  }
 
});
 
/* ----- Actually filtering the cards ----- */
 
function applyFilter() {
 
  
 
  const checkedStatuses = Array.from(filterCheckboxes)
    .filter(function (checkbox) { return checkbox.checked; })
    .map(function (checkbox) { return checkbox.value; });
 
  
 
  reportCards.forEach(function (card) {
 
    const cardStatus = card.dataset.status; // reads the data-status attribute we set back when building the cards
 
    if (checkedStatuses.includes(cardStatus)) {
      card.hidden = false;
    } else {
      card.hidden = true;
    }
 
  });
 
}
 
/* Re-run the filter every time ANY checkbox changes state */
 
filterCheckboxes.forEach(function (checkbox) {
  checkbox.addEventListener('change', applyFilter);
});