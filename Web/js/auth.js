/**
 * SmartMove Transport — Auth Interactivity (Login & Sign Up)
 * Handles role switching tabs and dynamic form field toggles
 */
document.addEventListener('DOMContentLoaded', () => {
  const roleButtons = document.querySelectorAll('.role-btn');
  const userRoleInput = document.getElementById('userRoleInput');
  const driverFields = document.getElementById('driverFields');

  if (roleButtons.length > 0) {
    roleButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        roleButtons.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        const role = btn.dataset.role;
        if (userRoleInput) {
          userRoleInput.value = role;
        }

        if (driverFields) {
          if (role === 'DRIVER') {
            driverFields.classList.add('show');
          } else {
            driverFields.classList.remove('show');
          }
        }
      });
    });
  }
});
