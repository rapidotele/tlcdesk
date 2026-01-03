<!DOCTYPE html>
<html>
<head>
    <title><?= __('register') ?> - TLCDesk</title>
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
        <div style="text-align: right; margin-bottom: 10px;">
            <select onchange="window.location.href='/language/switch?code='+this.value">
                <?php foreach (getEnabledLanguages() as $lang): ?>
                    <option value="<?= $lang['code'] ?>" <?= (\App\Core\Translator::getInstance()->getLocale() == $lang['code']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($lang['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <h2><?= __('register') ?></h2>
        <?php if (isset($error)): ?>
            <p style="color: red;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="POST" action="/register">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <div class="form-group">
                <label><?= __('account_type') ?></label>
                <select name="account_type" id="account_type" onchange="toggleFields()">
                    <option value="driver"><?= __('driver') ?></option>
                    <option value="fleet_manager"><?= __('fleet_manager') ?></option>
                </select>
            </div>

            <div class="form-group">
                <label><?= __('full_name') ?></label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label><?= __('email') ?></label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label><?= __('password') ?></label>
                <input type="password" name="password" required>
            </div>

            <!-- DRIVER FIELDS -->
            <div id="driver-fields">
                <h3 class="section-title"><?= __('driver_details') ?></h3>
                <div class="form-group">
                    <label><?= __('tlc_license') ?></label>
                    <input type="text" name="tlc_license">
                </div>
                <div class="form-group">
                    <label><?= __('tlc_expiration') ?></label>
                    <input type="date" name="tlc_expiration">
                </div>
                <div class="form-group">
                    <label><?= __('dmv_license') ?></label>
                    <input type="text" name="dmv_license">
                </div>
                <div class="form-group">
                    <label><?= __('dmv_expiration') ?></label>
                    <input type="date" name="dmv_expiration">
                </div>
                <div class="form-group">
                    <label><?= __('vehicle_plate') ?></label>
                    <input type="text" name="plate" id="plate" data-optional="true">
                    <label style="display:inline; font-weight: normal;">
                        <input type="checkbox" name="no_vehicle" id="no_vehicle" onclick="togglePlate()" data-optional="true" style="width: auto;">
                        <?= __('no_vehicle') ?>
                    </label>
                </div>
            </div>

            <!-- FLEET FIELDS -->
            <div id="fleet-fields" class="hidden">
                <h3 class="section-title"><?= __('fleet_details') ?></h3>
                <div class="form-group">
                    <label><?= __('company_name') ?></label>
                    <input type="text" name="company_name">
                </div>
                <div class="form-group">
                    <label><?= __('manager_name') ?></label>
                    <input type="text" name="manager_name">
                </div>
                <div class="form-group">
                    <label><?= __('contact_email') ?></label>
                    <input type="email" name="contact_email">
                </div>
                <div class="form-group">
                    <label><?= __('contact_phone') ?></label>
                    <input type="text" name="contact_phone">
                </div>
            </div>

            <button type="submit"><?= __('create_account') ?></button>
            <p style="text-align: center;"><a href="/login"><?= __('already_have_account') ?></a></p>
        </form>
    </div>
</body>
</html>
