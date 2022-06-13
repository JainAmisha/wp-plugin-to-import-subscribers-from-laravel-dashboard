<?php
    $encryption_details = get_option(LARAVEL_WP_ENCRYPTION_DETAILS, 1);
?>

<div style="padding:2%;">
    <h2>Laravel WP Encryption Details</h2>
    <hr>
    <form action="" method="post">
        <table style="text-align:left;width:100%;">
            <thead>
                <tr>
                    <th style="width:20%;"><label for="laravel_customer_profile_url">Laravel Customer Profile Base URL</label></th>
                    <td><input type="text" style="width:80%;" name="laravel_customer_profile_url" id="laravel_customer_profile_url" value="<?php echo isset($encryption_details['laravel_customer_profile_url']) ? $encryption_details['laravel_customer_profile_url'] : ''; ?>"></td>
                </tr>
                <tr>
                    <th><label for="encryption_cipher">Encryption Cipher</label></th>
                    <td><input type="text" style="width:80%;" name="encryption_cipher" id="encryption_cipher" value="<?php echo isset($encryption_details['ciphering']) ? $encryption_details['ciphering'] : ''; ?>"></td>
                </tr>
                <tr>
                    <th><label for="encryption_iv">Encryption IV</label></th>
                    <td><input type="text" style="width:80%;" name="encryption_iv" id="encryption_iv" value="<?php echo isset($encryption_details['iv']) ? $encryption_details['iv'] : ''; ?>"></td>
                </tr>
                <tr>
                    <th><label for="encryption_key">Encryption Key</label></th>
                    <td><input type="text" style="width:80%;" name="encryption_key" id="encryption_key" value="<?php echo isset($encryption_details['key']) ? $encryption_details['key'] : ''; ?>"></td>
                </tr>
            </thead>
        </table>
        <br>
        <input type="submit" class="button action" style="padding:0 2%;" name="laravel_wp_encryption_details" value="Save">
    </form>
</div>