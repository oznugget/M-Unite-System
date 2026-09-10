// M-Unite Officer - Notices page

let notices = [
  { id: 1, title: "Water Outage Scheduled", target: "Ward 5", content: "Water will be off on Friday for pipe repairs.", date: "2026-08-20" },
  { id: 2, title: "Community Clean-Up Day", target: "General", content: "Join us this Saturday at 9am at the town hall.", date: "2026-08-18" },
  { id: 3, title: "Road Closure Notice", target: "Ward 2", content: "Church Street will be closed for resurfacing.", date: "2026-08-15" }
];

let nextId = 4;
let editingId = null;

const listEl = document.getElementById("notice-list");
const emptyMsg = document.getElementById("empty-msg");
const searchInput = document.getElementById("search-input");
const filterSelect = document.getElementById("filter-select");
const form = document.getElementById("notice-form");
const titleInput = document.getElementById("notice-title");
const contentInput = document.getElementById("notice-content");
const targetInput = document.getElementById("notice-target");
const submitBtn = document.getElementById("submit-btn");
const cancelBtn = document.getElementById("cancel-edit-btn");

function updateStats() {
  document.getElementById("stat-total").textContent = notices.length;
  document.getElementById("stat-general").textContent = notices.filter(n => n.target === "General").length;
  document.getElementById("stat-ward").textContent = notices.filter(n => n.target !== "General").length;
}

function render() {
  const search = searchInput.value.toLowerCase();
  const filter = filterSelect.value;

  const filtered = notices.filter(n => {
    const matchesSearch = n.title.toLowerCase().includes(search) || n.content.toLowerCase().includes(search);
    const matchesFilter = filter === "all" || n.target === filter;
    return matchesSearch && matchesFilter;
  });

  listEl.innerHTML = "";
  emptyMsg.style.display = filtered.length ? "none" : "block";

  filtered.sort((a, b) => new Date(b.date) - new Date(a.date)).forEach(n => {
    const li = document.createElement("li");
    li.innerHTML = `
      <div class="item-title">${n.title} <span class="badge badge-progress">${n.target}</span></div>
      <div class="item-meta">Posted ${n.date}</div>
      <p>${n.content}</p>
      <button class="secondary" onclick="editNotice(${n.id})">Edit</button>
      <button class="danger" onclick="deleteNotice(${n.id})">Delete</button>
    `;
    listEl.appendChild(li);
  });

  updateStats();
}

function editNotice(id) {
  const n = notices.find(n => n.id === id);
  titleInput.value = n.title;
  targetInput.value = n.target;
  contentInput.value = n.content;
  editingId = id;
  submitBtn.textContent = "Save Changes";
  cancelBtn.style.display = "inline-block";
  window.scrollTo({ top: 0, behavior: "smooth" });
}

function deleteNotice(id) {
  if (confirm("Delete this notice?")) {
    notices = notices.filter(n => n.id !== id);
    render();
  }
}

function resetForm() {
  form.reset();
  editingId = null;
  submitBtn.textContent = "Post Notice";
  cancelBtn.style.display = "none";
  titleInput.closest("label").classList.remove("invalid");
  contentInput.closest("label").classList.remove("invalid");
}

cancelBtn.addEventListener("click", resetForm);

form.addEventListener("submit", (e) => {
  e.preventDefault();
  let valid = true;

  if (titleInput.value.trim() === "") {
    titleInput.closest("label").classList.add("invalid");
    valid = false;
  } else {
    titleInput.closest("label").classList.remove("invalid");
  }

  if (contentInput.value.trim() === "") {
    contentInput.closest("label").classList.add("invalid");
    valid = false;
  } else {
    contentInput.closest("label").classList.remove("invalid");
  }

  if (!valid) return;

  if (editingId) {
    const n = notices.find(n => n.id === editingId);
    n.title = titleInput.value;
    n.target = targetInput.value;
    n.content = contentInput.value;
  } else {
    notices.push({ id: nextId++, title: titleInput.value, target: targetInput.value, content: contentInput.value, date: new Date().toISOString().slice(0, 10) });
  }

  resetForm();
  render();
});

searchInput.addEventListener("input", render);
filterSelect.addEventListener("change", render);

render();
