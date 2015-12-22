<div class="crm-content-block crm-block">
  {if empty($activeContactSegments) and empty($inactiveContactSegments)}
    <div id="help">
      This contact is currently not linked to a {$parentSegmentLabel} or {$childSegmentLabel}
    </div>
  {/if}
  <div class="action-link">
    <a class="button new-option" href="{$addUrl}">
      <span><div class="icon add-icon"></div>Add {$parentSegmentLabel} or {$childSegmentLabel} to Contact</span>
    </a>
  </div>

  {include file="CRM/common/pager.tpl" location="top"}
  <div id="contact-segment-wrapper" class="dataTables_wrapper">
    {if !empty($activeContactSegments)}
      <h3>Active {$parentSegmentLabel}s and/or {$childSegmentLabel}s</h3>
      {include file="CRM/Contactsegment/Page/ActiveContactSegment.tpl"}
    {/if}
    {if !empty($pastContactSegments)}
      <h4 class="label font-red">Past {$parentSegmentLabel}s and/or {$childSegmentLabel}s</h4>
      {include file="CRM/Contactsegment/Page/PastContactSegment.tpl"}
    {/if}
  </div>
  {include file="CRM/common/pager.tpl" location="bottom"}
</div>