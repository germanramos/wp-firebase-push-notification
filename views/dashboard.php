<?php
if (!defined('ABSPATH')) {exit;}
?>

<h1><?php echo __(FCM_PLUGIN_NM,FCM_TD);?></h1>

<form action="options.php" method="post">
    <?php settings_fields( 'fcm_group'); ?>
    <?php do_settings_sections( 'fcm_group' ); ?>
<table>
    <tbody>

    <tr  height="70">
        <td><label for="fcm_api"><?php echo __("FCM API Key",FCM_TD);?></label> </td>
        <td><input id="fcm_api" name="stf_fcm_api" type="text" value="<?php echo get_option( 'stf_fcm_api' ); ?>" required="required" /></td>
    </tr>

    <tr>
        <td> <div class="col-sm-10"><?php submit_button(); ?></td>

    </tr>

    </tbody>
    </table>

</form>

<?php if(get_option('stf_fcm_api')){ ?>
<div>
    <h3>Test Notification</h3>
    <p>Notification sent to device with "test" topic</p>
    <a href="<?php echo admin_url('admin.php'); ?>?page=test_notification">Test Notification</a>
</div>

<?php
}
?>
