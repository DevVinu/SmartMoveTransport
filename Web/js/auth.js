// Auth tabs and form handler
document.addEventListener('DOMContentLoaded', () => {
  const roleButtons = document.querySelectorAll('.role-btn');
  const userRoleInput = document.getElementById('userRoleInput');
  const actorFieldGroups = {
    PASSENGER: document.getElementById('passengerFields'),
    DRIVER: document.getElementById('driverFields'),
    STAFF: document.getElementById('staffFields'),
    ADMIN: document.getElementById('adminFields')
  };

  if (roleButtons.length > 0) {
    roleButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        roleButtons.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        const role = (btn.dataset.role || 'PASSENGER').toUpperCase();
        if (userRoleInput) {
          userRoleInput.value = role;
        }

        // Toggle actor fields
        Object.keys(actorFieldGroups).forEach(r => {
          const el = actorFieldGroups[r];
          if (el) {
            if (r === role) {
              el.classList.add('show');
            } else {
              el.classList.remove('show');
            }
          }
        });
      });
    });
  }
});
