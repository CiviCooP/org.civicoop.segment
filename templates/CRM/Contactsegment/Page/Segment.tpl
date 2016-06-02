<div class="crm-content-block crm-block">
  <div id="help">
    The existing {$parentSegmentLabel}s (with linked {$childSegmentLabel}s) are listed below. You can add, edit or delete them from this screen.
  </div>
  <div class="action-link">
    <a class="button new-option" href="{$addUrl}">
      <span><div class="icon add-icon"></div>New {$parentSegmentLabel} or {$childSegmentLabel}</span>
    </a>
  </div>
  {include file="CRM/common/pager.tpl" location="top"}
  {include file='CRM/common/jsortable.tpl'}
  <div id="segment-wrapper" class="dataTables_wrapper">
    <table id="segment-table" class="display">
      <thead>
        <tr>
          <th>{ts}Label{/ts}</th>
          <th>{ts}Type{/ts}</th>
          <th>{ts}{$parentSegmentLabel}{/ts}</th>
          <th>{ts}Is active?{/ts}</th>
          <th id="nosort"></th>
        </tr>
      </thead>
      <tbody>
      {assign var="rowClass" value="odd-row"}
      {assign var="rowCount" value=0}
      {foreach from=$segments key=segmentId item=segment}
        {assign var="rowCount" value=$rowCount+1}
        <tr id="row{$rowCount}" class={$rowClass}>
          <td hidden="1">{$segmentId}
          {if !empty($segment.parent)}
            <td>&nbsp;&nbsp;&nbsp;{$segment.label}</td>
            <td>{$segment.type}</td>
          {else}
            <td><strong>{$segment.label}</strong></td>
            <td><strong>{$segment.type}</strong></td>
          {/if}
          <td>{$segment.parent}</td>
          <td>
            {if ($segment.is_active)}
            <img id="isActive" src="{$config->resourceBase}i/check.gif" alt="Is active">
            {/if}
          </td>
          <td>
              <span>
                {foreach from=$segment.actions item=actionLink}
                  {$actionLink}
                {/foreach}
              </span>
          </td>
        </tr>
        {if $rowClass eq "odd-row"}
          {assign var="rowClass" value="even-row"}
        {else}
          {assign var="rowClass" value="odd-row"}
        {/if}
      {/foreach}
      </tbody>
    </table>
  </div>
  {include file="CRM/common/pager.tpl" location="bottom"}
  <div class="action-link">
    <a class="button new-option" href="{$addUrl}">
      <span><div class="icon add-icon"></div>New {$parentSegmentLabel} or {$childSegmentLabel}</span>
    </a>
  </div>
</div>