// Prevent entering both developing and accomplished scores for same criteria
document.querySelectorAll('tr').forEach(row => {
  const dev = row.querySelector('.developing');
  const acc = row.querySelector('.accomplished');

  if (dev && acc) {
    dev.addEventListener('input', () => {
      if (dev.value && acc.value) {
        acc.value = '';
        calculateRowTotal(dev);
      }
    });

    acc.addEventListener('input', () => {
      if (acc.value && dev.value) {
        dev.value = '';
        calculateRowTotal(acc);
      }
    });
  }
});

// Calculate row total and grand total
function calculateRowTotal(input) {
  const row = input.closest('tr');
  const developing = row.querySelector('.developing').value || 0;
  const accomplished = row.querySelector('.accomplished').value || 0;
  const rowTotal = parseInt(developing) + parseInt(accomplished);

  row.querySelector('.row-total').textContent = rowTotal;
  calculateGrandTotal();
}

function calculateGrandTotal() {
  let grandTotal = 0;
  document.querySelectorAll('.row-total').forEach(total => {
    grandTotal += parseInt(total.textContent) || 0;
  });
  document.getElementById('grand-total').textContent = grandTotal;
}

// Form validation
document.getElementById("gradeForm").addEventListener("submit", function (event) {
  let isValid = true;

  // Check all required fields
  const requiredInputs = this.querySelectorAll("[required]");
  requiredInputs.forEach(input => {
    if (!input.value.trim()) {
      isValid = false;
      input.style.borderColor = "red";
    } else {
      input.style.borderColor = "";
    }
  });

  // Check at least one score per criteria
  document.querySelectorAll('tr').forEach(row => {
    const dev = row.querySelector('.developing');
    const acc = row.querySelector('.accomplished');

    if (dev && acc && !dev.value && !acc.value) {
      isValid = false;
      row.style.backgroundColor = "#ffdddd";
    } else {
      row.style.backgroundColor = "";
    }
  });

  if (!isValid) {
    event.preventDefault();
    alert("Please fill in all required fields and provide at least one score per criteria.");
    return false;
  }

  return true;
});

// // // Admin page filtering
// // if (document.getElementById("groupFilter")) {
// //   document.getElementById("groupFilter").addEventListener("input", function () {
// //     const filter = this.value.toLowerCase();
// //     const rows = document.querySelectorAll("#gradeTable tr:not(:first-child)");

// //     rows.forEach(row => {
// //       const groupCell = row.querySelector("td:first-child");
// //       if (groupCell) {
// //         const match = groupCell.textContent.toLowerCase().includes(filter);
// //         row.style.display = match ? "" : "none";
// //       }
// //     });
// //   });
// }

// Calculate row totals and grand total
function calculateRowTotal(input) {
  const row = input.closest('tr');
  const developing = row.querySelector('.developing').value || 0;
  const accomplished = row.querySelector('.accomplished').value || 0;
  const rowTotal = parseInt(developing) + parseInt(accomplished);

  row.querySelector('.row-total').textContent = rowTotal;
  calculateGrandTotal();
}

function calculateGrandTotal() {
  let grandTotal = 0;
  document.querySelectorAll('.row-total').forEach(total => {
    grandTotal += parseInt(total.textContent) || 0;
  });
  document.getElementById('grand-total').textContent = grandTotal;
}

// Form validation
document.getElementById("gradeForm").addEventListener("submit", function (event) {
  let isValid = true;

  // Check required fields
  this.querySelectorAll("[required]").forEach(input => {
    if (!input.value.trim()) {
      isValid = false;
      input.style.borderColor = "red";
    } else {
      input.style.borderColor = "";
    }
  });

  // Check at least one score per criteria
  document.querySelectorAll('tr').forEach(row => {
    const dev = row.querySelector('.developing');
    const acc = row.querySelector('.accomplished');

    if (dev && acc && !dev.value && !acc.value) {
      isValid = false;
      row.style.backgroundColor = "#ffdddd";
    } else {
      row.style.backgroundColor = "";
    }
  });

  if (!isValid) {
    event.preventDefault();
    alert("Please fill in all required fields and provide at least one score per criteria.");
  }
});