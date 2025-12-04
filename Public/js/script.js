/**
 * JavaScript untuk Sistem Informasi Toko Outdoor
 * Animasi, validasi form, dan interaksi user
 */

// ===========================
// GLOBAL FUNCTIONS
// ===========================

/**
 * Tampilkan alert dengan animasi
 */
function showAlert(message, type = "info") {
  const alertContainer = document.createElement("div");
  alertContainer.className = `alert alert-${type}`;
  alertContainer.innerHTML = `
        <strong>${
          type === "success" ? "✓" : type === "danger" ? "✕" : "ⓘ"
        }</strong>
        ${message}
        <span class="close-btn" onclick="this.parentElement.remove();">&times;</span>
    `;
  alertContainer.style.animation = "slideDown 0.3s ease-out";

  const content = document.querySelector(".content") || document.body;
  content.insertBefore(alertContainer, content.firstChild);

  // Auto remove setelah 5 detik
  setTimeout(() => {
    alertContainer.style.animation = "slideUp 0.3s ease-out";
    setTimeout(() => alertContainer.remove(), 300);
  }, 5000);
}

/**
 * Format currency ke Rupiah
 */
function formatCurrency(value) {
  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
  }).format(value);
}

/**
 * Format date
 */
function formatDate(dateString) {
  const options = {
    year: "numeric",
    month: "long",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  };
  return new Date(dateString).toLocaleDateString("id-ID", options);
}

/**
 * Konfirmasi sebelum delete
 */
function confirmDelete(
  message = "Apakah Anda yakin ingin menghapus data ini?"
) {
  return confirm(message);
}

/**
 * Validasi form input
 */
function validateFormInput(formId) {
  const form = document.getElementById(formId);
  if (!form) return false;

  const inputs = form.querySelectorAll(
    "input[required], textarea[required], select[required]"
  );
  let isValid = true;

  inputs.forEach((input) => {
    if (input.value.trim() === "") {
      input.style.borderColor = "#dc3545";
      isValid = false;
    } else {
      input.style.borderColor = "";
    }
  });

  return isValid;
}

/**
 * Validasi email
 */
function validateEmail(email) {
  const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return re.test(email);
}

/**
 * Clear form
 */
function clearForm(formId) {
  const form = document.getElementById(formId);
  if (form) {
    form.reset();
    form.querySelectorAll("input, textarea").forEach((input) => {
      input.style.borderColor = "";
    });
  }
}

// ===========================
// TABLE FUNCTIONS
// ===========================

/**
 * Search/Filter di tabel
 */
function filterTable(inputId, tableId) {
  const input = document.getElementById(inputId);
  const table = document.getElementById(tableId);
  const filter = input.value.toUpperCase();
  const rows = table.getElementsByTagName("tr");

  for (let i = 1; i < rows.length; i++) {
    const text = rows[i].textContent || rows[i].innerText;
    if (text.toUpperCase().indexOf(filter) > -1) {
      rows[i].style.display = "";
    } else {
      rows[i].style.display = "none";
    }
  }
}

/**
 * Sort tabel
 */
function sortTable(tableId, columnIndex) {
  const table = document.getElementById(tableId);
  let rows = Array.from(table.querySelectorAll("tbody tr"));
  let isAscending = true;

  // Cek jika sudah di-sort sebelumnya
  const header = table.querySelectorAll("thead th")[columnIndex];
  if (header.classList.contains("sorted-asc")) {
    isAscending = false;
    header.classList.remove("sorted-asc");
    header.classList.add("sorted-desc");
  } else {
    table.querySelectorAll("thead th").forEach((h) => {
      h.classList.remove("sorted-asc", "sorted-desc");
    });
    header.classList.add("sorted-asc");
  }

  rows.sort((a, b) => {
    const aValue = a.cells[columnIndex].textContent.trim();
    const bValue = b.cells[columnIndex].textContent.trim();

    // Cek jika nilai adalah number
    const aNum = parseFloat(aValue.replace(/[^\d.-]/g, ""));
    const bNum = parseFloat(bValue.replace(/[^\d.-]/g, ""));

    if (!isNaN(aNum) && !isNaN(bNum)) {
      return isAscending ? aNum - bNum : bNum - aNum;
    }

    // String comparison
    if (isAscending) {
      return aValue.localeCompare(bValue);
    } else {
      return bValue.localeCompare(aValue);
    }
  });

  const tbody = table.querySelector("tbody");
  rows.forEach((row) => tbody.appendChild(row));
}

/**
 * Export tabel ke CSV
 */
