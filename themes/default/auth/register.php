<!DOCTYPE html>
<html>
<head>
    <title>Register - TLCDesk</title>
    <style>
        body { font-family: sans-serif; background: #f4f4f4; padding: 20px; }
        .card { background: #fff; padding: 30px; border-radius: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 100%; max-width: 600px; margin: 0 auto; }
        h2 { text-align: center; margin-top: 0; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="email"], input[type="password"], input[type="date"], select { width: 100%; padding: 10px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #28a745; color: white; border: none; cursor: pointer; margin-top: 20px; }
        .hidden { display: none; }
        .section-title { border-bottom: 1px solid #eee; padding-bottom: 5px; margin-bottom: 15px; margin-top: 20px; }
    </style>
    <script>
        function toggleFields() {
            var type = document.getElementById('account_type').value;
            var driverFields = document.getElementById('driver-fields');
            var fleetFields = document.getElementById('fleet-fields');

            if (type === 'driver') {
                driverFields.classList.remove('hidden');
                fleetFields.classList.add('hidden');
                setRequired(driverFields, true);
                setRequired(fleetFields, false);
            } else {
                driverFields.classList.add('hidden');
                fleetFields.classList.remove('hidden');
                setRequired(driverFields, false);
                setRequired(fleetFields, true);
            }
        }

        function setRequired(container, isRequired) {
            var inputs = container.querySelectorAll('input, select');
            inputs.forEach(function(input) {
                if (input.dataset.optional !== 'true') {
                    if (isRequired) input.setAttribute('required', 'required');
                    else input.removeAttribute('required');
                }
            });
        }

        function togglePlate() {
            var noVehicle = document.getElementById('no_vehicle').checked;
            var plateInput = document.getElementById('plate');
            if (noVehicle) {
                plateInput.disabled = true;
                plateInput.value = '';
                plateInput.removeAttribute('required');
            } else {
                plateInput.disabled = false;
            }
        }

        window.onload = function() {
            toggleFields();
        };
    </script>
</head>
<body>
    <div class="card">
        <h2>Register</h2>
        <?php if (isset($error)): ?>
            <p style="color: red;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="POST" action="/register">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <div class="form-group">
                <label>Account Type</label>
                <select name="account_type" id="account_type" onchange="toggleFields()">
                    <option value="driver">Driver</option>
                    <option value="fleet_manager">Fleet Manager</option>
                </select>
            </div>

            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>

            <!-- DRIVER FIELDS -->
            <div id="driver-fields">
                <h3 class="section-title">Driver Details</h3>
                <div class="form-group">
                    <label>TLC License #</label>
                    <input type="text" name="tlc_license">
                </div>
                <div class="form-group">
                    <label>TLC Expiration Date</label>
                    <input type="date" name="tlc_expiration">
                </div>
                <div class="form-group">
                    <label>DMV License #</label>
                    <input type="text" name="dmv_license">
                </div>
                <div class="form-group">
                    <label>DMV Expiration Date</label>
                    <input type="date" name="dmv_expiration">
                </div>
                <div class="form-group">
                    <label>Vehicle Plate</label>
                    <input type="text" name="plate" id="plate" data-optional="true">
                    <label style="display:inline; font-weight: normal;">
                        <input type="checkbox" name="no_vehicle" id="no_vehicle" onclick="togglePlate()" data-optional="true" style="width: auto;">
                        I don't have a vehicle
                    </label>
                </div>
            </div>

            <!-- FLEET FIELDS -->
            <div id="fleet-fields" class="hidden">
                <h3 class="section-title">Fleet Details</h3>
                <div class="form-group">
                    <label>Company Name</label>
                    <input type="text" name="company_name">
                </div>
                <div class="form-group">
                    <label>Manager Name</label>
                    <input type="text" name="manager_name">
                </div>
                <div class="form-group">
                    <label>Contact Email</label>
                    <input type="email" name="contact_email">
                </div>
                <div class="form-group">
                    <label>Contact Phone</label>
                    <input type="text" name="contact_phone">
                </div>
            </div>

            <button type="submit">Create Account</button>
            <p style="text-align: center;"><a href="/login">Already have an account? Login</a></p>
        </form>
    </div>
</body>
</html>
