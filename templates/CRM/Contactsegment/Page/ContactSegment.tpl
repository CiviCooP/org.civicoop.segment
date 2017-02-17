<div class="crm-content-block crm-block">
  {if empty($activeContactSegments) and empty($inactiveContactSegments)}
    <div id="help">
      {ts}This contact is currently not linked to a{/ts} {$parentSegmentLabel} {ts}or{/ts} {$childSegmentLabel}
    </div>
  {/if}
  <div class="action-link">
    <a class="button new-option" href="{$addUrl}">
      <span><div class="icon add-icon"></div>{ts}Add{/ts} {$parentSegmentLabel} {ts}or{/ts} {$childSegmentLabel} {ts}to Contact{/ts}</span>
    </a>
  </div>

  {include file="CRM/common/pager.tpl" location="top"}
  <div id="contact-segment-wrapper" class="dataTables_wrapper">
    {if !empty($activeContactSegments)}
      <h3>{ts}Active{/ts} {$parentSegmentLabel}s {ts}and/or{/ts} {$childSegmentLabel}s</h3>
      {include file="CRM/Contactsegment/Page/ActiveContactSegment.tpl"}
    {/if}
    {if !empty($pastContactSegments)}
      <h4 class="label font-red">{ts}Past or Future{/ts} {$parentSegmentLabel}s {ts}and/or{/ts} {$childSegmentLabel}s</h4>
      {include file="CRM/Contactsegment/Page/PastContactSegment.tpl"}
    {/if}
    {if !empty($floatingContactSegments)}
      <h5 class="label font-red">{ts}Past or Future {/ts} {$childSegmentLabel}s {ts} with still active{/ts} {$parentSegmentLabel}s</h5>
      {include file="CRM/Contactsegment/Page/FloatingContactSegment.tpl"}
    {/if}
  </div>
  {include file="CRM/common/pager.tpl" location="bottom"}
</div>