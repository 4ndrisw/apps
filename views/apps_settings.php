<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php echo form_hidden('apps_settings'); ?>
<div class="horizontal-scrollable-tabs mbot15">
   <div role="tabpanel" class="tab-pane" id="apps">
      <div class="form-group">
         <label class="control-label" for="app_prefix"><?php echo _l('app_prefix'); ?></label>
         <input type="text" name="settings[app_prefix]" class="form-control" value="<?php echo get_option('app_prefix'); ?>">
      </div>
      <hr />

      <i class="fa fa-question-circle pull-left" data-toggle="tooltip" data-title="<?php echo _l('next_app_number_tooltip'); ?>"></i>
      <?php echo render_input('settings[next_app_number]','next_app_number',get_option('next_app_number'), 'number', ['min'=>1]); ?>
      <hr />

      <i class="fa fa-question-circle pull-left" data-toggle="tooltip" data-title="<?php echo _l('due_after_help'); ?>"></i>
      <?php echo render_input('settings[app_qrcode_size]', 'app_qrcode_size', get_option('app_qrcode_size')); ?>
      <hr />

      <i class="fa fa-question-circle pull-left" data-toggle="tooltip" data-title="<?php echo _l('due_after_help'); ?>"></i>
      <?php echo render_input('settings[app_due_after]','app_due_after',get_option('app_due_after')); ?>
      <hr />

      <i class="fa fa-question-circle pull-left" data-toggle="tooltip" data-title="<?php echo _l('app_number_of_date_tooltip'); ?>"></i>
      <?php echo render_input('settings[app_number_of_date]','app_number_of_date',get_option('app_number_of_date'), 'number', ['min'=>0]); ?>
      <hr />

      <?php render_yes_no_option('app_send_telegram_message','app_send_telegram_message'); ?>
      <hr />


      <?php echo render_yes_no_option('allow_staff_view_apps_assigned','allow_staff_view_apps_assigned'); ?>
      <hr />
      <?php render_yes_no_option('view_app_only_logged_in','require_client_logged_in_to_view_app'); ?>
      <hr />
      <?php render_yes_no_option('show_assigned_on_apps','show_assigned_on_apps'); ?>
      <hr />
      <?php render_yes_no_option('show_project_on_app','show_project_on_app'); ?>
      <hr />

      <?php echo render_input('settings[app_year]','app_year',get_option('app_year'), 'number', ['min'=>2020]); ?>
      <hr />
      
      <div class="form-group">
         <label for="app_number_format" class="control-label clearfix"><?php echo _l('app_number_format'); ?></label>
         <div class="radio radio-primary radio-inline">
            <input type="radio" name="settings[app_number_format]" value="1" id="e_number_based" <?php if(get_option('app_number_format') == '1'){echo 'checked';} ?>>
            <label for="e_number_based"><?php echo _l('app_number_format_number_based'); ?></label>
         </div>
         <div class="radio radio-primary radio-inline">
            <input type="radio" name="settings[app_number_format]" value="2" id="e_year_based" <?php if(get_option('app_number_format') == '2'){echo 'checked';} ?>>
            <label for="e_year_based"><?php echo _l('app_number_format_year_based'); ?> (YYYY.000001)</label>
         </div>
         <div class="radio radio-primary radio-inline">
            <input type="radio" name="settings[app_number_format]" value="3" id="e_short_year_based" <?php if(get_option('app_number_format') == '3'){echo 'checked';} ?>>
            <label for="e_short_year_based">000001-YY</label>
         </div>
         <div class="radio radio-primary radio-inline">
            <input type="radio" name="settings[app_number_format]" value="4" id="e_year_month_based" <?php if(get_option('app_number_format') == '4'){echo 'checked';} ?>>
            <label for="e_year_month_based">000001.MM.YYYY</label>
         </div>
         <hr />
      </div>
      
      <div class="row">
         <div class="col-md-12">
            <?php echo render_input('settings[apps_pipeline_limit]','pipeline_limit_status',get_option('apps_pipeline_limit')); ?>
         </div>
         <div class="col-md-7">
            <label for="default_proposals_pipeline_sort" class="control-label"><?php echo _l('default_pipeline_sort'); ?></label>
            <select name="settings[default_apps_pipeline_sort]" id="default_apps_pipeline_sort" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
               <option value="datecreated" <?php if(get_option('default_apps_pipeline_sort') == 'datecreated'){echo 'selected'; }?>><?php echo _l('apps_sort_datecreated'); ?></option>
               <option value="date" <?php if(get_option('default_apps_pipeline_sort') == 'date'){echo 'selected'; }?>><?php echo _l('apps_sort_app_date'); ?></option>
               <option value="pipeline_order" <?php if(get_option('default_apps_pipeline_sort') == 'pipeline_order'){echo 'selected'; }?>><?php echo _l('apps_sort_pipeline'); ?></option>
               <option value="expirydate" <?php if(get_option('default_apps_pipeline_sort') == 'expirydate'){echo 'selected'; }?>><?php echo _l('apps_sort_expiry_date'); ?></option>
            </select>
         </div>
         <div class="col-md-5">
            <div class="mtop30 text-right">
               <div class="radio radio-inline radio-primary">
                  <input type="radio" id="k_desc_app" name="settings[default_apps_pipeline_sort_type]" value="asc" <?php if(get_option('default_apps_pipeline_sort_type') == 'asc'){echo 'checked';} ?>>
                  <label for="k_desc_app"><?php echo _l('order_ascending'); ?></label>
               </div>
               <div class="radio radio-inline radio-primary">
                  <input type="radio" id="k_asc_app" name="settings[default_apps_pipeline_sort_type]" value="desc" <?php if(get_option('default_apps_pipeline_sort_type') == 'desc'){echo 'checked';} ?>>
                  <label for="k_asc_app"><?php echo _l('order_descending'); ?></label>
               </div>
            </div>
         </div>
         <div class="clearfix"></div>
      </div>
   </div>
 <?php hooks()->do_action('after_apps_tabs_content'); ?>
</div>
