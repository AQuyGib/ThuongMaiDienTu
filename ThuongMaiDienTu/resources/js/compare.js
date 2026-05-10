// resources/js/compare.js
// Compare bar dynamic behavior
let currentSlotIndex = null;

function debounce(func, wait) {
  let timeout;
  return function (...args) {
    clearTimeout(timeout);
    timeout = setTimeout(() => func.apply(this, args), wait);
  };
}

function openCompareSearch(slotIndex) {
  currentSlotIndex = slotIndex;
  document.getElementById('compareSearchModal').style.display = 'block';
  const input = document.getElementById('compareSearchInput');
  input.value = '';
  document.getElementById('compareSearchResults').innerHTML = '';
  input.focus();
}

function closeCompareSearch() {
  document.getElementById('compareSearchModal').style.display = 'none';
}

function performSearch(query) {
  // Adjust the endpoint according to your routes
  return fetch(`/compare/search?term=${encodeURIComponent(query)}`)
    .then(res => res.json());
}

function renderSearchResults(results) {
  const container = document.getElementById('compareSearchResults');
  container.innerHTML = '';
  results.forEach(product => {
    const item = document.createElement('div');
    item.className = 'search-result-item';
    item.innerHTML = `
      <img src="${product.image}" alt="${product.name}" class="result-img" />
      <span class="result-name">${product.name}</span>
      <span class="result-price">${product.price}</span>
    `;
    item.onclick = () => addToCompare(product);
    container.appendChild(item);
  });
}

const searchInput = document.getElementById('compareSearchInput');
if (searchInput) {
  searchInput.addEventListener('input', debounce(function (e) {
    const term = e.target.value.trim();
    if (term.length < 2) {
      document.getElementById('compareSearchResults').innerHTML = '';
      return;
    }
    performSearch(term).then(renderSearchResults);
  }, 300));
}

function addToCompare(product) {
  if (currentSlotIndex === null) return;
  const slot = document.getElementById(`compareSlot${currentSlotIndex}`);
  const emptyDiv = slot.querySelector('.compare-slot-empty');
  const filledDiv = slot.querySelector('.compare-slot-filled');

  filledDiv.style.display = 'flex';
  emptyDiv.style.display = 'none';

  filledDiv.querySelector('.compare-slot-img').src = product.image;
  filledDiv.querySelector('.compare-slot-name').textContent = product.name;
  filledDiv.querySelector('.compare-slot-price').textContent = product.price;
  filledDiv.dataset.productId = product.id;

  updateCount();
  closeCompareSearch();
}

function removeFromCompare(btn) {
  const slot = btn.closest('.compare-slot');
  const emptyDiv = slot.querySelector('.compare-slot-empty');
  const filledDiv = slot.querySelector('.compare-slot-filled');

  filledDiv.style.display = 'none';
  emptyDiv.style.display = 'block';
  filledDiv.dataset.productId = '';
  updateCount();
}

function clearCompare() {
  document.querySelectorAll('.compare-slot-filled').forEach(el => {
    el.style.display = 'none';
    el.dataset.productId = '';
  });
  document.querySelectorAll('.compare-slot-empty').forEach(el => el.style.display = 'block');
  updateCount();
}

function updateCount() {
  const count = document.querySelectorAll('.compare-slot-filled[data-product-id]:not([data-product-id=""])').length;
  document.getElementById('compareCountBadge').textContent = count;
  const bar = document.getElementById('compareBar');
  if (bar) bar.style.display = count > 0 ? 'block' : 'none';
}

function toggleCollapse() {
  const container = document.getElementById('compareSlotsContainer');
  const btn = document.getElementById('compareCollapseBtn');
  const bar = document.getElementById('compareBar');
  
  if (container && bar) {
    if (container.style.display === 'none') {
      container.style.display = 'flex';
      bar.classList.remove('collapsed');
      if (btn) btn.textContent = 'Thu gọn';
    } else {
      container.style.display = 'none';
      bar.classList.add('collapsed');
      if (btn) btn.textContent = 'Mở rộng';
    }
  }
}

// Bind collapse button
const collapseBtn = document.getElementById('compareCollapseBtn');
if (collapseBtn) {
  collapseBtn.addEventListener('click', toggleCollapse);
}

// Ensure bar is hidden initially if no items
updateCount();
