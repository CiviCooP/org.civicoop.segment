<div id="active-contact-segment-wrapper" class="dataTables_wrapper">
  <table id="active-contact-segment-table" class="display">
    <thead>
      <tr>
        <th class="sorting disabled">{ts}Label{/ts}</th>
        <th class="sorting disabled">{ts}Type{/ts}</th>
        <th class="sorting disabled">{ts}Role{/ts}</th>
        <th class="sorting disabled">{ts}Start Date{/ts}</th>
        <th class="sorting disabled">{ts}End Date{/ts}</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      {assign var="rowClass" value="odd-row"}
      {assign var="rowCount" value=0}
      {foreach from=$activeContactSegments key=activeContactSegmentId item=activeContactSegment}
        {assign var="rowCount" value=$rowCount+1}
        <tr id="row{$rowCount}" class={$rowClass}>
          <td hidden="1">{$activeContactSegmentId}</td>
          <td>{$activeContactSegment.label}</td>
          <td>{$activeContactSegment.type}</td>
          <td>{$activeContactSegment.role}</td>
          <td>{$activeContactSegment.start_date|crmDate}</td>
          <td>{$activeContactSegment.end_date|crmDate}</td>
          <td>
            <span>
              {foreach from=$activeContactSegment.actions item=actionLink}
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
