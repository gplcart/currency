<?php
/**
 * @package Currency
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2017, Iurii Makukh <gplcart.software@gmail.com>
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GNU General Public License 3.0
 */
?>
<form method="post" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $_token; ?>">
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="form-group">
        <div class="col-md-6 col-md-offset-2">
          <div class="checkbox">
            <label>
              <input name="settings[status]" value="1" type="checkbox"<?php echo empty($settings['status']) ? '' : ' checked'; ?>> <?php echo $this->text('Enable auto updating'); ?>
            </label>
          </div>
        </div>
      </div>
      <div class="form-group required<?php echo $this->error('interval', ' has-error'); ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Interval'); ?></label>
        <div class="col-md-6">
          <input name="settings[interval]" class="form-control" value="<?php echo $this->e($settings['interval']); ?>">
          <div class="help-block">
            <?php echo $this->error('interval'); ?>
            <div class="text-muted">
              <?php echo $this->text('Minimal interval in seconds between updates. This value will be checked only during cron calls'); ?>
            </div>
          </div>
        </div>
      </div>
      <div class="form-group required<?php echo $this->error('derivation', ' has-error'); ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Derivation, %'); ?></label>
        <div class="col-md-6">
          <input name="settings[derivation]" class="form-control" value="<?php echo is_array($settings['derivation']) ? $this->e(implode(',', $settings['derivation'])) : $this->e($settings['derivation']); ?>">
          <div class="help-block">
            <?php echo $this->error('derivation'); ?>
            <div class="text-muted">
              <?php echo $this->text('Max and min accepted currency rate derivation for new rates. Four numbers separated by comma. First and second number - max and min percent difference from the old value for decreasing rates, third and fourth - for increasing rates. If a new rate is out of the ranges, the currency rate will not be updated'); ?>
            </div>
          </div>
        </div>
      </div>
      <div class="form-group required<?php echo $this->error('correction', ' has-error'); ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Correction, %'); ?></label>
        <div class="col-md-6">
          <input name="settings[correction]" class="form-control" value="<?php echo $this->e($settings['correction']); ?>">
          <div class="help-block">
            <?php echo $this->error('correction'); ?>
            <div class="text-muted">
              <?php echo $this->text('A percent correction value to add to Yahoo\'s rates. To subtract use negative numbers'); ?>
            </div>
          </div>
        </div>
      </div>
      <div class="form-group">
        <div class="col-md-6 col-md-offset-2">
          <div class="checkbox">
            <label>
              <input name="settings[update]" type="checkbox" value="1"> <?php echo $this->text('Update on save'); ?>
              <div class="help-block"><?php echo $this->text('Update currency rates once the form is submitted. Derivation setting will be ignored!'); ?></div>
            </label>
          </div>
        </div>
      </div>
      <div class="form-group">
        <div class="col-md-4 col-md-offset-2">
          <div class="btn-toolbar">
            <a href="<?php echo $this->url("admin/module/list"); ?>" class="btn btn-default"><?php echo $this->text("Cancel"); ?></a>
            <button class="btn btn-default save" name="save" value="1"><?php echo $this->text("Save"); ?></button>
          </div>
        </div>
      </div>
    </div>
  </div>
</form>