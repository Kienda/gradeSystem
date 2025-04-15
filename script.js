document.addEventListener('DOMContentLoaded', function () {
  initScoringSystem();
});

function initScoringSystem() {
  const rows = document.querySelectorAll('tr[data-criteria]');

  rows.forEach(row => {
    const dev = row.querySelector('.developing');
    const acc = row.querySelector('.accomplished');

    if (dev && acc) {
      // Initialize row state
      updateInputStates(dev, acc);

      // Add event listeners
      dev.addEventListener('input', () => handleScoreInput(dev, acc));
      acc.addEventListener('input', () => handleScoreInput(acc, dev));

      // Initialize calculations
      calculateRowTotal(dev);
    }
  });

  calculateGrandTotal();
}

function handleScoreInput(currentInput, oppositeInput) {
  // If current input has value, clear and disable opposite
  if (currentInput.value) {
    oppositeInput.value = '';
    oppositeInput.disabled = true;
    oppositeInput.classList.add('disabled-input');
    currentInput.disabled = false;
    currentInput.classList.remove('disabled-input');
  } else {
    // If current input is cleared, enable opposite
    oppositeInput.disabled = false;
    oppositeInput.classList.remove('disabled-input');
  }

  calculateRowTotal(currentInput);
  validateScoreRange(currentInput);
}

function updateInputStates(input1, input2) {
  // Gray out and disable inputs based on current values
  if (input1.value) {
    input2.disabled = true;
    input2.classList.add('disabled-input');
  } else if (input2.value) {
    input1.disabled = true;
    input1.classList.add('disabled-input');
  } else {
    // Neither has value - both enabled
    input1.disabled = false;
    input2.disabled = false;
    input1.classList.remove('disabled-input');
    input2.classList.remove('disabled-input');
  }
}

function calculateRowTotal(input) {
  const row = input.closest('tr');
  const dev = row.querySelector('.developing');
  const acc = row.querySelector('.accomplished');

  // Use whichever score has a value
  const rowTotal = dev.value ? parseInt(dev.value) || 0 :
    acc.value ? parseInt(acc.value) || 0 : 0;

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

function validateScoreRange(input) {
  const max = parseInt(input.max) || (input.classList.contains('accomplished') ? 15 : 10);
  const min = parseInt(input.min) || 0;
  let value = parseInt(input.value) || 0;

  if (value > max) {
    input.value = max;
    showValidationMessage(input, `Maximum score is ${max}`);
  } else if (value < min) {
    input.value = min;
    showValidationMessage(input, `Minimum score is ${min}`);
  }
}

function showValidationMessage(input, message) {
  const container = input.closest('td');
  let messageElement = container.querySelector('.validation-message');

  if (!messageElement) {
    messageElement = document.createElement('div');
    messageElement.className = 'validation-message';
    container.appendChild(messageElement);
  }

  messageElement.textContent = message;
  messageElement.style.display = 'block';

  setTimeout(() => {
    messageElement.style.display = 'none';
  }, 3000);
}

// Form validation
document.getElementById("gradeForm")?.addEventListener("submit", function (e) {
  let isValid = true;
  const errorMessages = [];
  const requiredInputs = this.querySelectorAll("[required]");

  requiredInputs.forEach(input => {
    if (!input.value.trim()) {
      isValid = false;
      input.style.borderColor = "red";
      errorMessages.push(`${input.name} is required`);
    } else {
      input.style.borderColor = "";
    }
  });

  document.querySelectorAll('tr[data-criteria]').forEach(row => {
    const dev = row.querySelector('.developing');
    const acc = row.querySelector('.accomplished');
    const criteriaName = row.getAttribute('data-criteria');

    if (dev && acc && !dev.value && !acc.value) {
      isValid = false;
      row.style.backgroundColor = "#ffdddd";
      errorMessages.push(`Please score "${criteriaName}" criteria`);
    } else {
      row.style.backgroundColor = "";
    }
  });

  if (!isValid) {
    e.preventDefault();
    alert("Validation Errors:\n\n" + errorMessages.join('\n'));
  }
});