<div id="past-contact-segment-wrapper" class="dataTables_wrapper">
  <table id="past-contact-segment-table" class="display">
    <tbody>
      {assign var="rowClass" value="odd-row"}
      {assign var="rowCount" value=0}
      {foreach from=$floatingContactSegments key=floatingContactSegmentId item=floatingContactSegment}
        {assign var="rowCount" value=$rowCount+1}
        <tr id="row{$rowCount}" class={$rowClass}>
          <td hidden="1">{$floatingContactSegmentId}
          <td>{$floatingContactSegment.label}</td>
          <td>{$floatingContactSegment.type}</td>
          <td>{$floatingContactSegment.role}</td>
          <td>{$floatingContactSegment.start_date|crmDate}</td>
          <td>{$floatingContactSegment.end_date|crmDate}</td>
          <td>
            <span>
              {foreach from=$floatingContactSegment.actions item=actionLink}
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
