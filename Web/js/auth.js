// Auth tabs
document.addEventListener('DOMContentLoaded', () => {
  const roleButtons = document.querySelectorAll('.role-btn');
  const userRoleInput = document.getElementById('userRoleInput');
  const hintBox = document.getElementById('noRoleSelectedHint');
  const signupForm = document.querySelector('.auth-form');

  const actorFieldGroups = {
    PASSENGER: document.getElementById('passengerFields'),
    DRIVER: document.getElementById('driverFields')
  };

  function selectRole(role) {
    if (!role) return;
    const cleanRole = role.toUpperCase();

    // Toggle active tab style
    roleButtons.forEach(btn => {
      const btnRole = (btn.dataset.role || '').toUpperCase();
      if (btnRole === cleanRole) {
        btn.classList.add('active');
      } else {
        btn.classList.remove('active');
      }
    });

    if (userRoleInput) {
      userRoleInput.value = cleanRole;
    }

    if (hintBox) {
      hintBox.style.display = 'none';
    }

    // Toggle actor fields
    Object.keys(actorFieldGroups).forEach(r => {
      const el = actorFieldGroups[r];
      if (el) {
        if (r === cleanRole) {
          el.style.display = 'flex';
          el.classList.add('show');
        } else {
          el.style.display = 'none';
          el.classList.remove('show');
        }
      }
    });
  }

  if (roleButtons.length > 0) {
    roleButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        const role = btn.dataset.role || '';
        selectRole(role);
      });
    });
  }

  // Activate pre-selected role if present
  if (userRoleInput && userRoleInput.value) {
    selectRole(userRoleInput.value);
  }

  // Validate role selection on submit
  if (signupForm) {
    signupForm.addEventListener('submit', (e) => {
      if (userRoleInput && !userRoleInput.value) {
        e.preventDefault();
        if (hintBox) {
          hintBox.style.display = 'block';
          hintBox.style.borderColor = 'rgba(239, 68, 68, 0.6)';
          hintBox.style.color = '#FCA5A5';
          hintBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
      }
    });
  }
});
