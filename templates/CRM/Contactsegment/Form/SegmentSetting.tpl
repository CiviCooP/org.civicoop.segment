{* HEADER *}
<h3>Edit Segment Settings for Parent and Child</h3>
<div class="crm-block crm-form-block">
  <div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="top"}
  </div>

  <h3>Parent</h3>
  <div class="crm-section">
    <div class="label">{$form.parent_label.label}</div>
    <div class="content">{$form.parent_label.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.parent_roles.label}</div>
    <div class="content">{$form.parent_roles.html}</div>
    <div class="clear"></div>
  </div>
  <h3>Child</h3>
  <div class="crm-section">
    <div class="label">{$form.child_label.label}</div>
    <div class="content">{$form.child_label.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.child_roles.label}</div>
    <div class="content">{$form.child_roles.html}</div>
    <div class="clear"></div>
  </div>
  {* FOOTER *}
  <div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
  </div>
</div>