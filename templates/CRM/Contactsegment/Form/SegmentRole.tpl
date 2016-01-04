{* HEADER *}
<h3>Edit Unique Roles for Parent and Child</h3>
<div class="crm-block crm-form-block">
  <div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="top"}
  </div>

  <h3>Parent</h3>
  <div class="crm-section">
    <div class="label">{$form.parent_uniques.label}</div>
    <div class="content">{$form.parent_uniques.html}</div>
    <div class="clear"></div>
  </div>
  <h3>Child</h3>
  <div class="crm-section">
    <div class="label">{$form.child_uniques.label}</div>
    <div class="content">{$form.child_uniques.html}</div>
    <div class="clear"></div>
  </div>
  {* FOOTER *}
  <div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
  </div>
</div>