function exportTableToCSV(tableId, filename = "export.csv") {
  const table = document.getElementById(tableId);
  let csv = [];
  const rows = table.querySelectorAll("tr");

  rows.forEach((row) => {
    const rowData = [];
    row.querySelectorAll("th, td").forEach((cell) => {
      rowData.push('"' + cell.textContent.trim().replace(/"/g, '""') + '"');
    });
    csv.push(rowData.join(","));
  });

  const csvContent = "data:text/csv;charset=utf-8," + csv.join("\n");
  const link = document.createElement("a");
  link.setAttribute("href", encodeURI(csvContent));
  link.setAttribute("download", filename);
  link.click();
}

/**
 * Print tabel
 */
function printTable(tableId) {
  const table = document.getElementById(tableId);
  const printWindow = window.open("", "", "width=800,height=600");
  printWindow.document.write("<html><head><title>Print Table</title>");
  printWindow.document.write(
    '<link href="Public/css/style.css" rel="stylesheet">'
  );
  printWindow.document.write("</head><body>");
  printWindow.document.write(table.outerHTML);
  printWindow.document.write("</body></html>");
  printWindow.document.close();
  printWindow.print();
}

// ===========================
// FORM FUNCTIONS
// ===========================

/**
 * Submit form dengan AJAX
 */
function submitFormAjax(formId, actionUrl, onSuccess = null) {
  const form = document.getElementById(formId);
  if (!form) return;

  if (!validateFormInput(formId)) {
    showAlert("Harap isi semua field yang diperlukan", "warning");
    return;
  }

  const formData = new FormData(form);

  fetch(actionUrl, {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showAlert(data.message, "success");
        if (onSuccess) onSuccess(data);
      } else {
        showAlert(data.message, "danger");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showAlert("Terjadi kesalahan: " + error.message, "danger");
    });
}

/**
 * Update input harga otomatis
 */
function updateTotal(jumlahId, hargaSatuanId, totalId) {
  const jumlah = parseFloat(document.getElementById(jumlahId)?.value) || 0;
  const hargaSatuan =
    parseFloat(document.getElementById(hargaSatuanId)?.value) || 0;
  const total = jumlah * hargaSatuan;

  const totalInput = document.getElementById(totalId);
  if (totalInput) {
    totalInput.value = total.toFixed(2);
    totalInput.dispatchEvent(new Event("change"));
  }
}

/**
 * Update grand total transaksi
 */
function updateGrandTotal() {
  const detailRows = document.querySelectorAll(".detail-row");
  let grandTotal = 0;

  detailRows.forEach((row) => {
    const subtotal = parseFloat(row.querySelector(".subtotal")?.value) || 0;
    grandTotal += subtotal;
  });

  const grandTotalInput = document.getElementById("grand_total");
  if (grandTotalInput) {
    grandTotalInput.value = grandTotal.toFixed(2);
  }

  // Update display
  const grandTotalDisplay = document.getElementById("grand_total_display");
  if (grandTotalDisplay) {
    grandTotalDisplay.textContent = formatCurrency(grandTotal);
  }
}

// ===========================
// MODAL FUNCTIONS
// ===========================

/**
 * Open modal
 */
function openModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.classList.add("show");
    modal.style.display = "block";
    document.body.style.overflow = "hidden";
  }
}

/**
 * Close modal
 */
function closeModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.classList.remove("show");
    modal.style.display = "none";
    document.body.style.overflow = "auto";
    clearForm(modalId.replace("modal-", "") + "-form");
  }
}

/**
 * Close modal when click outside
 */
document.addEventListener("click", function (event) {
  if (event.target.classList.contains("modal")) {
    event.target.classList.remove("show");
    event.target.style.display = "none";
    document.body.style.overflow = "auto";
  }
});

// ===========================
// INITIALIZATION
// ===========================

document.addEventListener("DOMContentLoaded", function () {
  // Add animation ke card saat load
  const cards = document.querySelectorAll(".card, .stat-card");
  cards.forEach((card, index) => {
    card.style.animation = `slideUp 0.5s ease-out ${index * 0.1}s backwards`;
  });

  // Add loading animation ke buttons saat click
  const forms = document.querySelectorAll("form");
  forms.forEach((form) => {
    form.addEventListener("submit", function () {
      const submitBtn = this.querySelector('[type="submit"]');
      if (submitBtn) {
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = "⏳ Loading...";
        submitBtn.disabled = true;

        // Re-enable setelah 2 detik (sebagai fallback)
        setTimeout(() => {
          submitBtn.innerHTML = originalText;
          submitBtn.disabled = false;
        }, 2000);
      }
    });
  });

  // Format currency input
  const currencyInputs = document.querySelectorAll(".currency-input");
  currencyInputs.forEach((input) => {
    input.addEventListener("blur", function () {
      const value = parseFloat(this.value) || 0;
      this.value = value.toFixed(2);
    });
  });

  // Add smooth scroll untuk internal links
  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener("click", function (e) {
      e.preventDefault();
      const target = document.querySelector(this.getAttribute("href"));
      if (target) {
        target.scrollIntoView({
          behavior: "smooth",
          block: "start",
        });
      }
    });
  });
});

// ===========================
// LOGOUT FUNCTION
// ===========================

function logout() {
  if (confirm("Apakah Anda yakin ingin logout?")) {
    window.location.href = "logout.php";
  }
}

// ===========================
// PRINT RECEIPT
// ===========================

function printReceipt(transaksiId) {
  const printWindow = window.open(
    `print_receipt.php?id=${transaksiId}`,
    "",
    "width=400,height=600"
  );
  printWindow.focus();
}
