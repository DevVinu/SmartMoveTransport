  <!-- Add vehicle modal -->
  <div class="modal-backdrop" id="modalAddVehicle">
    <div class="modal-box">
      <div class="modal-header">
        <h3 class="modal-title">Register New Vehicle to Fleet</h3>
        <button type="button" class="modal-close-btn" onclick="closeModal('modalAddVehicle')">&times;</button>
      </div>
      <form method="POST" action="dashboard.php">
        <input type="hidden" name="action" value="addVehicle">
        <div class="form-group">
          <label class="form-label">Registration Number (e.g. WP-ND-5544)</label>
          <input type="text" name="registrationNo" class="form-input" required placeholder="WP-ND-5544">
        </div>
        <div class="form-group">
          <label class="form-label">Vehicle Category</label>
          <select name="vehicleType" class="form-select">
            <option value="BUS">Bus (Intercity Coach)</option>
            <option value="VAN">Van (Executive Commuter)</option>
            <option value="CAR">Car (Luxury Sedan)</option>
            <option value="THREEWHEEL">Three-Wheeler</option>
            <option value="BIKE">Motorbike</option>
          </select>
        </div>
        <div class="form-group" style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem;">
          <div>
            <label class="form-label">Model</label>
            <input type="text" name="model" class="form-input" required placeholder="Rosa / HiAce">
          </div>
          <div>
            <label class="form-label">Manufacturer</label>
            <input type="text" name="manufacturer" class="form-input" required placeholder="Toyota / Mitsubishi">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Passenger Capacity (Seats)</label>
          <input type="number" name="capacity" class="form-input" min="1" max="100" value="30" required>
        </div>
        <div style="display:flex; justify-content:flex-end; gap:0.75rem; margin-top:1.25rem;">
          <button type="button" class="action-btn-sm" onclick="closeModal('modalAddVehicle')">Cancel</button>
          <button type="submit" class="btn-primary" style="padding:0.6rem 1.25rem;">Save Vehicle</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Add user modal -->
  <div class="modal-backdrop" id="modalAddUser">
    <div class="modal-box">
      <div class="modal-header">
        <h3 class="modal-title">Create System User Account</h3>
        <button type="button" class="modal-close-btn" onclick="closeModal('modalAddUser')">&times;</button>
      </div>
      <form method="POST" action="dashboard.php">
        <input type="hidden" name="action" value="addUser">
        <div class="form-group" style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem;">
          <div>
            <label class="form-label">First Name</label>
            <input type="text" name="firstName" class="form-input" required placeholder="John">
          </div>
          <div>
            <label class="form-label">Last Name</label>
            <input type="text" name="lastName" class="form-input" required placeholder="Perera">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Email Address</label>
          <input type="email" name="email" class="form-input" required placeholder="john@smartmove.com">
        </div>
        <div class="form-group" style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem;">
          <div>
            <label class="form-label">Phone Number</label>
            <input type="text" name="phone" class="form-input" required placeholder="+94771234567">
          </div>
          <div>
            <label class="form-label">NIC Number</label>
            <input type="text" name="nic" class="form-input" required placeholder="199512345678">
          </div>
        </div>
        <div class="form-group" style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem;">
          <div>
            <label class="form-label">Role</label>
            <select name="role" id="addUserRoleSelect" class="form-select" onchange="toggleRoleFields(this.value, 'add')">
              <option value="PASSENGER" selected>Passenger</option>
              <option value="DRIVER">Driver</option>
              <option value="STAFF">Staff / Dispatcher</option>
              <option value="ADMIN">System Administrator</option>
            </select>
          </div>
          <div>
            <label class="form-label">Temporary Password</label>
            <input type="password" name="password" class="form-input" required placeholder="••••••••">
          </div>
        </div>

        <!-- Passenger attributes -->
        <div id="addPassengerFields" class="actor-fields-group">
          <div class="form-group">
            <label class="form-label">Residential Address</label>
            <input type="text" name="address" class="form-input" placeholder="e.g. No. 45, Galle Road, Colombo 03">
          </div>
          <div class="form-group">
            <label class="form-label">Preferred Payment Method</label>
            <select name="preferredPaymentMethod" class="form-select">
              <option value="CARD">Credit / Debit Card</option>
              <option value="CASH">Cash at Boarding</option>
              <option value="WALLET">Digital Wallet</option>
              <option value="ONLINE">Online Net Banking</option>
            </select>
          </div>
        </div>

        <!-- Driver attributes -->
        <div id="addDriverFields" class="actor-fields-group" style="display:none;">
          <div class="form-group" style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem;">
            <div>
              <label class="form-label">Driving License No.</label>
              <input type="text" name="licenseNo" class="form-input" placeholder="B-8891234">
            </div>
            <div>
              <label class="form-label">License Class</label>
              <select name="licenseType" class="form-select">
                <option value="HEAVY_COACH">Heavy Coach (Buses)</option>
                <option value="COMMERCIAL_VAN">Commercial Van</option>
                <option value="EXECUTIVE_SEDAN">Executive Sedan</option>
                <option value="THREE_WHEELER">Three-Wheeler</option>
                <option value="MOTORBIKE">Motorbike</option>
              </select>
            </div>
          </div>
          <div class="form-group" style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem;">
            <div>
              <label class="form-label">License Expiry Date</label>
              <input type="date" name="licenseExpiryDate" class="form-input" value="<?php echo date('Y-m-d', strtotime('+3 years')); ?>">
            </div>
            <div>
              <label class="form-label">Driver Status</label>
              <select name="driverStatus" class="form-select">
                <option value="AVAILABLE">Available</option>
                <option value="ON_DUTY">On Duty</option>
                <option value="OFF_DUTY">Off Duty</option>
                <option value="SUSPENDED">Suspended</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Staff attributes -->
        <div id="addStaffFields" class="actor-fields-group" style="display:none;">
          <div class="form-group" style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem;">
            <div>
              <label class="form-label">Staff Position</label>
              <select name="position" class="form-select">
                <option value="Dispatch Controller">Dispatch Controller</option>
                <option value="Fleet Maintenance Supervisor">Fleet Maintenance Supervisor</option>
                <option value="Ticketing Officer">Ticketing Officer</option>
                <option value="Customer Support Officer">Customer Support Officer</option>
                <option value="Operations Executive">Operations Executive</option>
              </select>
            </div>
            <div>
              <label class="form-label">Staff Status</label>
              <select name="staffStatus" class="form-select">
                <option value="ACTIVE">Active Duty</option>
                <option value="ON_LEAVE">On Leave</option>
                <option value="INACTIVE">Inactive</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Admin attributes -->
        <div id="addAdminFields" class="actor-fields-group" style="display:none;">
          <div class="form-group" style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem;">
            <div>
              <label class="form-label">Admin Code</label>
              <input type="text" name="adminCode" class="form-input" placeholder="ADM-101">
            </div>
            <div>
              <label class="form-label">Privilege Level</label>
              <select name="privilegeLevel" class="form-select">
                <option value="OPS_ADMIN">Operations Admin</option>
                <option value="SUPER_ADMIN">Super Administrator</option>
                <option value="AUDITOR">Compliance Auditor</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Department</label>
            <input type="text" name="department" class="form-input" placeholder="Central Operations Command">
          </div>
        </div>

        <div style="display:flex; justify-content:flex-end; gap:0.75rem; margin-top:1.25rem;">
          <button type="button" class="action-btn-sm" onclick="closeModal('modalAddUser')">Cancel</button>
          <button type="submit" class="btn-primary" style="padding:0.6rem 1.25rem;">Create Account</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Edit user modal -->
  <div class="modal-backdrop" id="modalEditUser">
    <div class="modal-box">
      <div class="modal-header">
        <h3 class="modal-title">Edit User Account &amp; Actor Details</h3>
        <button type="button" class="modal-close-btn" onclick="closeModal('modalEditUser')">&times;</button>
      </div>
      <form method="POST" action="dashboard.php">
        <input type="hidden" name="action" value="editUser">
        <input type="hidden" name="userId" id="editUserId" value="">
        <div class="form-group" style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem;">
          <div>
            <label class="form-label">First Name</label>
            <input type="text" name="firstName" id="editFirstName" class="form-input" required>
          </div>
          <div>
            <label class="form-label">Last Name</label>
            <input type="text" name="lastName" id="editLastName" class="form-input" required>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Email Address</label>
          <input type="email" name="email" id="editEmail" class="form-input" required>
        </div>
        <div class="form-group" style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem;">
          <div>
            <label class="form-label">Phone Number</label>
            <input type="text" name="phone" id="editPhone" class="form-input" required>
          </div>
          <div>
            <label class="form-label">NIC Number</label>
            <input type="text" name="nic" id="editNic" class="form-input" required>
          </div>
        </div>
        <div class="form-group" style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem;">
          <div>
            <label class="form-label">Role</label>
            <select name="role" id="editRoleSelect" class="form-select" onchange="toggleRoleFields(this.value, 'edit')">
              <option value="PASSENGER">Passenger</option>
              <option value="DRIVER">Driver</option>
              <option value="STAFF">Staff / Dispatcher</option>
              <option value="ADMIN">System Administrator</option>
            </select>
          </div>
          <div>
            <label class="form-label">Account Status</label>
            <select name="accountStatus" id="editAccountStatus" class="form-select">
              <option value="ACTIVE">Active</option>
              <option value="SUSPENDED">Suspended</option>
              <option value="INACTIVE">Inactive</option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Reset Password (Optional)</label>
          <input type="password" name="password" id="editPassword" class="form-input" placeholder="Leave empty to retain existing password">
        </div>

        <!-- Passenger edit attributes -->
        <div id="editPassengerFields" class="actor-fields-group">
          <div class="form-group">
            <label class="form-label">Residential Address</label>
            <input type="text" name="address" id="editPassengerAddress" class="form-input" placeholder="e.g. No. 45, Galle Road, Colombo 03">
          </div>
          <div class="form-group">
            <label class="form-label">Preferred Payment Method</label>
            <select name="preferredPaymentMethod" id="editPassengerPayment" class="form-select">
              <option value="CARD">Credit / Debit Card</option>
              <option value="CASH">Cash at Boarding</option>
              <option value="WALLET">Digital Wallet</option>
              <option value="ONLINE">Online Net Banking</option>
            </select>
          </div>
        </div>

        <!-- Driver edit attributes -->
        <div id="editDriverFields" class="actor-fields-group" style="display:none;">
          <div class="form-group" style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem;">
            <div>
              <label class="form-label">Driving License No.</label>
              <input type="text" name="licenseNo" id="editDriverLicenseNo" class="form-input">
            </div>
            <div>
              <label class="form-label">License Class</label>
              <select name="licenseType" id="editDriverLicenseType" class="form-select">
                <option value="HEAVY_COACH">Heavy Coach (Buses)</option>
                <option value="COMMERCIAL_VAN">Commercial Van</option>
                <option value="EXECUTIVE_SEDAN">Executive Sedan</option>
                <option value="THREE_WHEELER">Three-Wheeler</option>
                <option value="MOTORBIKE">Motorbike</option>
              </select>
            </div>
          </div>
          <div class="form-group" style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem;">
            <div>
              <label class="form-label">License Expiry Date</label>
              <input type="date" name="licenseExpiryDate" id="editDriverLicenseExpiryDate" class="form-input">
            </div>
            <div>
              <label class="form-label">Driver Status</label>
              <select name="driverStatus" id="editDriverStatus" class="form-select">
                <option value="AVAILABLE">Available</option>
                <option value="ON_DUTY">On Duty</option>
                <option value="OFF_DUTY">Off Duty</option>
                <option value="SUSPENDED">Suspended</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Staff edit attributes -->
        <div id="editStaffFields" class="actor-fields-group" style="display:none;">
          <div class="form-group" style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem;">
            <div>
              <label class="form-label">Staff Position</label>
              <input type="text" name="position" id="editStaffPosition" class="form-input" placeholder="Dispatch Controller">
            </div>
            <div>
              <label class="form-label">Staff Status</label>
              <select name="staffStatus" id="editStaffStatus" class="form-select">
                <option value="ACTIVE">Active Duty</option>
                <option value="ON_LEAVE">On Leave</option>
                <option value="INACTIVE">Inactive</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Admin edit attributes -->
        <div id="editAdminFields" class="actor-fields-group" style="display:none;">
          <div class="form-group" style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem;">
            <div>
              <label class="form-label">Admin Code</label>
              <input type="text" name="adminCode" id="editAdminCode" class="form-input">
            </div>
            <div>
              <label class="form-label">Privilege Level</label>
              <select name="privilegeLevel" id="editAdminPrivilege" class="form-select">
                <option value="OPS_ADMIN">Operations Admin</option>
                <option value="SUPER_ADMIN">Super Administrator</option>
                <option value="AUDITOR">Compliance Auditor</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Department</label>
            <input type="text" name="department" id="editAdminDepartment" class="form-input">
          </div>
        </div>

        <div style="display:flex; justify-content:flex-end; gap:0.75rem; margin-top:1.25rem;">
          <button type="button" class="action-btn-sm" onclick="closeModal('modalEditUser')">Cancel</button>
          <button type="submit" class="btn-primary" style="padding:0.6rem 1.25rem;">Save Changes</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Add route modal -->
  <div class="modal-backdrop" id="modalAddRoute">
    <div class="modal-box">
      <div class="modal-header">
        <h3 class="modal-title">Establish New Transit Route</h3>
        <button type="button" class="modal-close-btn" onclick="closeModal('modalAddRoute')">&times;</button>
      </div>
      <form method="POST" action="dashboard.php">
        <input type="hidden" name="action" value="addRoute">
        <div class="form-group">
          <label class="form-label">Route Display Name</label>
          <input type="text" name="routeName" class="form-input" required placeholder="e.g. Negombo - Colombo Express">
        </div>
        <div class="form-group" style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem;">
          <div>
            <label class="form-label">Origin</label>
            <input type="text" name="origin" class="form-input" required placeholder="Negombo Bus Stand">
          </div>
          <div>
            <label class="form-label">Destination</label>
            <input type="text" name="destination" class="form-input" required placeholder="Colombo Fort">
          </div>
        </div>
        <div class="form-group" style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:0.5rem;">
          <div>
            <label class="form-label">Distance (KM)</label>
            <input type="number" step="0.1" name="distanceKm" class="form-input" required value="38.5">
          </div>
          <div>
            <label class="form-label">Est. Duration (Mins)</label>
            <input type="number" name="estimatedDurationMinutes" class="form-input" required value="50">
          </div>
          <div>
            <label class="form-label">Base Fare (LKR)</label>
            <input type="number" step="0.5" name="baseFare" class="form-input" required value="400.00">
          </div>
        </div>
        <div style="display:flex; justify-content:flex-end; gap:0.75rem; margin-top:1.25rem;">
          <button type="button" class="action-btn-sm" onclick="closeModal('modalAddRoute')">Cancel</button>
          <button type="submit" class="btn-primary" style="padding:0.6rem 1.25rem;">Establish Route</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Broadcast advisory modal -->
  <div class="modal-backdrop" id="modalBroadcastAdvisory">
    <div class="modal-box">
      <div class="modal-header">
        <h3 class="modal-title">Broadcast Travel Advisory</h3>
        <button type="button" class="modal-close-btn" onclick="closeModal('modalBroadcastAdvisory')">&times;</button>
      </div>
      <form method="POST" action="dashboard.php">
        <input type="hidden" name="action" value="createAnnouncement">
        <div class="form-group">
          <label class="form-label">Advisory Headline / Title</label>
          <input type="text" name="title" class="form-input" required
            placeholder="e.g. Southern Expressway Flash Flood Notice">
        </div>
        <div class="form-group" style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem;">
          <div>
            <label class="form-label">Category</label>
            <select name="category" class="form-select">
              <option value="EXPRESSWAY_ALERT">Expressway Alert</option>
              <option value="ROADWORK">Scheduled Roadwork</option>
              <option value="WEATHER">Weather Advisory</option>
              <option value="COMMUNITY_NEWS">Community News</option>
            </select>
          </div>
          <div>
            <label class="form-label">Urgency Priority</label>
            <select name="priority" class="form-select">
              <option value="HIGH">High Priority</option>
              <option value="MEDIUM">Medium Priority</option>
              <option value="LOW">Low Priority</option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Affected Route IDs (comma-separated, e.g. 1, 2)</label>
          <input type="text" name="affectedRoutes" class="form-input" value="1, 2">
        </div>
        <div class="form-group">
          <label class="form-label">Advisory Details & Commuter Guidance</label>
          <textarea name="message" class="form-textarea" rows="3" required
            placeholder="Enter full notification message broadcasted to passengers and drivers..."></textarea>
        </div>
        <div style="display:flex; justify-content:flex-end; gap:0.75rem; margin-top:1.25rem;">
          <button type="button" class="action-btn-sm" onclick="closeModal('modalBroadcastAdvisory')">Cancel</button>
          <button type="submit" class="btn-primary" style="padding:0.6rem 1.25rem;">Publish Broadcast</button>
        </div>
      </form>
    </div>
  </div>

