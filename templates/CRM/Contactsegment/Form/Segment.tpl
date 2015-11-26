{* HEADER *}
<h3>{$actionLabel}&nbsp;{$segmentTypeLabel}</h3>

<div class="crm-block crm-form-block">
  <div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="top"}
  </div>
  <div class="crm-section">
    <div class="label">{$form.segment_type.label}</div>
    <div class="content">{$form.segment_type.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.segment_label.label}</div>
    <div class="content">{$form.segment_label.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.segment_parent.label}</div>
    <div class="content">{$form.segment_parent.html}</div>
    <div class="clear"></div>
  </div>
  {* FOOTER *}
  <div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
  </div>
</div>

{* show or hide parent select list *}
{literal}
  <script type="text/javascript">
    cj(document).ready(function() {
      cj('#segment_type').parent().parent().hide();
      if (cj('#segment_type').val() != "child") {
        setParent();
      } else {
        setChild();
      }
    });
    cj('#segment_type_list_0').click(function() {
      if (cj(this).is(":checked")) {
        setParent();
      }
    });
    cj('#segment_type_list_1').click(function() {
      if (cj(this).is(":checked")) {
        setChild();
      }
    });
    function setParent() {
      cj('#segment_type_list_1').prop({"checked":false});
      cj('#segment_type_list_0').prop({"checked":true});
      cj('#segment_parent').parent().parent().hide();
    }
    function setChild() {
      cj('#segment_type_list_0').prop({"checked":false});
      cj('#segment_type_list_1').prop({"checked":true});
      cj('#segment_parent').parent().parent().show();
    }
  </script>
{/literal}
